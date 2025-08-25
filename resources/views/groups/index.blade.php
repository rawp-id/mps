@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Groups</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('groups.create') }}" class="btn btn-primary">Create Group</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Processes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->name }}</td>
                            <td>
                                @foreach($group->groupingProcesses as $gp)
                                    {{ $gp->processProduct->id }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('groups.edit', $group) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('groups.destroy', $group) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete?')" type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No Groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
