@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Products</h1>
    <div class="d-flex align-items-center">
        <a href="{{ route('plan.generate')}}" class="btn btn-secondary me-2">Generate Plan</a>
        {{-- <a href="{{ route('reset')}}" class="btn btn-secondary me-2">Reset</a> --}}
        <a href="{{ route('reset')}}" class="btn btn-secondary me-2">Reset</a>
        {{-- <a href="{{ route('products.import') }}" class="btn btn-secondary me-2">Import Products</a> --}}
        <a href="{{ route('products.import') }}" class="btn btn-secondary me-2">Import Products</a>
        {{-- <a href="{{ route('products.export') }}" class="btn btn-success">Export Products</a> --}}
        <a href="{{ route('products.create') }}" class="btn btn-primary">Create New Product</a>
    </div>
</div>

@if($products->isEmpty())
    <div class="alert alert-info">No products found. <a href="{{ route('products.create') }}">Create one</a>.</div>
@else
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Shipping Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->code }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->shipping_date ?? '-' }}</td>
                <td>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-warning btn-sm">View</a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-info btn-sm">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $products->links('pagination::bootstrap-5') }}
@endif
@endsection
