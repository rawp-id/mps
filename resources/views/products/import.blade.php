@extends('layouts.app')

@section('title', 'Import Products')

@section('content')
<h1>Import Products from Excel</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ implode(', ', $errors->all()) }}
    </div>
@endif

<form action="{{ route('products.import.preview') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label for="file" class="form-label">Choose Excel/CSV File</label>
        <input type="file" class="form-control" name="file" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload & Preview</button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
</form>
@endsection
