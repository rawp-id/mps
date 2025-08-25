@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit Group</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('groups.update', $group->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Group Name"
                                    value="{{ old('name', $group->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="Description">{{ old('description', $group->description) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Processes</label>
                                <div class="ps-2">
                                    @foreach ($processProducts as $pp)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="process_products[]"
                                                value="{{ $pp->id }}" id="pp{{ $pp->id }}"
                                                {{ in_array($pp->id, old('process_products', $selected ?? ($group->processProducts->pluck('id')->toArray() ?? []))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="pp{{ $pp->id }}">
                                                {{ $pp->id }} - {{ $pp->type }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
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
