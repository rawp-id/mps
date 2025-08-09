<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Schedule Management')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <link href="https://unpkg.com/vis-timeline@latest/styles/vis-timeline-graph2d.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vis-timeline@latest/standalone/umd/vis-timeline-graph2d.min.js"></script>

    @yield('head')
    <style>
        .dropdown-submenu>.dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
            display: none;
            position: absolute;
        }

        .dropdown-submenu:hover>.dropdown-menu {
            display: block;
        }

        .dropdown-submenu.dropend-left>.dropdown-menu {
            left: auto;
            right: 100%;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                <i class="bi bi-list"></i> Menu
            </button>
            <a class="navbar-brand" href="{{ route('schedules.index') }}">ERP</a>
        </div>
    </nav>

    @include('layouts.sidebar')

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1️⃣ Flip submenu ke kiri kalau nabrak kanan
            document.querySelectorAll('.dropdown-submenu').forEach(function(submenu) {
                submenu.addEventListener('mouseenter', function() {
                    const submenuMenu = submenu.querySelector('.dropdown-menu');
                    if (!submenuMenu) return;

                    submenuMenu.style.display = 'block';
                    const rect = submenuMenu.getBoundingClientRect();
                    submenuMenu.style.display = '';

                    if (rect.right > window.innerWidth) {
                        submenu.classList.add('dropend-left');
                    } else {
                        submenu.classList.remove('dropend-left');
                    }
                });
            });

            // 2️⃣ Toggle parent submenu
            document.querySelectorAll('.dropdown-submenu > .dropdown-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const parentLi = this.parentNode;

                    // Tutup dropdown lain
                    document.querySelectorAll('.dropdown-submenu').forEach(function(other) {
                        if (other !== parentLi) {
                            other.classList.remove('show');
                        }
                    });

                    // Toggle yang ini
                    parentLi.classList.toggle('show');
                });
            });

            // 3️⃣ Redirect child button click
            document.querySelectorAll('.machine-link').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const url = this.getAttribute('data-url');
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
