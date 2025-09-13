@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Contact</li>
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
                                <h3 class="d-inline fw-bold"> Contact Edit </h3>
                                <a href="{{ route('admin.contact.index') }}" class="btn btn-primary float-right"><i
                                    class="fas fa-arrow-left text-white  "></i>Back</a>
                           </div>
                        </div>
                        <div class="card-body  col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1">
                            <form role="form" class="text-start" action="{{ route('admin.contact.update', $contact->id) }}"
                                method="post">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="name">Type</label>
                                    <select name="type_id" id="" class="form-control">
                                        <option value="">Select Type</option>
                                        @foreach($types as $type)
                                        <option value="{{$type->id}}" {{$type->id == $contact->type_id ? 'selected' : ''}}>{{$type->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('type_id')
                                        <span class="text-danger d-block">*{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark" for="text">Name</label>
                                    <input type="text" class="form-control border border-1 border-secondary px-3"
                                        id="text" name="name" value="{{ $contact->name }}">
                                    @error('name')
                                        <span class="text-danger d-block">*{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark" for="text">Phone or Account name</label>
                                    <input type="text" class="form-control border border-1 border-secondary px-3"
                                        id="value" name="value" value="{{ $contact->value }}">
                                    @error('value')
                                        <span class="text-danger d-block">*{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
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
