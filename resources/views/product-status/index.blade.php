@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Product Status</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('product-status.create') }}" class="btn btn-primary">Add New Product Status</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productStatuses as $productStatus)
                        <tr>
                            <td>{{ $productStatus->id }}</td>
                            <td>{{ $productStatus->code }}</td>
                            <td>{{ $productStatus->description }}</td>
                            <td>
                                <a href="{{ route('product-status.show', $productStatus) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('product-status.edit', $productStatus) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('product-status.destroy', $productStatus) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this product status?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No product statuses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
