@extends('layouts.app')

@section('title', 'Schedules List')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Schedules</h1>
        {{-- <a href="{{ route('schedules.create') }}" class="btn btn-primary">+ Schedule</a> --}}
    </div>


    {{-- <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Process</th>
                <th>Machine</th>
                <th>Quantity</th>
                <th>Plan Speed</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->id }}</td>
                    <td>{{ $schedule->product->name ?? '-' }}</td>
                    <td>{{ $schedule->process->name ?? 'Process ' . $schedule->process_id }}</td>
                    <td>{{ $schedule->machine->name ?? 'Machine ' . $schedule->machine_id }}</td>
                    <td>{{ $schedule->quantity }}</td>
                    <td>{{ $schedule->plan_speed }}</td>
                    <td>{{ $schedule->start_time }}</td>
                    <td>{{ $schedule->end_time }}</td>
                    <td>
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#staticBackdrop{{ $schedule->id }}">Delay</button>
                            <a href="{{ route('schedules.show', $schedule) }}" class="btn btn-sm btn-info w-100">View</a>
                            <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning w-100">Edit</a>
                            <form action="{{ route('schedules.destroy', $schedule) }}" method="POST"
                                style="display:inline-block" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger w-100">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop{{ $schedule->id }}" data-bs-backdrop="static"
                    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('schedules.delay', $schedule->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="delay_minutes" class="form-label">Delay Duration (minutes)</label>
                                        <input type="number" class="form-control" id="delay_minutes" name="delay_minutes"
                                            required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Delay</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No schedules found.</td>
                </tr>
            @endforelse
        </tbody>
    </table> --}}

    {{-- <div class="mb-3">
        <h2>Schedule Gantt Chart</h2>
    </div> --}}

    <div class="container mt-5">
        {{-- <h2 class="mb-4">Production Gantt Chart</h2> --}}

        <div id="gantt_chart" style="width: 100%; height: 500px;"></div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {
            'packages': ['gantt']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'ID');
            data.addColumn('string', 'Task Name');
            data.addColumn('string', 'Product Name');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            data.addRows([
                @foreach ($schedules as $schedule)
                    [
                        '{{ $schedule->id }}',
                        '{{ $schedule->process->name ?? 'Process ' . $schedule->process_id }}',
                        '{{ $schedule->product->name ?? 'Unknown Product' }}',
                        new Date('{{ \Carbon\Carbon::parse($schedule->start_time)->format('Y-m-d H:i:s') }}'),
                        new Date('{{ \Carbon\Carbon::parse($schedule->end_time)->format('Y-m-d H:i:s') }}'),
                        null,
                        0,
                        '{{ $schedule->previous_schedule_id ?? '' }},{{ $schedule->process_dependency_id ?? '' }}'
                    ]
                    @if (!$loop->last)
                        ,
                    @endif
                @endforeach
            ]);

            var options = {
                height: {{ count($schedules) * 50 + 50 }},
                gantt: {
                    trackHeight: 30,
                }
            };

            var chart = new google.visualization.Gantt(document.getElementById('gantt_chart'));
            chart.draw(data, options);

            // Tambahkan event click listener
            google.visualization.events.addListener(chart, 'select', function() {
                var selection = chart.getSelection();
                if (selection.length > 0) {
                    var row = selection[0].row;
                    var id = data.getValue(row, 0);
                    var taskName = data.getValue(row, 1);
                    var productName = data.getValue(row, 2);
                    // Ambil data schedule dari schedules blade ke JS
                    var scheduleId = id;
                    var schedule = @json($schedules->keyBy('id'));
                    var selected = schedule[scheduleId];

                    // Isi form modal edit
                    if (selected) {
                        // Buat modal edit jika belum ada
                        if (!document.getElementById('editScheduleModal')) {
                            var modalHtml = `
                                <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form id="editScheduleForm" method="POST">
                                                <div class="modal-body">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-3">
                                                        <label for="edit_quantity" class="form-label">Quantity</label>
                                                        <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_plan_speed" class="form-label">Plan Speed</label>
                                                        <input type="number" class="form-control" id="edit_plan_speed" name="plan_speed" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_start_time" class="form-label">Start Time</label>
                                                        <input type="datetime-local" class="form-control" id="edit_start_time" name="start_time" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_end_time" class="form-label">End Time</label>
                                                        <input type="datetime-local" class="form-control" id="edit_end_time" name="end_time" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            `;
                            document.body.insertAdjacentHTML('beforeend', modalHtml);
                        }

                        // Set form action
                        var form = document.getElementById('editScheduleForm');
                        form.action = '/schedules/' + scheduleId;

                        // Set value
                        document.getElementById('edit_quantity').value = selected.quantity;
                        document.getElementById('edit_plan_speed').value = selected.plan_speed;
                        document.getElementById('edit_start_time').value = selected.start_time.replace(' ', 'T');
                        document.getElementById('edit_end_time').value = selected.end_time.replace(' ', 'T');

                        // Tampilkan modal
                        var modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
                        modal.show();
                    }
                }
            });
        }
    </script>



    {{-- @php
        $px_per_minute = 1.6667; // 100px = 1 hour
        $start_chart = \Carbon\Carbon::createFromTime(8, 0);
        $end_chart = \Carbon\Carbon::createFromTime(20, 0);
        $total_minutes = $start_chart->diffInMinutes($end_chart);
        $products = $schedules->groupBy('product_id');
    @endphp

    <style>
        .gantt-wrapper {
            display: flex;
            overflow-x: auto;
            border: 1px solid #ccc;
        }

        .gantt-labels {
            flex: 0 0 200px;
            background-color: #f8f9fa;
            border-right: 1px solid #ccc;
        }

        .gantt-labels .label {
            height: 50px;
            display: flex;
            align-items: center;
            padding-left: 10px;
            border-bottom: 1px solid #ccc;
            font-weight: bold;
        }

        .gantt-chart {
            min-width: {{ $total_minutes * $px_per_minute }}px;
            flex-grow: 1;
            position: relative;
        }

        .gantt-row {
            height: 50px;
            border-bottom: 1px solid #ccc;
            position: relative;
        }

        .gantt-bar {
            position: absolute;
            height: 30px;
            background-color: #0d6efd;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gantt-time-header {
            display: flex;
            margin-left: 200px;
            border-bottom: 1px solid #ccc;
        }

        .gantt-time-header div {
            /* width will be set inline via style attribute */
            text-align: center;
            font-size: 12px;
            padding: 4px 0;
            border-left: 1px solid #eee;
        }
    </style>

    <div class="gantt-time-header">
        @for ($hour = 8; $hour <= 20; $hour++)
            <div style="width: {{ 60 * $px_per_minute }}px;">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00</div>
        @endfor
    </div>

    <div class="gantt-wrapper">
        <div class="gantt-labels">
            @foreach ($products as $productId => $groupedSchedules)
                <div class="label">{{ $groupedSchedules->first()->product->name ?? 'Unknown' }}</div>
            @endforeach
        </div>

        <div class="gantt-chart">
            @foreach ($products as $productId => $groupedSchedules)
                <div class="gantt-row">
                    @foreach ($groupedSchedules as $schedule)
                        @php
                            $start = \Carbon\Carbon::parse($schedule->start_time);
                            $end = \Carbon\Carbon::parse($schedule->end_time);
                            $duration = $end->diffInMinutes($start);
                            $offset = max(0, $start_chart->diffInMinutes($start));
                            $left = $offset * $px_per_minute;
                            $width = $duration * $px_per_minute;
                        @endphp

                        <div class="gantt-bar" style="left: {{ $left }}px; width: {{ $width }}px;"
                            title="{{ $schedule->start_time }} → {{ $schedule->end_time }}">
                            {{ $schedule->process->name ?? 'Process' }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div> --}}



    {{-- <style>
        .gantt-row {
            position: relative;
            height: 40px;
            border-bottom: 1px solid #ddd;
        }

        .gantt-bar {
            position: absolute;
            height: 100%;
            background-color: #0d6efd;
            color: #fff;
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
            padding: 5px;
            border-radius: 4px;
        }

        .gantt-time-labels {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .gantt-container {
            position: relative;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .gantt-grid {
            position: relative;
            width: 1200px;
            /* adjustable width for 12 hours */
        }
    </style>

    @php
        $px_per_minute = 1.6667; // 100px per 60 minutes
        // Find the earliest start and latest end time from all schedules
        $start_chart = $schedules->min('start_time')
            ? \Carbon\Carbon::parse($schedules->min('start_time'))
            : \Carbon\Carbon::now()->startOfDay();
        $end_chart = $schedules->max('end_time')
            ? \Carbon\Carbon::parse($schedules->max('end_time'))
            : \Carbon\Carbon::now()->endOfDay();
        $start_hour = $start_chart->copy()->hour;
        $end_hour = $end_chart->copy()->hour;
        $total_minutes = $start_chart->diffInMinutes($end_chart);
        $gantt_width = $total_minutes * $px_per_minute;
    @endphp

    <div class="gantt-time-labels" style="margin-left: 120px;">
        @php
            $current = $start_chart->copy()->minute(0)->second(0);
            $end = $end_chart->copy()->minute(0)->second(0);
        @endphp
        @while ($current <= $end)
            <div style="width: {{ 60 * $px_per_minute }}px; text-align: center;">
                {{ $current->format('H:i') }}
            </div>
            @php $current->addHour(); @endphp
        @endwhile
    </div>

    <div class="gantt-container border">
        <div style="display: flex;">
            <div style="width: 120px;">
                @foreach ($schedules as $schedule)
                    <div class="gantt-row"
                        style="height: 40px; border-bottom: 1px solid #ddd; display: flex; align-items: center;">
                        <span style="font-size: 13px; font-weight: bold;">
                            {{ $schedule->product->name ?? '-' }}
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="gantt-grid" style="width: {{ $gantt_width }}px;">
                @foreach ($schedules as $schedule)
                    @php
                        $start = \Carbon\Carbon::parse($schedule->start_time);
                        $end = \Carbon\Carbon::parse($schedule->end_time);
                        $duration = $end->diffInMinutes($start);
                        $offset = max(0, $start_chart->diffInMinutes($start));
                        $left = $offset * $px_per_minute;
                        $width = $duration * $px_per_minute;
                    @endphp
                    <div class="gantt-row">
                        <div class="gantt-bar" style="left: {{ $left }}px; width: {{ $width }}px;"
                            title="{{ $schedule->start_time }} → {{ $schedule->end_time }}">
                            {{ $schedule->process->name ?? '-' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div> --}}

    {{-- <div class="mt-3">
        {{ $schedules->links() }}
    </div> --}}
@endsection
