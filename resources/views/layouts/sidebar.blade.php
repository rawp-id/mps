    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column">
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
                    <a class="nav-link text-white" href="{{ route('schedules.create') }}">
                        <i class="bi bi-plus-circle me-2"></i> Plan
                    </a>
                </li>
            </ul>
        </div>
    </div>