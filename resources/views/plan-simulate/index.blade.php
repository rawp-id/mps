@extends('layouts.app')

@section('title', 'Plan Simulations')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Plan Simulations</h1>
        <a href="{{ route('plan-simulate.create') }}" class="btn btn-primary">Create New Plan</a>
    </div>

    {{-- @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif --}}

    @if ($plans->isEmpty())
        <div class="alert alert-info">
            No plan simulations found. <a href="{{ route('plan-simulate.create') }}">Create one</a>.
        </div>
    @else
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Plan Name</th>
                    <th>Product</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $plan->name }}</td>
                        <td>{{ $plan->product->name ?? '-' }}</td>
                        <td>{{ $plan->description ?? '-' }}</td>
                        <td class="d-flex">
                            <a href="{{route('plan-simulate.show', $plan->id)}}" class="btn btn-warning btn-sm me-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <form action="{{ route('plan-simulate.destroy', $plan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this plan?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
