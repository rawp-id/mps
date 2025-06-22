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
        <div id="gantt_chart" style="width: 100%; height: auto;"></div>
    </div>

    <h4 class="mt-5">Schedules</h4>

    <table class="table table-bordered table-striped">
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
    </table>

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
            data.addColumn('string', 'Resource');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            data.addRows([
                @foreach ($schedules as $schedule)
                    [
                        '{{ $schedule->id }}',
                        '{{ $schedule->process->name ?? "Process " . $schedule->process_id }}',
                        '{{ $schedule->machine->name ?? "Machine " . $schedule->machine_id }}',
                        new Date('{{ \Carbon\Carbon::parse($schedule->start_time)->format('Y-m-d H:i:s') }}'),
                        new Date('{{ \Carbon\Carbon::parse($schedule->end_time)->format('Y-m-d H:i:s') }}'),
                        null,
                        0,
                        '{{ $schedule->previous_schedule_id ?? '' }}'
                    ]{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]);

            var options = {
                height: {{ count($schedules) * 50 + 50 }},
                gantt: {
                    trackHeight: 30
                }
            };

            var chart = new google.visualization.Gantt(document.getElementById('gantt_chart'));
            chart.draw(data, options);
        }
    </script>
@endsection
