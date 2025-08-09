@extends('layouts.app')

@section('content')
    <h2>CO Details</h2>

    <div>
        <strong>Name:</strong> {{ $co->name }}
    </div>
    <div>
        <strong>Description:</strong> {{ $co->description }}
    </div>
    <div>
        <strong>Shipping Date:</strong> {{ $co->shipping_date }}
    </div>

    <div class="mt-3">
        <a href="{{ route('co.index') }}" class="btn btn-secondary">Back to List</a>
        <a href="{{ route('co.edit', $co->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('co.destroy', $co->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
@endsection
