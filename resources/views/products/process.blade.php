@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Product Process</h1>
            <button type="button" class="btn btn-primary mb-3" id="add-step">Add Step</button>
        </div>

        <form method="POST" action="{{ route('products.process.input', $product->id) }}">
            @csrf

            <div id="steps-container"></div>

            <button type="submit" class="btn btn-success">Save Process</button>
        </form>

        <!-- Template Step -->
        <template id="step-template">
            <div class="card mb-2 process-step" data-index="{index}">
                <div class="card-header">
                    Step <span class="step-number">{stepNumber}</span>:
                    <button type="button" class="btn btn-danger btn-sm float-end remove-step">Remove</button>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label for="type_{index}">Type</label>
                        <select name="steps[{index}][type]" id="type_{index}" class="form-control step-type">
                            <option value="">-- Select Type --</option>
                            <option value="operation">Operation</option>
                            <option value="setting">Setting</option>
                        </select>
                    </div>
                    <div class="mb-2 operation-group" style="display:none;">
                        <label for="operation_{index}">Operation</label>
                        <select name="steps[{index}][operation_id]" id="operation_{index}" class="form-control">
                            <option value="">-- Select Operation --</option>
                            @foreach ($operations as $operation)
                                <option value="{{ $operation->id }}">{{ $operation->name }}
                                    ({{ $operation->machine->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 setting-group" style="display:none;">
                        <label for="setting_{index}">Setting</label>
                        <select name="steps[{index}][setting_id]" id="setting_{index}" class="form-control">
                            <option value="">-- Select Setting --</option>
                            @foreach ($settings as $setting)
                                <option value="{{ $setting->id }}">{{ $setting->name }} ({{ $setting->machine->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <script>
            let stepIndex = 0;

            // Tambah step baru
            document.getElementById('add-step').onclick = function() {
                const template = document.getElementById('step-template').innerHTML
                    .replace(/{index}/g, stepIndex)
                    .replace(/{stepNumber}/g, stepIndex + 1);

                document.getElementById('steps-container').insertAdjacentHTML('beforeend', template);
                stepIndex++;
            };

            // Hapus step
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-step')) {
                    e.target.closest('.process-step').remove();
                    renumberSteps();
                }
            });

            // Tampilkan input sesuai tipe
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('step-type')) {
                    const stepCard = e.target.closest('.process-step');
                    const type = e.target.value;
                    stepCard.querySelector('.operation-group').style.display = (type === 'operation') ? '' : 'none';
                    stepCard.querySelector('.setting-group').style.display = (type === 'setting') ? '' : 'none';
                }
            });

            // Renumber setelah remove
            function renumberSteps() {
                const steps = document.querySelectorAll('.process-step');
                stepIndex = steps.length;
                steps.forEach((step, idx) => {
                    step.setAttribute('data-index', idx);
                    step.querySelector('.step-number').textContent = idx + 1;
                    step.querySelector('input[type="text"]').setAttribute('name', `steps[${idx}][name]`);
                    step.querySelector('select[name$="[type]"]').setAttribute('name', `steps[${idx}][type]`);
                    step.querySelector('select[name$="[operation_id]"]').setAttribute('name',
                        `steps[${idx}][operation_id]`);
                    step.querySelector('select[name$="[setting_id]"]').setAttribute('name',
                    `steps[${idx}][setting_id]`);
                });
            }
        </script>


    </div>
@endsection
