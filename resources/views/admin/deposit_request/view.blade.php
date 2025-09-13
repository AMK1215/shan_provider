@extends('layouts.master')
@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/material-icons@1.13.12/iconfont/material-icons.min.css">
<style>
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.95rem;
    }
    .info-value {
        font-size: 1.1rem;
        color: #222;
    }
    .info-row {
        margin-bottom: 1.1rem;
    }
    .card-detail {
        border-radius: 18px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        padding: 2rem 2.5rem;
    }
    .img-preview {
        max-width: 180px;
        border-radius: 10px;
        border: 1px solid #eee;
        margin-bottom: 1.5rem;
    }
    .section-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.7rem;
        color: #3b5998;
    }
    .badge-status {
        font-size: 1rem;
        padding: 0.5em 1.2em;
        border-radius: 1.5em;
        margin-bottom: 1.2rem;
    }
    .action-buttons {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        margin-right: 1rem;
    }
</style>
@endsection
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Deposit Request Detail</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card card-detail mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Deposit Request Detail</h4>
                        <a href="{{ route('admin.agent.deposit') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left mr-1"></i>Back</a>
                    </div>
                    <div class="mb-3">
                        @if ($deposit->status == 0)
                            <span class="badge badge-warning badge-status"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>
                        @elseif ($deposit->status == 1)
                            <span class="badge badge-success badge-status"><i class="fas fa-check-circle mr-1"></i>Approved</span>
                        @elseif ($deposit->status == 2)
                            <span class="badge badge-danger badge-status"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ asset('assets/img/deposit/' . $deposit->image) }}" class="img-preview mb-2" alt="Deposit Slip">
                            <div class="info-label">Deposit Slip</div>
                        </div>
                        <div class="col-md-8">
                            <div class="section-title">Transaction Info</div>
                            <div class="info-row row">
                                <div class="col-5 info-label">User Name</div>
                                <div class="col-7 info-value"><i class="fas fa-user mr-1 text-primary"></i>{{ $deposit->user->name }}</div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Amount</div>
                                <div class="col-7 info-value"><i class="fas fa-coins mr-1 text-warning"></i>{{ number_format($deposit->amount) }}</div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Reference No</div>
                                <div class="col-7 info-value"><i class="fas fa-receipt mr-1 text-info"></i>{{ $deposit->refrence_no }}</div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Date & Time</div>
                                <div class="col-7 info-value"><i class="fas fa-calendar-alt mr-1 text-success"></i>{{ $deposit->created_at->setTimezone('Asia/Yangon')->format('d-m-Y H:i:s') }}</div>
                            </div>
                            <div class="section-title mt-4">Bank Info</div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Bank Account Name</div>
                                <div class="col-7 info-value"><i class="fas fa-id-card mr-1 text-secondary"></i>{{ $deposit->bank->account_name }}</div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Bank Account No</div>
                                <div class="col-7 info-value"><i class="fas fa-hashtag mr-1 text-dark"></i>{{ $deposit->bank->account_number }}</div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Payment Method</div>
                                <div class="col-7 info-value"><i class="fas fa-university mr-1 text-primary"></i>{{ $deposit->bank->paymentType->name }}</div>
                            </div>
                            <div class="section-title mt-4">Approval Info</div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Approved/Rejected By</div>
                                <div class="col-7 info-value">
                                    @if ($deposit->status == 1)
                                        <i class="fas fa-user-check mr-1 text-success"></i>
                                    @elseif ($deposit->status == 2)
                                        <i class="fas fa-user-times mr-1 text-danger"></i>
                                    @else
                                        <i class="fas fa-user-clock mr-1 text-warning"></i>
                                    @endif
                                    {{ $deposit->sub_agent_name ?? '-' }}
                                </div>
                            </div>
                            <div class="info-row row">
                                <div class="col-5 info-label">Note</div>
                                <div class="col-7 info-value"><i class="fas fa-sticky-note mr-1 text-info"></i>{{ $deposit->note ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons for Pending Deposits -->
                    @if ($deposit->status == 0)
                        <div class="action-buttons">
                            <div class="section-title">Actions</div>
                            <div class="d-flex align-items-center">
                                <form action="{{ route('admin.agent.depositStatusUpdate', $deposit->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{ $deposit->amount }}">
                                    <input type="hidden" name="status" value="1">
                                    <input type="hidden" name="player" value="{{ $deposit->user->id }}">
                                    <button class="btn btn-success btn-action" type="submit" onclick="return confirm('Are you sure you want to approve this deposit request?')">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.agent.depositStatusreject', $deposit->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="2">
                                    <button class="btn btn-danger btn-action" type="submit" onclick="return confirm('Are you sure you want to reject this deposit request?')">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: successMessage,
            background: 'hsl(230, 40%, 10%)',
            timer: 3000,
            showConfirmButton: false
        });
        @elseif(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            background: 'hsl(230, 40%, 10%)',
            timer: 3000,
            showConfirmButton: false
        });
        @endif
    });
</script>
@endsection