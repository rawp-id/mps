@extends('layouts.app')

@section('content')
    <h1>Process Details</h1>

    <p><strong>Code:</strong> {{ $process->code }}</p>
    <p><strong>Name:</strong> {{ $process->name }}</p>
    <p><strong>Speed:</strong> {{ $process->speed }}</p>

    <a href="{{ route('processes.index') }}">Back</a>
@endsection
