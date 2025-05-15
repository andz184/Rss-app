@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">{{ __('Add New Feed') }}</div>

            <div class="card-body">
                <form method="POST" action="{{ route('feeds.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <label for="feed_url" class="col-md-4 col-form-label text-md-end">{{ __('Feed URL') }}</label>

                        <div class="col-md-6">
                            <input id="feed_url" type="url" class="form-control @error('feed_url') is-invalid @enderror" name="feed_url" value="{{ old('feed_url') }}" required autofocus>

                            @error('feed_url')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Enter the URL of the RSS/Atom feed.') }}
                            </small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="category_id" class="col-md-4 col-form-label text-md-end">{{ __('Category') }}</label>

                        <div class="col-md-6">
                            <select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                <option value="">{{ __('Uncategorized') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Add Feed') }}
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
