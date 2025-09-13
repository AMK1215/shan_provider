@extends('layouts.master')

@section('content')
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card col-11 m-lg-5 m-md-3 m-sm-2 m-2 ">
                <!-- Card header -->
                <div class="card-header pb-0 col-12">
                    <div class="d-lg-flex">
                            <h4 class="mb-0 my-3 fw-bold">Last login</h4>
                    </div>
                </div>
                <div class="table-responsive ">
                    <table id="mytable" class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <th>#</th>
                            <th>User Id</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                        </thead>
                        <tbody>
                            @if (isset($logs))
                                @if (count($logs) > 0)
                                    @foreach ($logs as $log)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="d-block">{{ $log->user->user_name }}</span>
                                            </td>
                                            <td class="text-sm  font-weight-bold">{{ $log->ip_address }}</td>
                                            <td>{{ $log->created_at }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td col-span=8>
                                            There was no Players.
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            {{-- kzt --}}

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('admin_app/assets/js/plugins/datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    {{-- <script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: true
    });
  </script> --}}
    <script>
        if (document.getElementById('users-search')) {
            const dataTableSearch = new simpleDatatables.DataTable("#users-search", {
                searchable: true,
                fixedHeight: false,
                perPage: 7
            });

            document.querySelectorAll(".export").forEach(function(el) {
                el.addEventListener("click", function(e) {
                    var type = el.dataset.type;

                    var data = {
                        type: type,
                        filename: "material-" + type,
                    };

                    if (type === "csv") {
                        data.columnDelimiter = "|";
                    }

                    dataTableSearch.export(data);
                });
            });
        };
    </script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endsection
