@extends('layouts.app')
@section('title', 'Report List')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Reports</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
    {{-- Search Form --}}
    <form method="GET" action="{{ route('reports.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search reports..."
                value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>
    @if ($products->isEmpty())
        <div class="alert alert-info">No products found.</div>
    @else
        <table class="table table-bordered table-striped mb-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ $product->stock ?? '-' }}</td>
                        <td class="d-flex justify-content-start d-inline">
                            <a href="{{route('reports.show', $product->id)}}" class="btn btn-info btn-sm me-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    {{ $products->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
@endsection
