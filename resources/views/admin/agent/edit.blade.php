@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                    <li class="breadcrumb-item active">Edit Agent</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card  col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1" style="border-radius: 15px;">
            <div class="card-header">
                   <div class="card-title col-12">
                    <h5 class="d-inline fw-bold">Edit Agent</h5>
                    <a href="{{ route('admin.agent.index') }}" class="btn btn-primary float-right">
                        <i class="fas fa-arrow-left" style="font-size: 20px;"></i> Back
                    </a>

                </div>
            </span>
            </h3>
            </div>
            <form method="POST" action="{{ route('admin.agent.update',$agent->id) }}">
                  @csrf
                  @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 offset-lg-2 col-md-8 offset-md-2 col-sm-8 offset-sm-2 col-10 offset-1">
                            <!-- Basic Information -->
                            <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Basic Information</h6>
                            
                            <div class="form-group">
                                <label>Agent ID<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_name" value="{{$agent->user_name}}" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ $agent->name }}" required>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Phone<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" value="{{ $agent->phone }}" required>
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="{{ $agent->email }}">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Profile Description</label>
                                <textarea class="form-control" name="profile" rows="3" placeholder="Agent profile description">{{ $agent->profile }}</textarea>
                                @error('profile')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Agent Logo</label>
                                <input type="text" class="form-control" name="agent_logo" value="{{ $agent->agent_logo }}" placeholder="logo-filename.png">
                                <small class="form-text text-muted">Current logo: {{ $agent->agent_logo ?? 'default.png' }}</small>
                                @error('agent_logo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Financial Settings -->
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-money-bill-wave"></i> Financial Settings</h6>
                            
                            <div class="form-group">
                                <label>Max Score</label>
                                <input type="number" step="0.01" class="form-control" name="max_score" value="{{ $agent->max_score }}">
                                @error('max_score')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Commission (%)</label>
                                <input type="number" step="0.01" class="form-control" name="commission" value="{{ $agent->commission }}">
                                @error('commission')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Type</label>
                                <select class="form-control" name="payment_type_id">
                                    <option value="">Select Payment Type</option>
                                    @foreach($paymentTypes as $paymentType)
                                        <option value="{{ $paymentType->id }}" {{ $agent->payment_type_id == $paymentType->id ? 'selected' : '' }}>
                                            {{ $paymentType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_type_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Banking Information -->
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-university"></i> Banking Information</h6>
                            
                            <div class="form-group">
                                <label>Account Name</label>
                                <input type="text" class="form-control" name="account_name" value="{{ $agent->account_name }}">
                                @error('account_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Account Number</label>
                                <input type="text" class="form-control" name="account_number" value="{{ $agent->account_number }}">
                                @error('account_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact & Site Information -->
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-globe"></i> Contact & Site Information</h6>
                            
                            <div class="form-group">
                                <label>Line ID</label>
                                <input type="text" class="form-control" name="line_id" value="{{ $agent->line_id }}">
                                @error('line_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Referral Code</label>
                                <input type="text" class="form-control" name="referral_code" value="{{ $agent->referral_code }}">
                                @error('referral_code')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" class="form-control" name="site_name" value="{{ $agent->site_name }}">
                                @error('site_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Site Link</label>
                                <input type="url" class="form-control" name="site_link" value="{{ $agent->site_link }}" placeholder="https://example.com">
                                @error('site_link')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shan Game Configuration -->
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-gamepad"></i> Shan Game Configuration</h6>
                            
                            <div class="form-group">
                                <label>Shan Agent Code<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="shan_agent_code" value="{{ $agent->shan_agent_code }}" required>
                                <small class="form-text text-muted">Unique code for Shan game integration</small>
                                @error('shan_agent_code')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Shan Agent Name</label>
                                <input type="text" class="form-control" name="shan_agent_name" value="{{ $agent->shan_agent_name }}">
                                @error('shan_agent_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Shan Secret Key<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="shan_secret_key" value="{{ $agent->shan_secret_key }}" required>
                                <small class="form-text text-muted">Secret key for secure Shan game transactions</small>
                                @error('shan_secret_key')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Shan Callback URL<span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="shan_callback_url" value="{{ $agent->shan_callback_url }}" required placeholder="https://example.com/api/shan/callback">
                                <small class="form-text text-muted">URL for Shan game transaction callbacks</small>
                                @error('shan_callback_url')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Settings -->
                            <h6 class="text-primary mb-3 mt-4"><i class="fas fa-cog"></i> Status Settings</h6>
                            
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                    <option value="1" {{ $agent->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $agent->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>User Type</label>
                                <input type="text" class="form-control" value="{{ $agent->type == 10 ? 'Owner' : ($agent->type == 20 ? 'Agent' : 'Other') }}" readonly>
                                <input type="hidden" name="type" value="{{ $agent->type }}">
                                <small class="form-text text-muted">User type cannot be changed after creation</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Password Change Required</label>
                                <select class="form-control" name="is_changed_password">
                                    <option value="1" {{ $agent->is_changed_password == 1 ? 'selected' : '' }}>Yes - Must change password on next login</option>
                                    <option value="0" {{ $agent->is_changed_password == 0 ? 'selected' : '' }}>No - Current password is fine</option>
                                </select>
                                @error('is_changed_password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer col-12 bg-white">
                    <button type="submit" class="btn btn-success float-right">Update</button>
                </div>
            </form>
        </div>

    </div>
    </div>
</section>
@endsection
