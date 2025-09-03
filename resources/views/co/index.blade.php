@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>CO List</h2>
        <a href="{{ route('co.create') }}" class="btn btn-primary">Create Data</a>
    </div>
    <span>Updated at: {{ now()->format('Y-m-d H:i:s') }}</span>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Product</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cos as $co)
                <tr>
                    <td>
                        {{ $co->name }}
                    </td>
                    <td>
                        {{ $co->description }}
                    </td>
                    <td>
                        @foreach ($co->coProducts as $coProduct)
                            {{ $coProduct->product->name }}<br>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('co.show', $co->id) }}" class="btn btn-info">View</a>
                        <a href="{{ route('co.edit', $co->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('co.destroy', $co->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
