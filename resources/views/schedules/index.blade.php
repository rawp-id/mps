@extends('layouts.app')

@section('title', 'Schedules List')

@section('head')
    <!-- FullCalendar CSS -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet"> --}}
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Schedules</h1>
    </div>

    <div id="calendar"></div>

    <hr>

    {{-- <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Shipment Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->shipping_date ?? 'N/A' }}</td>
                    <td>
                        <div class="d-flex flex-row gap-2 align-items-center">
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No schedules found.</td>
                </tr>
            @endforelse
        </tbody>
    </table> --}}
@endsection

@section('scripts')
    <!-- FullCalendar JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    @foreach($products as $product)
                        @if($product->shipping_date)
                        {
                            title: '{{ $product->name }}',
                            start: '{{ $product->shipping_date }}',
                            url: '{{ route("schedules.show.product", $product->id) }}',
                        },
                        @endif
                    @endforeach
                ],
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // don't let the browser follow the link
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                }
            });
            calendar.render();
        });
    </script>
@endsection