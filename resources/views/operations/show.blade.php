@extends('layouts.app')

@section('content')
    <h1>Operation Details</h1>

    <p><strong>Code:</strong> {{ $operation->code }}</p>
    <p><strong>Name:</strong> {{ $operation->name }}</p>
    <p><strong>Process:</strong> {{ $operation->process->name }}</p>
    <p><strong>Machine:</strong> {{ $operation->machine->name }}</p>
    <p><strong>Duration:</strong> {{ $operation->duration }}</p>

    <a href="{{ route('operations.index') }}">Back</a>
@endsection
