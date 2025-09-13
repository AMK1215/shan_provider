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
                        <li class="breadcrumb-item active">Upload Video</li>
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
                    <!-- Upload Video Card -->
                    <div class="card shadow-lg rounded">
                        <div class="card-header bg-gradient-primary text-white">
                            <h4 class="card-title mb-0">Upload Video</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.video-upload.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="video_ads" class="form-label">Select Video</label>
                                    <input type="file" class="form-control" name="video_ads" id="video_ads"
                                        accept="video/*" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Upload Video</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Video List Card -->
                    <div class="card mt-4 shadow-lg rounded">
                        <div class="card-header bg-gradient-secondary text-white">
                            <h4 class="card-title mb-0">Video List</h4>
                        </div>
                        <div class="card-body">
                            <table id="videoTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">Preview</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($videos as $video)
                                        <tr>
                                            <td class="text-center">
                                                <video id="video_{{ $video->id }}" class="video-js vjs-default-skin"
                                                    controls preload="auto" width="480" height="270" data-setup="{}">
                                                    <source src="{{ asset('assets/img/video_ads/' . $video->video_ads) }}"
                                                        type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.video-upload.edit', $video->id) }}"
                                                    class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('admin.video-upload.destroy', $video->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this video?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No videos available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Upload Video Card -->
                    <div class="card shadow-lg rounded">
                        <div class="card-header bg-gradient-primary text-white">
                            <h4 class="card-title mb-0">Upload Video</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.video-upload.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="video_ads" class="form-label">Select Video</label>
                                    <input type="file" class="form-control" name="video_ads" id="video_ads"
                                        accept="video/*" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Upload Video</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Video List Card -->
                    <div class="card mt-4 shadow-lg rounded">
                        <div class="card-header bg-gradient-secondary text-white">
                            <h4 class="card-title mb-0">Video List</h4>
                        </div>
                        <div class="card-body">
                            <table id="videoTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">Preview</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($videos as $video)
                                        <tr>
                                            <td class="text-center">
                                                <video id="video_{{ $video->id }}" class="video-js vjs-default-skin"
                                                    controls preload="auto" width="480" height="270" data-setup="{}">
                                                    <source src="{{ $video->video_url }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center">No videos available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}
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
