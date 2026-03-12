<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendia Admin - @yield('title')</title>

    <!-- Link Bootstrap & Icons (Online CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-teal: #134B46;
            --sidebar-w: 280px;
            --panel-wide: 300px;
            --panel-narrow: 90px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafb;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- SIDEBAR LEFT (DESKTOP) --- */
        .sidebar-left {
            background-color: var(--primary-teal);
            height: 100vh;
            width: var(--sidebar-w);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            display: none;
            flex-direction: column;
            border-radius: 0 40px 40px 0;
            padding: 30px 0;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.05);
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 0 20px;
            scrollbar-width: none;
        }

        .sidebar-content::-webkit-scrollbar {
            display: none;
        }

        .brand {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 40px;
            padding-left: 20px;
            color: white;
            text-decoration: none;
            display: block;
            letter-spacing: -1px;
        }

        /* Navigation Style */
        .nav-link {
            color: rgba(255, 255, 255, 0.5) !important;
            padding: 14px 20px;
            border-radius: 18px;
            margin-bottom: 5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
        }

        .nav-link:hover,
        .nav-link.active,
        .nav-link:not(.collapsed) {
            color: white !important;
        }

        .nav-link.active:not([data-bs-toggle="collapse"]) {
            background-color: white !important;
            color: var(--primary-teal) !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Dropdown Style */
        .sub-nav {
            padding-left: 10px;
            margin-bottom: 15px;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            margin-left: 30px;
        }

        .sub-link {
            font-size: 0.85rem;
            padding: 10px 15px !important;
            color: rgba(255, 255, 255, 0.4) !important;
            border-radius: 12px;
            display: block;
            text-decoration: none;
            transition: 0.2s;
        }

        .sub-link:hover,
        .sub-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .chevron-icon {
            transition: transform 0.3s;
            margin-left: auto;
            font-size: 0.8rem;
        }

        .nav-link:not(.collapsed) .chevron-icon {
            transform: rotate(90deg);
        }

        /* --- RIGHT PANEL (Collapsible) --- */
        .right-panel {
            width: var(--panel-wide);
            background: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 1040;
            border-left: 1px solid #eee;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 40px 25px;
            display: none;
        }

        .right-panel.minimized {
            width: var(--panel-narrow);
            padding: 40px 15px;
        }

        .hide-on-min {
            transition: 0.3s;
            opacity: 1;
            visibility: visible;
        }

        .minimized .hide-on-min {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            display: none;
        }

        /* Panel Toggle Button */
        .toggle-panel {
            position: absolute;
            left: -15px;
            top: 50px;
            width: 30px;
            height: 30px;
            background: white;
            border: 1px solid #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            z-index: 10;
        }

        .minimized .toggle-panel i {
            transform: rotate(180deg);
        }

        /* --- MAIN CONTENT WRAPPER --- */
        .main-wrapper {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 25px;
            min-height: 100vh;
        }

        @media (min-width: 1200px) {
            .sidebar-left {
                display: flex;
            }

            .right-panel {
                display: block;
            }

            .main-wrapper {
                margin-left: var(--sidebar-w);
                margin-right: var(--panel-wide);
                padding: 40px;
            }

            /* Melebar saat panel kanan diclose */
            .main-wrapper.expanded {
                margin-right: var(--panel-narrow);
            }
        }

        /* --- APP BAR --- */
        .top-app-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 30px;
            border-radius: 25px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .top-app-bar h2 {
            font-weight: 800;
            color: var(--primary-teal);
            margin: 0;
            font-size: 1.2rem;
        }

        /* Mobile Adjustments */
        .offcanvas-sidebar {
            background-color: var(--primary-teal);
            width: 280px !important;
            color: white;
            border: none;
        }
    </style>
    @yield('extra-css')
</head>

<body>

    @php
    $isUserActive = request()->is('user*') || request()->is('siswa*') || request()->is('guru*');
    $isAcadActive = request()->is('academic-year*') || request()->is('mapel*') || request()->is('kelas*') || request()->is('class-members*');
    $isOpsActive = request()->is('schedules*') || request()->is('academic-calendar*') || request()->is('locations*');
    $isEvalActive = request()->is('web.kategori-penilaian.*') || request()->is('web.assessment-reports.*');
    @endphp

    <!-- 1. SIDEBAR LEFT -->
    <aside class="sidebar-left shadow-lg">
        <div class="px-4"><a href="{{ route('dashboard') }}" class="brand text-white text-decoration-none">Attendia.</a></div>
        <div class="sidebar-content">
            <nav class="nav flex-column">
                <a href="{{ url('dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>

                <!-- Grup Pengguna -->
                <a class="nav-link {{ $isUserActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gUser">
                    <i class="bi bi-person-gear"></i> Manajemen Pengguna <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isUserActive ? 'show' : '' }}" id="gUser">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('user') }}" class="sub-link {{ request()->is('user*') ? 'active' : '' }}">Data User</a>
                        <a href="{{ url('guru') }}" class="sub-link {{ request()->is('guru*') ? 'active' : '' }}">Data Guru</a>
                        <a href="{{ url('siswa') }}" class="sub-link {{ request()->is('siswa*') ? 'active' : '' }}">Data Siswa</a>
                    </div>
                </div>

                <!-- Grup Akademik -->
                <a class="nav-link {{ $isAcadActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gAcad">
                    <i class="bi bi-journal-text"></i> Akademik & Kelas <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isAcadActive ? 'show' : '' }}" id="gAcad">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('academic-year') }}" class="sub-link {{ request()->is('academic-year*') ? 'active' : '' }}">Tahun Ajaran</a>
                        <a href="{{ url('subjects') }}" class="sub-link {{ request()->is('subjects*') ? 'active' : '' }}">Mata Pelajaran</a>
                        <a href="{{ url('classes') }}" class="sub-link {{ request()->is('classes*') ? 'active' : '' }}">Data Kelas</a>
                        <a href="{{ url('class-members') }}" class="sub-link {{ request()->is('class-members*') ? 'active' : '' }}">Anggota Kelas</a>
                    </div>
                </div>

                <!-- Grup Operasional -->
                <a class="nav-link {{ $isOpsActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gOps">
                    <i class="bi bi-calendar-check"></i> Operasional <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isOpsActive ? 'show' : '' }}" id="gOps">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('schedules') }}" class="sub-link {{ request()->is('schedules*') ? 'active' : '' }}">Jadwal Pelajaran</a>
                        <a href="{{ url('academic-calendar') }}" class="sub-link {{ request()->is('academic-calendar*') ? 'active' : '' }}">Hari Libur</a>
                        <a href="{{ url('locations') }}" class="sub-link {{ request()->is('locations*') ? 'active' : '' }}">lokasi Sekolah</a>
                    </div>
                </div>

                <a class="nav-link {{ $isEvalActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gEval">
                    <i class="bi bi-star-half"></i> Evaluasi Sikap <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isEvalActive ? 'show' : '' }}" id="gEval">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ route('web.kategori-penilaian.index') }}" 
                            class="sub-link {{ request()->routeIs('web.kategori-penilaian.index') ? 'active' : '' }}">
                            Indikator Nilai
                        </a>
                        <a href="{{ route('web.assessment-reports.index') }}" 
                            class="sub-link {{ request()->routeIs('web.assessment-reports.index') ? 'active' : '' }}">
                            Laporan Sikap
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <div class="px-4 mt-auto">
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start shadow-none p-3"><i class="bi bi-box-arrow-left"></i> Logout</button>
            </form>
        </div>
    </aside>

    <!-- 2. SIDEBAR MOBILE (OFFCANVAS) -->
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header p-4">
            <h5 class="brand mb-0 text-white">Attendia.</h5><button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body px-3 pt-0">
            <nav class="nav flex-column">
                <a href="{{ url('dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>

                <!-- Grup Pengguna -->
                <a class="nav-link {{ $isUserActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gUser">
                    <i class="bi bi-person-gear"></i> Manajemen Pengguna <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isUserActive ? 'show' : '' }}" id="gUser">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('user') }}" class="sub-link {{ request()->is('user*') ? 'active' : '' }}">Data User</a>
                        <a href="{{ url('guru') }}" class="sub-link {{ request()->is('guru*') ? 'active' : '' }}">Data Guru</a>
                        <a href="{{ url('siswa') }}" class="sub-link {{ request()->is('siswa*') ? 'active' : '' }}">Data Siswa</a>
                    </div>
                </div>

                <!-- Grup Akademik -->
                <a class="nav-link {{ $isAcadActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gAcad">
                    <i class="bi bi-journal-text"></i> Akademik & Kelas <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isAcadActive ? 'show' : '' }}" id="gAcad">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('academic-year') }}" class="sub-link {{ request()->is('academic-year*') ? 'active' : '' }}">Tahun Ajaran</a>
                        <a href="{{ url('mapel') }}" class="sub-link {{ request()->is('mapel*') ? 'active' : '' }}">Mata Pelajaran</a>
                        <a href="{{ url('kelas') }}" class="sub-link {{ request()->is('kelas*') ? 'active' : '' }}">Data Kelas</a>
                        <a href="{{ url('class-members') }}" class="sub-link {{ request()->is('class-members*') ? 'active' : '' }}">Anggota Kelas</a>
                    </div>
                </div>

                <!-- Grup Operasional -->
                <a class="nav-link {{ $isOpsActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gOps">
                    <i class="bi bi-calendar-check"></i> Operasional <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isOpsActive ? 'show' : '' }}" id="gOps">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ url('schedules') }}" class="sub-link {{ request()->is('schedules*') ? 'active' : '' }}">Jadwal Pelajaran</a>
                        <a href="{{ url('academic-calendar') }}" class="sub-link {{ request()->is('academic-calendar*') ? 'active' : '' }}">Hari Libur</a>
                        <a href="{{ url('locations') }}" class="sub-link {{ request()->is('locations*') ? 'active' : '' }}">lokasi Sekolah</a>
                    </div>
                </div>

                <a class="nav-link {{ $isEvalActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#gEval">
                    <i class="bi bi-star-half"></i> Evaluasi Sikap <i class="bi bi-chevron-right chevron-icon"></i>
                </a>
                <div class="collapse {{ $isEvalActive ? 'show' : '' }}" id="gEval">
                    <div class="sub-nav flex-column d-flex">
                        <a href="{{ route('web.kategori-penilaian.index') }}" 
                            class="sub-link {{ request()->routeIs('web.setup-penilaian.index') ? 'active' : '' }}">
                            Indikator Nilai
                        </a>
                        <a href="{{ route('web.assessment-reports.index') }}" 
                            class="sub-link {{ request()->routeIs('web.assessment-reports.index') ? 'active' : '' }}">
                            Laporan Sikap
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <!-- 3. RIGHT PANEL (Collapsible) -->
    <aside class="right-panel shadow-sm" id="rightPanel">
        <div class="toggle-panel" id="togglePanelBtn"><i class="bi bi-chevron-right"></i></div>

        <!-- Profile Section -->
        <div class="d-flex align-items-center justify-content-center gap-3 mb-5 overflow-hidden">
            <div class="text-end hide-on-min">
                <div class="fw-bold small text-truncate" style="max-width: 130px;">{{ Auth::user()->name ?? 'Administrator' }}</div>
                <div class="text-secondary" style="font-size: 10px; font-weight: 700;">SUPER ADMIN</div>
            </div>
            <div class="rounded-4 overflow-hidden border shadow-sm flex-shrink-0" style="width: 50px; height: 50px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/0e/Hyunjin_of_Stray_Kids%2C_September_24%2C_2025.png" class="w-100 h-100 object-fit-cover">
            </div>
        </div>

        <!-- Info Section -->
        <div class="hide-on-min">
            <h6 class="fw-bold mb-4 small text-uppercase tracking-wider">Status Sistem</h6>
            <div class="small text-secondary d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3"><i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i> Server Online</div>
                <div class="d-flex align-items-center gap-3"><i class="bi bi-circle-fill text-warning" style="font-size: 8px;"></i> 12 Izin Menunggu</div>
            </div>
        </div>
    </aside>

    <!-- 4. MAIN CONTENT WRAPPER -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Top App Bar -->
        <header class="top-app-bar shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light rounded-3 d-xl-none border" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"><i class="bi bi-list fs-3"></i></button>
                <h2>@yield('page-title', 'Dashboard')</h2>
            </div>
            <div class="position-relative d-none d-md-block">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                <input type="text" id="mainSearch" class="form-control rounded-pill ps-5 border-0 bg-light shadow-none" placeholder="Cari data..." style="width: 250px; height: 45px;">
            </div>
        </header>

        <!-- Dynamic Content -->
        <div class="content-body">
            @yield('content')
        </div>
    </div>

    <!-- Scripts (Online CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('togglePanelBtn');
            const rightPanel = document.getElementById('rightPanel');
            const mainWrapper = document.getElementById('mainWrapper');

            // Cek status terakhir dari browser agar tidak reset saat refresh
            if (localStorage.getItem('panelState') === 'closed') {
                rightPanel.classList.add('minimized');
                mainWrapper.classList.add('expanded');
            }

            toggleBtn.addEventListener('click', () => {
                rightPanel.classList.toggle('minimized');
                mainWrapper.classList.toggle('expanded');

                const isClosed = rightPanel.classList.contains('minimized');
                localStorage.setItem('panelState', isClosed ? 'closed' : 'open');
            });
        });
    </script>
    @yield('extra-js')
</body>

</html>