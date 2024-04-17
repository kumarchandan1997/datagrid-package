<script src="{{ asset('data-grid/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('data-grid/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('data-grid/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('data-grid/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

<script src="{{ asset('data-grid/assets/vendor/js/menu.js') }}"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('data-grid/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('data-grid/assets/js/main.js') }}"></script>
<script src="{{ asset('data-grid/assets/vendor/js/custom.js') }}"></script>

<!-- Page JS -->
<script src="{{ asset('data-grid/assets/js/dashboards-analytics.js') }}"></script>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

{{-- sweet alert --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11"> --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- font awsome link --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">




<!-- Bootstrap JS and jQuery (required for Bootstrap functionality) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        toastr.options = {
            'closeButton': true,
            'debug': false,
            'newestOnTop': false,
            'progressBar': true,
            'positionClass': 'toast-top-right',
            'preventDuplicates': false,
            'showDuration': '1000',
            'hideDuration': '1000',
            'timeOut': '2000',
            'extendedTimeOut': '1000',
            'showEasing': 'swing',
            'hideEasing': 'linear',
            'showMethod': 'fadeIn',
            'hideMethod': 'fadeOut',
        }


        $('.delete-row').click(function(event) {
            event.preventDefault();
            var deleteForm = $(this).closest('form');

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteForm.attr('action'),
                        type: 'DELETE',
                        data: deleteForm.serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Your data has been deleted.",
                                    icon: "success"
                                }).then(() => {
                                    location
                                        .reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: "Error!",
                                text: "An error occurred while deleting the lead.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });


        $('th input[type="checkbox"]').change(function() {
            if ($(this).is(':checked')) {
                $('td input[type="checkbox"]').prop('checked', true);
            } else {
                $('td input[type="checkbox"]').prop('checked', false);
            }
        });


        $('#delete-selected').click(function() {
            var selectedRows = $('input[name="selectedRows[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedRows.length > 0) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        var csrfToken = $('meta[name="csrf-token"]').attr('content');
                        var model = @json($model);
                        $.ajax({
                            url: '{{ route('datagrid.bulkDelete') }}',
                            type: 'POST',
                            data: {
                                selectedRows: selectedRows,
                                _token: '{{ csrf_token() }}',
                                model: model
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: response.message,
                                        icon: "success"
                                    }).then(() => {
                                        window.location.reload(true);
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: response.message,
                                        icon: "error"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    }
                });
            } else {
                Swal.fire("Please select at least one row to delete.");
            }
        });


        $('#export-btn').click(function() {
            var filterSearch = $('#search').val();
            var searchColumns = @json($searchColumns);
            var model = @json($model);
            var exportType = $('#export-type').val();
            if (!exportType) {
                alert('Select a format for export!');
                return false;
            }
            $.ajax({
                url: '{{ route('export.data') }}',
                method: 'POST',
                async: false,
                dataType: 'json',
                data: {
                    searchColumns: searchColumns,
                    allColumns: @json($columnsAll),
                    filterSearch: filterSearch,
                    exportType: exportType,
                    _token: '{{ csrf_token() }}',
                    model: model
                },
                success: function(response) {
                    var fileUrl = response.file_url;
                    var fileName = response.file_name;
                    var downloadLink = document.createElement("a");
                    downloadLink.href = fileUrl;
                    downloadLink.download =
                        fileName;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    setTimeout(() => {
                        toastr.success(response.message);
                    }, 1000);
                    setTimeout(() => {
                        fetch('/delete-file?fileUrl=' + encodeURIComponent(fileUrl))
                            .then(response => {})
                            .catch(error => {
                                console.error('Error deleting file:', error);
                            });
                    }, 3000);
                },
                error: function(xhr, status, error) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.errorMessage) {
                            toastr.error(response.errorMessage);
                        } else {
                            console.error();
                            toastr.error("An unknown error occurred to export file.");
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response: " + e.message);
                    }
                }
            });
        });
    });
</script>
