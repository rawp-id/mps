@extends('layouts.app')

@section('content')
    <h2>Edit CO</h2>

    <form action="{{ route('co.update', $co->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $co->name }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required>{{ $co->description }}</textarea>
        </div>

        <div class="mb-3">
            <label for="shipping_date" class="form-label">Shipping Date</label>
            <input type="datetime-local" class="form-control" id="shipping_date" name="shipping_date" value="{{ $co->shipping_date }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('co.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
