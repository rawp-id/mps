@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Process</h1>

        <form action="{{ route('processes.update', $process) }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Code:</label>
                <input type="text" name="code" value="{{ $process->code }}" required class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input type="text" name="name" value="{{ $process->name }}" required class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Speed:</label>
                <input type="number" name="speed" value="{{ $process->speed }}" required min="0" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('processes.index') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
@endsection
