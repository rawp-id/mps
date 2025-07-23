@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Machine</h1>
        <form action="{{ route('machines.store') }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">Capacity:</label>
                <input type="number" id="capacity" name="capacity" class="form-control" value="0" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('machines.index') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
@endsection
