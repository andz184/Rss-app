@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">{{ __('Edit Feed') }}</div>

            <div class="card-body">
                <form method="POST" action="{{ route('feeds.update', $feed) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label for="title" class="col-md-4 col-form-label text-md-end">{{ __('Title') }}</label>

                        <div class="col-md-6">
                            <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $feed->title) }}" required>

                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="feed_url" class="col-md-4 col-form-label text-md-end">{{ __('Feed URL') }}</label>

                        <div class="col-md-6">
                            <input id="feed_url" type="url" class="form-control @error('feed_url') is-invalid @enderror" name="feed_url" value="{{ old('feed_url', $feed->feed_url) }}" required>

                            @error('feed_url')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="category_id" class="col-md-4 col-form-label text-md-end">{{ __('Category') }}</label>

                        <div class="col-md-6">
                            <select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                <option value="">{{ __('Uncategorized') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $feed->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="is_active" class="col-md-4 col-form-label text-md-end">{{ __('Status') }}</label>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $feed->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                            </div>
                            <small class="form-text text-muted">
                                {{ __('Inactive feeds will not be updated automatically.') }}
                            </small>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Update Feed') }}
                            </button>
                            <a href="{{ route('feeds.index') }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
