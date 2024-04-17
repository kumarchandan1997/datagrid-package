<?php

use Datagrid\Http\Controllers\DataGridController;
use Illuminate\Support\Facades\Route;

Route::get('users/{users}', function ($users) {
    return view('datagrid::data-grid');
});

// Route::get('/datagrid', [DataGridController::class, 'index'])->name('datagrid.index');

Route::group(['middleware' => ['web']], function () {
    Route::post('/user', [DataGridController::class, 'setColumnsToDisplay'])->name('datagrid.setColumnsToDisplay');
});

Route::post('users/{users}', function ($users) {
    return view('datagrid::data-grid');
});

Route::delete('users/{users}', [DataGridController::class, 'destroyLead'])->name('datagrid.destroyLead');
Route::POST('/users', [DataGridController::class, 'bulkDelete'])->name('datagrid.bulkDelete');
Route::post('/export-data', [DataGridController::class, 'exportData'])->name('export.data');
Route::post('/import', [DataGridController::class, 'importCSV'])->name('import.csv');
Route::get('/delete-file', function (Illuminate\Http\Request $request) {
    $fileUrl = $request->query('fileUrl');
    $filePath = public_path('storage/'.basename(parse_url($fileUrl, PHP_URL_PATH)));
    if (file_exists($filePath)) {
        unlink($filePath);

        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'File not found'], 404);
    }
});

Route::get('edit/{id}', [DataGridController::class, 'editData']);
Route::post('/update', [DataGridController::class, 'updateData']);
Route::post('/create', [DataGridController::class, 'createData']);
