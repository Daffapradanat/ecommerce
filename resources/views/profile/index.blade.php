@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">{{ __('administrator.profile') }}</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="profile-image-container">
                                        @php
                                            $imageUrl = $user->image ? asset('storage/users/' . $user->image) : asset('default-avatar.png');
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $user->name }}" class="img-fluid rounded-circle profile-image" id="profileImagePreview">
                                        <div class="image-overlay d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-light btn-sm change-photo-btn" onclick="document.getElementById('imageUpload').click()">
                                                {{ __('administrator.profile_image') }}
                                            </button>
                                        </div>
                                    </div>
                                    <input type="file" id="imageUpload" name="image" style="display: none;" accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('administrator.name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}">
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('administrator.email') }}</label>
                                    <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">{{ __('administrator.role') }}</label>
                                    <input type="text" class="form-control" id="role" value="{{ $user->role ? $user->role->name : 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <button type="submit" class="btn btn-primary">{{ __('administrator.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .profile-image-container {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .profile-image-container:hover .image-overlay {
        opacity: 1;
    }
    .profile-image-container:hover .profile-image {
        transform: scale(1.1);
    }
    .change-photo-btn {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('profileImagePreview').src = e.target.result;
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
