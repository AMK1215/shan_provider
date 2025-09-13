@extends('layouts.master')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card col-11 m-lg-5 m-md-5 m-sm-3 m-3 pb-3" style="border-radius: 20px;">
                <!-- Card header -->
                <div class="card-header mb-2">
                    <div class="d-lg-flex">
                        <div>
                            <h3 class="mb-0 my-3 fw-bold">Transfer Log Detail</h3>

                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="mytable" class="table table-bordered table-hover">

                        <thead class="thead-light">

                            <tr>
                                <th>Date</th>
                                <th>PlayerId</th>
                                <th>PlayerName</th>
                                <th>Old Balance</th>
                                <th>Amount</th>
                                <th>New Balance</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transferLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->targetUser->user_name }}</td>
                                    <td>{{ $log->targetUser->name }}</td>
                                    <th>{{ $log->old_balance}}</th>
                                    <td>
                                        @if ($log->type == 'withdraw')
                                            <p class="text-success font-weight-bold"> {{ abs($log->amountFloat) }}</p>
                                        @else
                                            <p class="text-danger font-weight-bold"> {{ abs($log->amountFloat) }}</p>
                                        @endif
                                    </td>
                                    <td>{{ $log->new_balance}}</td>
                                    <td>
                                        @if ($log->type == 'deposit')
                                            <p class="text-danger font-weight-bold">Withdraw</p>
                                        @else
                                            <p class="text-success font-weight-bold">Deposit</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>




                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('admin_app/assets/js/plugins/datatables.js') }}"></script>
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
