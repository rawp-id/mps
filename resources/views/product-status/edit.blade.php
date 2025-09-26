@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit Product Status</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('product-status.update', $productStatus->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="code" name="code" required step="1" pattern="\d*" value="{{ $productStatus->code }}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" required>{{ $productStatus->description }}</textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2 me-2 mb-3">
                                <a href="{{ route('product-status.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
