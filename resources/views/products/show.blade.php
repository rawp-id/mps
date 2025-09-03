@extends('layouts.app')

@section('title', 'View Product')

@section('content')
    <h1>Product Details</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text"><strong>Code:</strong> {{ $product->code }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Gantt Chart Data</h5>
            @if ($ganttData->isEmpty())
                <p>No Gantt chart data available for this product.</p>
            @else
                <div id="timeline" style="height: 400px; border: 1px solid #ccc;"></div>
                <script type="text/javascript" src="https://unpkg.com/vis-timeline@latest/standalone/umd/vis-timeline-graph2d.min.js">
                </script>
                <link href="https://unpkg.com/vis-timeline@latest/styles/vis-timeline-graph2d.min.css" rel="stylesheet"
                    type="text/css" />

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const ganttData = @json($ganttData);
                        const groups = [];
                        const items = [];

                        // Collect unique process names as groups
                        const processMap = new Map();
                        ganttData.forEach(d => {
                            const id = d.operation ? parseInt(d.operation.id) : 0;
                            const name = d.operation?.process?.name ?? 'Unknown Process';
                            processMap.set(id, name);
                        });

                        // Sort ganttData ascending by id for group order
                        const seen = new Set();
                        ganttData.slice().sort((a, b) => a.id - b.id).forEach(d => {
                            const id = d.operation ? parseInt(d.operation.id) : 0;
                            if (!seen.has(id)) {
                                groups.push({
                                    id,
                                    content: processMap.get(id)
                                });
                                seen.add(id);
                            }
                        });

                        // Items for timeline
                        ganttData.forEach(d => {
                            items.push({
                                id: d.id,
                                group: d.operation ? parseInt(d.operation.id) : 0,
                                content: d.operation?.process?.name ?? 'Process',
                                start: d.start_time,
                                end: d.end_time
                            });
                        });

                        // Render timeline
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
                    });
                </script>
            @endif
        </div>
    </div>

    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Back</a>
@endsection
