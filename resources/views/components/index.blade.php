@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 fw-bold">Components</h1>
            <a href="{{ route('components.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg"></i> Create New Component
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 40px;">#</th>
                                <th scope="col">Code</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Stock</th>
                                <th scope="col" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($components as $component)
                                <tr>
                                    <td>{{ $component->id }}</td>
                                    <td>{{ $component->code }}</td>
                                    <td>{{ $component->name }}</td>
                                    <td>{{ $component->description }}</td>
                                    <td>{{ $component->unit }}</td>
                                    <td>{{ $component->stock }}</td>
                                    <td>
                                        <a href="{{ route('components.show', $component) }}" class="btn btn-outline-info btn-sm me-1">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('components.edit', $component) }}" class="btn btn-outline-warning btn-sm me-1">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('components.destroy', $component) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Delete this component?')" type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No components found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
