@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('Manage Categories') }}</span>
                <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary">
                    {{ __('Add New Category') }}
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Color') }}</th>
                            <th>{{ __('Feeds') }}</th>
                            <th>{{ __('Parent') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-categories">
                        @forelse ($categories as $category)
                            <tr data-id="{{ $category->id }}" class="sortable-item">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="sortable-handle me-2">☰</span>
                                        <a href="{{ route('articles.index', ['category' => $category->id]) }}">{{ $category->name }}</a>
                                    </div>
                                </td>
                                <td>
                                    @if ($category->color)
                                        <span class="badge" style="background-color: {{ $category->color }};">&nbsp;&nbsp;&nbsp;</span>
                                        <small class="text-muted">{{ $category->color }}</small>
                                    @else
                                        <span class="text-muted">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $category->feeds()->count() }}
                                </td>
                                <td>
                                    @if ($category->parent)
                                        <a href="{{ route('articles.index', ['category' => $category->parent_id]) }}">{{ $category->parent->name }}</a>
                                    @else
                                        <span class="text-muted">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('articles.index', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this category?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="mb-0">{{ __('No categories found.') }}</p>
                                    <a href="{{ route('categories.create') }}" class="btn btn-primary mt-3">
                                        {{ __('Add Your First Category') }}
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Enable drag-and-drop sorting for categories
    document.addEventListener('DOMContentLoaded', function() {
        const sortable = document.getElementById('sortable-categories');

        if (sortable && sortable.children.length > 1) {
            // Initialize sortable functionality (placeholder code - requires a sorting library)
            // In a real implementation, you would use a library like SortableJS or jQuery UI Sortable

            // After sorting, update order via AJAX
            function updateOrder() {
                const items = Array.from(sortable.children);
                const order = items.map(item => item.dataset.id);

                // Send order to server
                fetch('{{ route('categories.order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Order updated successfully');
                    }
                })
                .catch(error => {
                    console.error('Error updating order:', error);
                });
            }
        }
    });
</script>
@endpush
