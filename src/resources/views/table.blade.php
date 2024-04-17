<div class="container-xxl flex-grow-1 container-p-y">
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row select-z">
                            <div class="col-xl-5">
                                <form id= "filter-form">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Search..."
                                            name="search" id="search" value="{{ request()->get('search') }}">
                                        <label class="error"></label>
                                        <button class="btn btn-primary" id="search-btn"
                                            type="button">{{ __('datagrid::grid.Search') }}</button>
                                        <a href="{{ route(class_basename($model) . '.index') }}"
                                            class="btn btn-secondary">Reset</a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-5">
                                <div class="dropdown-container">
                                    <div class="dropdown" data-control="checkbox-dropdown">
                                        <label class="dropdown-label">Select</label>
                                        <form method="post" action="{{ route('datagrid.setColumnsToDisplay') }}">
                                            @csrf
                                            <div class="dropdown-list">
                                                <a href="#" data-toggle="check-all" class="dropdown-option">Check
                                                    All</a>
                                                <div class="checkbox-group">
                                                    <input type="hidden" name="dropdown_group[]" value="id">
                                                    <input type="hidden" name="model" value="{{ $model }}">
                                                    @foreach ($columnsAll as $column)
                                                        @php
                                                            if ($column == 'id') {
                                                                continue;
                                                            }
                                                            $checked = in_array($column, $columns);
                                                        @endphp
                                                        <div class="columns-list">
                                                            <label class="dropdown-option">
                                                                <input type="checkbox" name="dropdown_group[]"
                                                                    value="{{ $column }}"
                                                                    {{ $checked ? 'checked' : '' }} />
                                                                {{ $column }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="submit" class="btn btn-primary save-btn">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2">
                                <button type="button" id="delete-selected" class="btn btn-danger">Delete
                                    Selected</button>
                            </div>
                        </div>
                        <div class="row mt-10">
                            <div class="col-sm-3">
                                @if (config('datagrid.' . class_basename($model) . '_has_create_option'))
                                    <button type="button" class="btn btn-primary create-button" data-toggle="modal"
                                        data-target="#createModal">
                                        Create
                                    </button>
                                @endif
                            </div>
                            <div class="col-sm-9 text-end mt-3">
                                <div class="showing-rows-info pull-right">
                                    <span>@lang('datagrid::grid.Showing') </span>
                                    <select name="rows-per-page">
                                        @php
                                            $currentRowsPerPage = isset($_GET['rows-per-page'])
                                                ? $_GET['rows-per-page']
                                                : $rowsPerPage;
                                        @endphp
                                        @foreach (config('datagrid.rowsPerPage') as $nr)
                                            <option value="{{ $nr }}"
                                                {{ $nr == $currentRowsPerPage ? 'selected' : '' }}>
                                                {{ $nr }}</option>
                                        @endforeach
                                    </select>
                                    <span>@lang('datagrid::grid.rows per page.')</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <div class="card mt-2">
                            <table class="table table-striped" id="data-table">
                                <thead>
                                    <tr>
                                        @if (count($columns) > 1)
                                            <th scope="col"><input type="checkbox"></th>
                                            <th scope="col" class="column">Sr.No</th>
                                            @foreach ($columns as $column)
                                                @if ($column != 'id')
                                                    <th scope="col" class="column" id="{{ $column }}">
                                                        <a class="arrow-btn"
                                                            href="{{ $urlOrder }}&sort_by={{ $column }}&sort_order={{ request()->sort_order == 'desc' && request()->sort_by == $column ? 'asc' : 'desc' }}"
                                                            title="@lang('Datagrid::grid.Order descending')"
                                                            class="arrow-down">{{ $column }} <i
                                                                class="fa fa-exchange" aria-hidden="true"></i></a>
                                                    </th>
                                                @endif
                                            @endforeach
                                            <th scope="col">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($data as $row)
                                        <tr>
                                            @if (count($columns) > 1)
                                                <td><input type="checkbox" name="selectedRows[]"
                                                        value="{{ $row['id'] }}"></td>
                                                <td class="column" data-column="id">{{ $rank++ }}</td>
                                                @foreach ($columns as $column)
                                                    @if ($column != 'id')
                                                        <td class="column" data-column="{{ $column }}">
                                                            @if ($column == config('datagrid.' . class_basename($model) . '_has_image'))
                                                                <img src="{{ !empty($row[$column]) ? asset('storage/' . class_basename($model) . '/' . $row[$column]) : asset('storage/default/default.png') }}"
                                                                    alt="Image" style="width: 100px; height: auto;">
                                                            @else
                                                                {{ $row[$column] }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endforeach
                                                <td style="padding: 15px 0px 15px 15px;">
                                                    @if (config('datagrid.' . class_basename($model) . '_has_edit_option'))
                                                        <a href="#" class="edit-button" data-toggle="modal"
                                                            data-target="#editModal" data-id="{{ $row['id'] }}">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </a>
                                                    @endif
                                                    <form id="delete-form" method="POST"
                                                        action="users/{{ $row['id'] }}" style="display: inline-block; margin-left:10px">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="model" id="model"
                                                            value="{{ $model }}">
                                                        <a href="#" class="delete-row" role="button"
                                                            value="Delete user">
                                                            <i class="fas fa-trash-alt" style="color: red;"></i>
                                                        </a>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($columns) + (count($columns) > 1 ? 1 : 2) }}"
                                                class="text-center">@lang('datagrid::grid.No results found.')</td>
                                        </tr>
                                    @endforelse
                                    @if (count($columns) <= 1)
                                        <tr>
                                            <td class="text-center">@lang('datagrid::grid.No Columns selected')</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row table-footer">

                        @php
                            $currentPage = $data->currentPage();
                            $lastPage = $data->lastPage();
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $startPage + 4);
                            if ($endPage - $startPage < 4) {
                                $startPage = max(1, $lastPage - 4);
                                $endPage = $lastPage;
                            }
                        @endphp
                        <div class="col-sm-8 export-import">
                            {{-- @if ($allowExport) --}}
                            <div class="input-group">
                                <select name="export" id="export-type" class="form-control">
                                    <option value="" selected disabled>@lang('datagrid::grid.Select an option to export')</option>
                                    <option value="tsv">TSV</option>
                                    <option value="csv">CSV</option>
                                </select>
                                <!-- Export Button -->
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="export-btn" type="button">Export</button>
                                </div>
                                <form action="{{ route('import.csv') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @method('POST')
                                    @csrf
                                    <input type="hidden" name="model" id="model"
                                        value="{{ $model }}">
                                    <button class="btn btn-primary" type="submit">Import</button>
                                    <input type="hidden" name="allColumns" id="model"
                                        value="{{ json_encode($columnsAll) }}">
                                    <input type="file" name="csv_file">
                                </form>
                            </div>
                        </div>
                        {{-- {{dump($data)}}
                        {{dump($endPage)}}
                        {{dd($startPage)}} --}}
                        @if (count($columns) > 1)
                            <div class="col-sm-4">
                                <nav>
                                    <ul class="pagination">
                                        <li class="page-item {{ $data->currentPage() == 1 ? 'disabled' : '' }}">
                                            <a class="page-link"
                                                href="{{ $data->url($data->currentPage() - 1) . '&' . http_build_query(['rows-per-page' => request()->input('rows-per-page')]) }}"
                                                aria-label="Previous">
                                                <span aria-hidden="true">&lsaquo;</span>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                        </li>
                                        @for ($i = $startPage; $i <= $endPage; $i++)
                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                <a class="page-link"
                                                    href="{{ $urlPagination }}&page={{ $i }}">{{ $i }}</a>
                                            </li>
                                        @endfor
                                        <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                                            <a class="page-link"
                                                href="{{ $data->url($data->currentPage() + 1) . '&' . http_build_query(['rows-per-page' => request()->input('rows-per-page')]) }}"
                                                aria-label="Next">
                                                <span class="sr-only">Next</span>
                                                <span aria-hidden="true">&rsaquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- model code start --}}
<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <input type="hidden" id="edit_id_to_update" value="">
        <input type="hidden" id="image_field_name"
            value="{{ config('datagrid.' . class_basename($model) . '_has_image') }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">{{ 'Edit ' . class_basename($model) . ' Data' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @if (count($columnsAll) > 1)
                        @foreach ($columnsAll as $column)
                            @if ($column != 'id')
                                <div class="form-group">
                                    <label for="{{ $column }}">
                                        <h5>{{ $column }}</h5>
                                    </label>
                                    @if ($column == config('datagrid.' . class_basename($model) . '_has_image'))
                                        <input type="file" class="form-control-file"
                                            id="{{ class_basename($model) }}_{{ $column }}"
                                            name="{{ $column }}">
                                    @else
                                        <input type="text" class="form-control"
                                            id="{{ class_basename($model) }}_{{ $column }}"
                                            name="{{ $column }}"
                                            placeholder="Please enter {{ $column }}">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif
                    <input type="hidden" id="editModelId" name="model_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn" data-dismiss="modal"
                    @if (isset($row['id'])) data-id="{{ $row['id'] }}" @else data-id="" @endif>Save</button>

            </div>
        </div>
    </div>
</div>

{{-- model code end --}}

{{-- create model start --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">{{ 'Create ' . class_basename($model) }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    @if (count($columnsAll) > 1)
                        @foreach ($columnsAll as $column)
                            @if ($column != 'id')
                                <div class="form-group">
                                    <label for="{{ $column }}">
                                        <h5>{{ $column }}</h5>
                                    </label>
                                    @if ($column == config('datagrid.' . class_basename($model) . '_has_image'))
                                        <input type="file" class="form-control-file"
                                            id="{{ class_basename($model) }}_{{ $column }}"
                                            name="{{ $column }}">
                                    @else
                                        <input type="text" class="form-control"
                                            id="{{ class_basename($model) }}_{{ $column }}"
                                            name="{{ $column }}"
                                            placeholder="Please enter {{ $column }}">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif
                    <input type="hidden" id="editModelId" name="model_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDataBtn"
                    @if (isset($row['id'])) data-id="{{ $row['id'] }}" @else data-id="" @endif
                    data-dismiss="modal">Save
                </button>
            </div>
        </div>
    </div>
</div>
{{-- create model end --}}

@include('datagrid::layouts.script')
@php
    $message = Cache::get('message');
    // dd($message);
    $type = Cache::get('message_type');
@endphp

@if ($message)
    <script>
        $(document).ready(function() {
            @if ($type === 'success')
                toastr.success('{{ $message }}');
            @elseif ($type === 'import_error')
                Swal.fire({
                    // title: 'Alert Title',
                    text: '{{ $message }}',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            @else
                toastr.error('{{ $message }}');
            @endif

            @php
                Cache::forget('message');
                Cache::forget('message_type');
            @endphp
        });
    </script>
@endif

<script>
    $(document).ready(function() {
        $('.edit-button').click(function() {
            var id = $(this).data('id');
            var model = @json($model);

            // Populate modal form with existing model data via AJAX
            $.ajax({
                url: '/edit/' + id,
                type: 'GET',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                    model: model
                },
                success: function(response) {
                    console.log(response.id);
                    $('#edit_id_to_update').val(response.id);
                    @if (count($columnsAll) > 1)
                        @foreach ($columnsAll as $column)
                            @if ($column != 'id')
                                $('#{{ class_basename($model) }}_{{ $column }}')
                                    .val(response.{{ $column }});
                            @endif
                        @endforeach
                    @endif
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Handle save changes button click
        $('#saveChangesBtn').click(function() {
            var updateId = $('#edit_id_to_update').val();
            var model = @json($model);
            var imageFieldName = $('#image_field_name').val();

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('model', model);
            formData.append('edit_id', updateId);

            // Add other form fields
            $('#editForm input[type="text"]').each(function() {
                formData.append('form_data[' + $(this).attr('name') + ']', $(this).val());
            });


            // Add file input field if present
            var imageInput = $('#editForm input[type="file"]');
            if (imageInput.length > 0) {
                formData.append(imageFieldName, imageInput[0].files[0]);
            }

            $.ajax({
                url: '{{ url('/update') }}',
                type: 'POST',
                dataType: 'json',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // save data on create

        $('#saveDataBtn').click(function() {
            var model = @json($model);
            var formData = new FormData($('#createForm')[0]);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('model', model);

            $.ajax({
                url: '{{ url('/create') }}',
                type: 'POST',
                dataType: 'json',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    $('#createModal').modal('hide');
                    Swal.fire({
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });


    });
</script>
