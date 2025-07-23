@extends('layouts.app')

@section('content')
    <h1>Machine Details</h1>
    <p><strong>ID:</strong> {{ $machine->id }}</p>
    <p><strong>Name:</strong> {{ $machine->name }}</p>
    <p><strong>Capacity:</strong> {{ $machine->capacity }}</p>
    <a href="{{ route('machines.index') }}">Back</a>
@endsection
