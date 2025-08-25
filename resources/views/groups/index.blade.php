@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <a href="{{ route('groups.create') }}" class="btn btn-primary mb-3">Create Group</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Processes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>
                        @foreach($group->groupingProcesses as $gp)
                            {{ $gp->processProduct->id }}{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('groups.edit',$group) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('groups.destroy',$group) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
