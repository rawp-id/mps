@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create Group</h4>
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
                                <h5 class="mb-2">Processes</h5>
                                @foreach($processProducts as $pp)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="process_products[]" value="{{ $pp->id }}" id="pp{{ $pp->id }}">
                                        <label class="form-check-label" for="pp{{ $pp->id }}">
                                            {{ $pp->id }} - {{ $pp->type }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
