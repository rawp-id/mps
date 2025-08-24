    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('products.index') }}">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('schedules.index') }}">
                        <i class="bi bi-calendar me-2"></i> Schedules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('schedules.gantt') }}">
                        <i class="bi bi-calendar me-2"></i> Gantt Chart
                    </a>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('plan-simulate.index') }}">
                        <i class="bi bi-plus-circle me-2"></i> Plan Simulate
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('reports.index') }}">
                        <i class="bi bi-file-earmark-text me-2"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('co.index') }}">
                        <i class="bi bi-clipboard me-2"></i> CO Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('components.index') }}" class="nav-link text-white">
                        <i class="bi bi-cpu me-2"></i> Components
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('boms.index') }}" class="nav-link text-white">
                        <i class="bi bi-diagram-3 me-2"></i> BOMs
                    </a>
                </li>
                <hr>
                <span class="text-secondary ms-3 mb-1">Settings</span>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('operations.index') }}">
                        <i class="bi bi-gear me-2"></i> Operations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('operations.index') }}">
                        <i class="bi bi-tools me-2"></i> Settings Machines
                    </a>
                </li>
                <hr>
                <p class="text-secondary ms-3 mb-1">Update Apps</p>
                <li class="nav-item">
                    <a class="nav-link text-white">
                        <i class="bi bi-arrow-up-circle me-2"></i> Penambahan fitur Operation Management
                    </a>
                </li>
            </ul>
        </div>
    </div>
