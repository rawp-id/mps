@extends('layouts.app')

@section('title', 'Import Preview')

@section('content')
<h1>Preview Imported Products</h1>

@if ($rows->isEmpty())
    <div class="alert alert-warning">No data found in the file.</div>
    <a href="{{ route('products.import') }}" class="btn btn-secondary">Back</a>
@else
    <form action="{{ route('products.import.store') }}" method="POST" class="mb-3">
        @csrf

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Confirm & Import</button>
        <a href="{{ route('products.import') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endif
@endsection
