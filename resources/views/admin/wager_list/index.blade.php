@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Wager List</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Wager List</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end mb-3">
                    <a href="" class="btn btn-success " style="width: 100px;"><i
                            class="fas fa-plus text-white  mr-2"></i>Create</a>
                </div>
                <div class="card">
                    <!-- wager list start_date and end_date form here-->
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="date" id="start_date">
                            <input type="date" id="end_date">
                            <input type="text" id="member_account" placeholder="Member Account">
                            <select id="status" class="mx-1">
                                <option value="">All Status</option>
                                <option value="BET">BET</option>
                                <option value="SETTLED">SETTLED</option>
                            </select>
                            <button id="fetchWagers" class="btn btn-primary">Fetch</button>
                            <button id="exportCSV" class="btn btn-success mx-1">Export to CSV</button>
                            <span id="loading" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Loading...</span>
                        </div>
                        <div id="error-message" class="alert alert-danger" style="display:none;"></div>
                        <div id="pagination-controls" class="mb-3"></div>
                        <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member Account</th>
                                    <th>Round ID</th>
                                    <th>Currency</th>
                                    <th>Provider ID</th>
                                    <th>Provider Line ID</th>
                                    <th>Game Type</th>
                                    <th>Game Code</th>
                                    <th>Valid Bet Amount</th>
                                    <th>Bet Amount</th>
                                    <th>Prize Amount</th>
                                    <th>Status</th>
                                    <th>Settled At (Date)</th>
                                    <th>Created At (Date)</th>
                                    <th>Updated At (Date)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
           
        </div>
    </div>
</section>
@endsection
@section('script')
<script>
let currentPage = 1;
let pageSize = 100;

function formatDate(ts) {
    if (!ts || ts == 0) return '';
    let d = new Date(Number(ts));
    return d.toLocaleString();
}

function fetchWagers(page = 1) {
    $('#error-message').hide();
    $('#mytable tbody').html('');
    $('#loading').show();
    $('#fetchWagers').prop('disabled', true);

    let start = $('#start_date').val();
    let end = $('#end_date').val();
    let member_account = $('#member_account').val();
    let status = $('#status').val();
    let offset = (page - 1) * pageSize;

    $.ajax({
        url: '{{ route("admin.wager-list.fetch") }}',
        method: 'GET',
        data: { start_date: start, end_date: end, member_account: member_account, status: status, offset: offset, size: pageSize },
        success: function(res) {
            let rows = '';
            if (res.wagers.length === 0) {
                rows = `<tr><td colspan=\"15\" class=\"text-center\">No data found</td></tr>`;
            } else {
                res.wagers.forEach(function(wager) {
                    rows += `<tr>
                        <td>${wager.id}</td>
                        <td>${wager.member_account}</td>
                        <td>${wager.round_id}</td>
                        <td>${wager.currency}</td>
                        <td>${wager.provider_id}</td>
                        <td>${wager.provider_line_id}</td>
                        <td>${wager.game_type}</td>
                        <td>${wager.game_code}</td>
                        <td>${wager.valid_bet_amount}</td>
                        <td>${wager.bet_amount}</td>
                        <td>${wager.prize_amount}</td>
                        <td>${wager.status}</td>
                        <td>${formatDate(wager.settled_at)}</td>
                        <td>${formatDate(wager.created_at)}</td>
                        <td>${formatDate(wager.updated_at)}</td>
                        <td><a href='${wager.id ? `/admin/wager-list/${wager.id}` : '#'}' class='btn btn-sm btn-info' target='_blank'>View</a></td>
                    </tr>`;
                });
            }
            $('#mytable tbody').html(rows);
            // Pagination
            let total = res.pagination.total;
            let totalPages = Math.ceil(total / pageSize);
            let paginationHtml = '';
            for(let i=1; i<=totalPages; i++) {
                paginationHtml += `<button class='btn btn-sm btn-secondary mx-1' onclick='fetchWagers(${i})' ${i===page?'disabled':''}>${i}</button>`;
            }
            $('#pagination-controls').html(paginationHtml);
            currentPage = page;
        },
        error: function(xhr) {
            let msg = 'An error occurred while fetching data.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $('#error-message').text(msg).show();
        },
        complete: function() {
            $('#loading').hide();
            $('#fetchWagers').prop('disabled', false);
        }
    });
}

$('#fetchWagers').on('click', function() {
    fetchWagers(1);
});

$('#exportCSV').on('click', function() {
    let params = {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        member_account: $('#member_account').val(),
        status: $('#status').val()
    };
    let query = $.param(params);
    window.location = '{{ route("admin.wager-list.export-csv") }}?' + query;
});
</script>
@endsection
