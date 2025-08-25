@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Create Group</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Group Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Group Name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Description"></textarea>
                </div>
                <div class="mb-3">
                    <h4>Processes</h4>
                    @foreach($processProducts as $pp)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="process_products[]" value="{{ $pp->id }}" id="pp{{ $pp->id }}">
                            <label class="form-check-label" for="pp{{ $pp->id }}">
                                {{ $pp->id }} - {{ $pp->type }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
