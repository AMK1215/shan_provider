@extends('layouts.master')
@section('style')
    <link rel="stylesheet" href="{{ asset('css/video-js.css') }}">
@endsection
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Video</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Edit Video Card -->
                    <div class="card shadow-lg rounded">
                        <div class="card-header bg-gradient-primary text-white">
                            <h4 class="card-title mb-0">Edit Video</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.video-upload.update', $video->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT') <!-- Use PUT method for updates -->

                                <!-- Current Video Preview -->
                                <div class="form-group text-center">
                                    <label>Current Video</label>
                                    <video class="video-js vjs-default-skin" controls preload="auto" width="480"
                                        height="270" data-setup="{}">
                                        <source src="{{ asset('assets/img/video_ads/' . $video->video_ads) }}"
                                            type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>

                                <!-- Video Upload Field -->
                                <div class="form-group">
                                    <label for="video_ads" class="form-label">Upload New Video</label>
                                    <input type="file" class="form-control" name="video_ads" id="video_ads"
                                        accept="video/*">
                                    <small class="text-muted">Leave blank to keep the current video.</small>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Update Video</button>
                                    <a href="{{ route('admin.video-upload.index') }}"
                                        class="btn btn-secondary btn-lg">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('js/video.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#videoTable').DataTable();
        });
    </script>

    @if (session()->has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif
@endsection
