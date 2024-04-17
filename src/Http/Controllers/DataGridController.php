<?php

namespace Datagrid\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DataGridController extends Controller
{
    public function setColumnsToDisplay(Request $request)
    {
        //Get name of model name and count
        $modelsPath = app_path('Models');
        $modelFiles = File::glob($modelsPath . '/*.php');

        $modelFileNames = [];

        foreach ($modelFiles as $filePath) {
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $modelFileNames[] = $fileName;
        }

        $modelName = class_basename($request->input('model'));
        $generateSessionKey = $modelName . '_SessionKey';
        $sessionKey = config('datagrid.' . $generateSessionKey); // Define the session key
        $columns = $request->dropdown_group;
        $uniqueColumns = array_unique($columns);

        for ($i = 0; $i < count($modelFileNames); $i++) {

            if ($modelName == $modelFileNames[$i]) {
                $request->session()->put($sessionKey, $uniqueColumns);
                return redirect()->route($modelName . '.index');
            }
        }
    }

    public function destroyLead(Request $request, $id)
    {
        try {
            $model = $request->model;
            $deleted = $model::where('id', $id)->delete();

            if ($deleted) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to delete lead']);
            }
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function bulkDelete(Request $request)
    {
        // Accept the selected row IDs from the request
        $selectedRows = $request->input('selectedRows');
        $model = $request->input('model');
        try {
            foreach ($selectedRows as $id) {
                $user = $model::find($id);
                if ($user) {
                    $user->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'Selected rows deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting selected rows: ' . $e->getMessage()], 500);
        }
    }

    public function exportData(Request $request)
    {
        try {
            $searchValue = $request->input('filterSearch');
            $allColumns = $request->input('allColumns');
            $model = $request->input('model');
            $searchColumns = $request->input('searchColumns');
            $headers = isset($searchColumns) ? $searchColumns : $allColumns;
            $headers = array_filter($headers, function ($header) {
                return $header !== 'id';
            });

            // Check if $headers array is empty or null
            if (empty($headers)) {
                throw new \Exception('Select At Least one column to export file.');
            }

            $exportType = $request->input('exportType');
            $fileName = 'export_' . time() . '.' . $exportType;
            $filePath = public_path('storage/' . $fileName);

            $file = fopen($filePath, 'w');
            if (!$file) {
                throw new \Exception('Failed to open file for writing.');
            }

            $headersWithSrNo = ['Sr.No'];
            foreach ($headers as $header) {
                $headersWithSrNo[] = strtoupper($header);
            }
            if ($exportType == 'tsv') {
                fwrite($file, implode("\t", $headersWithSrNo) . PHP_EOL);
            } else {
                fputcsv($file, $headersWithSrNo);
            }

            $query = $model::select($headers)->orderBy('created_at', 'DESC');
            if ($searchValue !== null && !empty($searchColumns)) {
                $query->where(function ($query) use ($searchValue, $searchColumns) {
                    foreach ($searchColumns as $column) {
                        $query->orWhere($column, 'like', '%' . $searchValue . '%');
                    }
                });
            }
            $leads = $query->get();
            if ($leads->isEmpty()) {
                throw new \Exception('No data available for export.');
            }

            $increment = 1;
            foreach ($leads as $lead) {
                $rowData = [$increment++];
                foreach ($headers as $header) {
                    $rowData[] = $lead->{$header};
                }
                if ($exportType == 'tsv') {
                    fwrite($file, implode("\t", $rowData) . PHP_EOL);
                } else {
                    fputcsv($file, $rowData);
                }
            }

            fclose($file);
            $fileUrl = url('storage/' . $fileName);

            $message = 'File Exported successfully';

            return response()->json([
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json(['errorMessage' => $e->getMessage()], 500);
        }
    }

    public function importCSV(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'csv_file' => 'required|mimetypes:text/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:2048',
            ]);

            $model = $request->input('model');
            $file = $request->file('csv_file');
            $csvData = file_get_contents($file->path());

            // Determine delimiter based on file extension
            $extension = $file->getClientOriginalExtension();
            $delimiter = ($extension === 'tsv') ? "\t" : ',';

            $rows = explode("\n", trim($csvData));
            $headers = str_getcsv(array_shift($rows), $delimiter);

            if (empty($headers) || empty($rows)) {
                throw new \Exception('Invalid CSV file');
            }

            $lowercaseHeaders = array_map('strtolower', $headers);
            $configColumns = json_decode($request->input('allColumns'), true);
            $headerColumns = array_map('trim', $lowercaseHeaders);

            $columnsMapping = array_intersect($configColumns, $headerColumns);

            $modelName = class_basename($request->input('model'));
            $generateUniqueColumn = $modelName . '_unique_column';
            $uniqueColumn = config('datagrid.' . $generateUniqueColumn);
            $emptyFields = [];

            foreach ($rows as $key => $row) {
                $rowData = str_getcsv($row, $delimiter);
                $filteredRowData = [];

                foreach ($columnsMapping as $column) {
                    $index = array_search(strtolower($column), $headerColumns);
                    if ($index !== false) {
                        $filteredRowData[$column] = $rowData[$index];
                        $filteredRowData['password'] = 'password';
                    }
                }
                if (!empty($filteredRowData[$uniqueColumn])) {

                    $existingModel = $model::where($uniqueColumn, $filteredRowData[$uniqueColumn])->first();

                    Log::info($existingModel);
                    if ($existingModel) {
                        $existingModel->update($filteredRowData);
                    } else {
                        $model::create($filteredRowData);
                    }
                } else {
                    $emptyFields[] = $key + 1;
                }
            }
            // Prepare the message after all fields have been processed
            if (!empty($emptyFields)) {
                $message = implode(', ', $emptyFields);
                $message .= " numbers have issues with unique validation in $uniqueColumn column";
                Cache::put('message', $message, 1);
                Cache::put('message_type', 'import_error', 1);
            } else {
                Cache::put('message', 'CSV file imported successfully', 1);
                Cache::put('message_type', 'success', 1);
            }

            return redirect()->back();
        } catch (\Throwable $th) {
            Cache::put('message', $th->getMessage(), 1);
            Cache::put('message_type', 'error', 1);

            return redirect()->back();
        }
    }

    public function editData(Request $request, $id)
    {
        $modelClass = '\\' . $request->model;
        $modelData = $modelClass::findOrFail($id);
        return response()->json($modelData);
    }

    public function updateData(Request $request)
    {
        // $imageField = config('datagrid.Customer_has_image');
        $modelName = class_basename($request->input('model'));
        $imageField = config('datagrid.'. $modelName . '_has_image');
        $generateUniqueColumn = $modelName . '_unique_column';
        $uniqueColumn = config('datagrid.' . $generateUniqueColumn);
        $modelClass = '\\' . $request->model;
        $modelData = $modelClass::findOrFail($request->edit_id);
        $insertData = $request->form_data;
        // Check if the request contains a new image
        if ($request->hasFile($imageField)) {
            // Delete the old image from the public folder
            $oldImagePath = public_path('storage/'.$modelName.'/' . $modelData->image);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            $image = $request->file($imageField);
            $imageName = $image->getClientOriginalName();
            $image->storeAs($modelName, $imageName, 'public');
            $insertData[$imageField] = $imageName;

        }
        if ($modelData->$uniqueColumn !== $insertData[$uniqueColumn]) {
            $existingUser = $modelClass::where($uniqueColumn, $insertData[$uniqueColumn])->first();
            if ($existingUser) {
                return response()->json(['message' => 'Please provide a unique ' . $uniqueColumn]);
            }
        }
        $modelData->update($insertData);
        return response()->json(['message' =>  $modelName . ' updated successfully!']);
    }

    public function createData(Request $request)
    {
        $modelData = $request->except('_token', 'model', 'image');
        $modelName = class_basename($request->input('model'));
        $imageField = config('datagrid.'. $modelName . '_has_image');

        if ($request->hasFile($imageField)) {
            $image = $request->file($imageField);
            $imageName = $image->getClientOriginalName();
            $image->storeAs($modelName, $imageName, 'public');
            $modelData[$imageField] = $imageName;
        }
        $modelClass = '\\' . $request->model;
        $modelClass::create($modelData);

        return response()->json(['message' => 'Data stored successfully']);
    }
}
