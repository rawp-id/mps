@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Process</h1>

        <form action="{{ route('processes.store') }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code:</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="speed" class="form-label">Speed:</label>
                <input type="number" name="speed" id="speed" class="form-control" required min="0">
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('processes.index') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
@endsection
