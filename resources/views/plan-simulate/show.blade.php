@extends('layouts.app')

@section('title', 'Plan Simulation Detail')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Plan: {{ $plan->name }}</h1>
        <a href="{{ route('plan-simulate.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <p><strong>Slug:</strong> {{ $plan->slug }}</p>
    <p><strong>Product:</strong> {{ $plan->product->name ?? '-' }}</p>
    <p><strong>Description:</strong> {{ $plan->description ?? '-' }}</p>

    <hr>

    <div class="container mt-4">
        <h4 class="mb-3">Gantt Chart</h4>
        <div id="timeline" style="width: 100%; height: 400px; border: 1px solid #ccc;"></div>
    </div>

    {{-- <h4 class="mt-5">Schedules</h4> --}}

    {{-- <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Process</th>
                <th>Machine</th>
                <th>Quantity</th>
                <th>Speed</th>
                <th>Duration</th>
                <th>Start</th>
                <th>End</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schedules as $schedule)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $schedule->process->name ?? 'Process ' . $schedule->process_id }}</td>
                    <td>{{ $schedule->machine->name ?? 'Machine ' . $schedule->machine_id }}</td>
                    <td>{{ $schedule->quantity }}</td>
                    <td>{{ $schedule->plan_speed }}</td>
                    <td>{{ round($schedule->plan_duration, 2) }} min</td>
                    <td>{{ $schedule->start_time }}</td>
                    <td>{{ $schedule->end_time }}</td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}

    {{-- vis.js --}}
    <link href="https://unpkg.com/vis-timeline@latest/dist/vis-timeline-graph2d.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vis-timeline@latest/dist/vis-timeline-graph2d.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const schedules = @json($schedules);
            const groups = [];
            const items = [];

            // Kumpulkan mesin unik
            const productMap = new Map();
            schedules.forEach(s => {
                const id = s.product ? parseInt(s.product.id) : 0;
                const name = (s.product?.name?.length > 20) ? s.product.name.substring(0, 20) + '…' : (s
                    .product?.name ?? 'Unknown Product');
                productMap.set(id, name);
            });

            // 2️⃣ Buat array groups terurut ID ASC
            // Urutkan produk sesuai urutan kemunculan pertama pada schedules (berdasarkan schedule id)
            const seen = new Set();
            // Sort schedules ascending by schedule id to get products in order of first appearance
            schedules.slice().sort((a, b) => a.id - b.id).forEach(s => {
                const id = s.product ? parseInt(s.product.id) : 0;
                if (!seen.has(id)) {
                    groups.push({
                        id,
                        content: productMap.get(id)
                    });
                    seen.add(id);
                }
            });

            // 3️⃣ Buat items
            schedules.forEach(s => {
                items.push({
                    id: s.id,
                    group: s.product ? parseInt(s.product.id) : 0,
                    content: s.operation?.name ?? 'Process',
                    start: s.start_time,
                    end: s.end_time
                });
            });

            // 4️⃣ Render timeline
            const container = document.getElementById('timeline');
            const options = {
                stack: false,
                showMajorLabels: true,
                showCurrentTime: true,
                zoomMin: 1000 * 60 * 60, // 1 hour
                zoomMax: 1000 * 60 * 60 * 24 * 30, // 1 month
                orientation: 'top'
            };

            const timeline = new vis.Timeline(container);
            timeline.setOptions(options);
            timeline.setGroups(new vis.DataSet(groups));
            timeline.setItems(new vis.DataSet(items));

            timeline.on('select', function(properties) {
                if (properties.items.length > 0) {
                    openEditModal(properties.items[0]);
                }
            });
        });
    </script>
@endsection
