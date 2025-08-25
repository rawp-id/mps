@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">BOMs</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">View Products</a>
                <a href="{{ route('components.index') }}" class="btn btn-secondary">View Components</a>
                <a href="{{ route('boms.create') }}" class="btn btn-primary">Add New BOM</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Component</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Usage Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boms as $bom)
                        <tr>
                            <td>{{ $bom->id }}</td>
                            <td>{{ $bom->product->name }}</td>
                            <td>{{ $bom->component->name }}</td>
                            <td>{{ $bom->quantity }}</td>
                            <td>{{ $bom->unit }}</td>
                            <td>{{ $bom->usage_type === 'usage_based' ? 'Usage Based' : 'Consumable' }}</td>
                            <td>
                                <a href="{{ route('boms.show', $bom) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('boms.edit', $bom) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('boms.destroy', $bom) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this BOM?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No BOMs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
