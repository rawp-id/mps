@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Machine</h1>
        <form action="{{ route('machines.update', $machine) }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" value="{{ $machine->name }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">Capacity:</label>
                <input type="number" id="capacity" name="capacity" value="{{ $machine->capacity }}" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('machines.index') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
@endsection
