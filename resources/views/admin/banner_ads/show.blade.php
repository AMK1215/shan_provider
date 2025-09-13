@extends('layouts.master')
@section('styles')
<style>
  .transparent-btn {
    background: none;
    border: none;
    padding: 0;
    outline: none;
    cursor: pointer;
    box-shadow: none;
    appearance: none;
    /* For some browsers */
  }


  .custom-form-group {
    margin-bottom: 20px;
  }

  .custom-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
  }

  .custom-form-group input,
  .custom-form-group select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #e1e1e1;
    border-radius: 5px;
    font-size: 16px;
    color: #333;
  }

  .custom-form-group input:focus,
  .custom-form-group select:focus {
    border-color: #d33a9e;
    box-shadow: 0 0 5px rgba(211, 58, 158, 0.5);
  }

  .submit-btn {
    background-color: #d33a9e;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
  }

  .submit-btn:hover {
    background-color: #b8328b;
  }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/material-icons@1.13.12/iconfont/material-icons.min.css">
@endsection
@section('content')
<div class="row justify-content-center">
  <div class="col-12">
    <div class="container mt-5">
      <div class="card col-lg-8 offset-lg-2 col-md-8 offset-md-2 col-sm-8 offset-sm-2 col-10 offset-1" style="border-radius: 15px;">

            <div class="card-title col-12 mt-3">
                <h3 class="d-inline fw-bold">Banner's Detail </h3>
                <a href="{{ route('admin.adsbanners.index') }}" class="btn btn-danger float-right"><i
                    class="fas fa-arrow-left text-white  "></i>Back</a>
           </div>

       <div class="card-body ">
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <tbody>
                          <tr>
                              <th>ID</th>
                              <td>{!! $adsbanner->id !!}</td>
                          </tr>
                          <tr>
                              <th>Image</th>
                              <td>
                                  @if ($adsbanner->img_url)
                                      <img src="{{ $adsbanner->img_url }}" width="150px" class="img-thumbnail" alt="">
                                  @else
                                      No Image
                                  @endif
                              </td>
                          </tr>
                          <tr>
                              <th>Create Date</th>
                              <td>{!! $adsbanner->created_at ? $adsbanner->created_at->format('F j, Y') : 'N/A' !!}</td>
                          </tr>
              </tbody>
            </table>
          </div>
       </div>
      </div>
    </div>
  </div>
</div>


@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>

<script src="{{ asset('admin_app/assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('admin_app/assets/js/plugins/quill.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>


@endsection
