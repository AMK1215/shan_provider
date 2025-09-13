@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">BannerText</li>
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

                    <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1" style="border-radius: 15px;">
                        <div class="card-header">
                            <div class="card-title col-12">
                                <h3 class="d-inline fw-bold">Banner Ads Edit </h3>
                                <a href="{{ route('admin.adsbanners.index') }}" class="btn btn-primary float-right"><i
                                    class="fas fa-arrow-left text-white  "></i>Back</a>
                           </div>
                        </div>
                        <div class="card-body col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1">
                            <form role="form" class="text-start"
                                action="{{ route('admin.adsbanners.update', ['adsbanner' => $adsbanner->id]) }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="custom-form-group mb-3">
                                    <label for="title">Ads Banner Image</label>
                                    <input type="file" class="form-control" id="inputEmail3" name="image">
                                    <img src="{{ $adsbanner->img_url }}" width="150px" class="img-thumbnail"
                                        alt="">
                                </div>
                                <div class="custom-form-group mb-3">
                                        <button class="btn btn-success float-right" type="submit" style="width:70px;">Edit</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection
