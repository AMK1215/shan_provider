@extends('layouts.master')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/material-icons@1.13.12/iconfont/material-icons.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="container mb-3">
        </div>
        <div class="container my-auto mt-5">
            <div class="row mt-5">
                <div class="col-lg-10 col-md-2 col-12 mx-auto">
                    <div class="card z-index-0 fadeIn3 fadeInBottom">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg py-2 pe-1">
                                <h4 class="text-white font-weight-bolder text-center mb-2">Edit Game Image</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.game_list.update_image_url', $gameList->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="custom-form-group">
                                    <label for="image">Game Image</label>
                                    <input type="file" class="form-control" name="image" required>
                                </div>

                                <div class="custom-form-group mt-3">
                                    <button class="btn btn-primary" type="submit">Update</button>
                                    <a class="btn btn-icon btn-2 btn-danger float-end me-5" href="{{ route('admin.gameLists.index') }}">
                                        Cancel</span>
                                    </a>

                                </div>
                            </form>

                            <div class="custom-form-group mt-3">
                                <img src="{{ asset($gameList->image_url) }}" alt="{{ $gameList->game_name }}"
                                    width="100px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection