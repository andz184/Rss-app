<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=Poppins:300,400,500,600,700" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Additional Styles -->
    <style>
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 280px;
            --header-height: 70px;
            --sidebar-bg: #fff;
            --body-bg: #f5f8fa;
            --card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            --text-muted: #6c757d;
        }

        [data-bs-theme="dark"] {
            --sidebar-bg: #1e293b;
            --body-bg: #111827;
            --card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Poppins', 'Nunito', sans-serif;
            background-color: var(--body-bg);
            transition: all 0.3s ease;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand .app-logo {
            margin-right: 10px;
        }

        .sidebar-menu {
            padding: 1rem 0;
            overflow-y: auto;
            height: calc(100vh - var(--header-height));
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 10px;
        }

        .sidebar-section {
            padding: 0 1rem;
            margin-bottom: 1rem;
        }

        .sidebar-section-title {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            padding: 0 0.5rem;
        }

        .nav-item {
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--dark-color);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(74, 108, 247, 0.05);
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 8px rgba(74, 108, 247, 0.25);
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .main-content-expanded {
            margin-left: 70px;
        }

        .main-header {
            height: var(--header-height);
            background-color: var(--sidebar-bg);
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .toggle-sidebar {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-muted);
            margin-right: 20px;
        }

        .header-search {
            flex: 1;
            position: relative;
            max-width: 600px;
            margin-right: 1rem;
        }

        .header-search input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: 50px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: rgba(0, 0, 0, 0.03);
        }

        .header-search i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
        }

        .header-actions .btn {
            margin-left: 0.5rem;
        }

        .theme-toggle {
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.03);
            color: var(--text-muted);
            margin-right: 1rem;
        }

        .profile-dropdown img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Main Page Content */
        .page-content {
            padding: 2rem;
        }

        .page-title {
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .card {
            background-color: var(--sidebar-bg);
            border-radius: 0.5rem;
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Module Feature Cards */
        .module-card {
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.08);
        }

        .module-card .card-body {
            position: relative;
            padding-top: 3.5rem;
        }

        .module-icon {
            position: absolute;
            top: -1.5rem;
            left: 1.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(45deg, var(--primary-color), #7b96ff);
            box-shadow: 0 0.5rem 1rem rgba(74, 108, 247, 0.2);
            color: white;
            font-size: 1.5rem;
        }

        /* Media Queries */
        @media (max-width: 991.98px) {
            :root {
                --sidebar-width: 0px;
            }

            .sidebar {
                left: -280px;
            }

            .sidebar.show {
                left: 0;
                width: 280px;
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block !important;
            }
        }

        /* Dark Mode Styles */
        [data-bs-theme="dark"] .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }

        [data-bs-theme="dark"] .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        [data-bs-theme="dark"] .header-search input {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        [data-bs-theme="dark"] .theme-toggle {
            background-color: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.8);
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: var(--sidebar-bg);
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
        }

        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="app-logo"><i class="fas fa-rss"></i></div>
                <span class="brand-text">{{ config('app.name', 'Laravel') }}</span>
            </div>
            <div class="sidebar-menu">
                <!-- Core Navigation -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Chính</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Bảng điều khiển</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- RSS Module -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Đọc RSS</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('articles*') ? 'active' : '' }}" href="{{ route('articles.index') }}">
                                <i class="fas fa-newspaper"></i>
                                <span>Bài viết</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('feeds*') ? 'active' : '' }}" href="{{ route('feeds.index') }}">
                                <i class="fas fa-rss"></i>
                                <span>Nguồn tin</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('categories*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                <i class="fas fa-folder"></i>
                                <span>Danh mục</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('web-scraper*') ? 'active' : '' }}" href="{{ route('web-scraper.index') }}">
                                <i class="fas fa-code"></i>
                                <span>Web to RSS</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Additional Modules (Placeholder for future integration) -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Mô-đun</div>
                    <ul class="nav flex-column">
                        <!-- Agent-S Module -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('agents*') || request()->is('agent-tasks*') ? 'active' : '' }}" href="{{ route('agents.index') }}">
                                <i class="fas fa-robot"></i>
                                <span>Agent AI</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-envelope"></i>
                                <span>Ứng dụng Email</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-tasks"></i>
                                <span>Quản lý Công việc</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar"></i>
                                <span>Lịch</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-alt"></i>
                                <span>Ghi chú</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Settings -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Cài đặt</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user-cog"></i>
                                <span>Hồ sơ</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i>
                                <span>Cài đặt</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="toggle-sidebar" id="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>

                <div class="header-search d-none d-md-block">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Tìm kiếm...">
                </div>

                <div class="ms-auto header-actions">
                    <div class="theme-toggle" id="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </div>

                    <div class="dropdown">
                        <a class="profile-dropdown" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'User' }}&background=4a6cf7&color=fff" alt="User">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li>
                                <div class="dropdown-item text-muted">
                                    <small>Đăng nhập với</small><br>
                                    <strong>{{ Auth::user()->name ?? 'Người dùng' }}</strong>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> {{ __('Đăng xuất') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Sidebar
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('main-content-expanded');
                });
            }

            // Toggle Theme (Light/Dark)
            const themeToggle = document.getElementById('theme-toggle');
            const htmlElement = document.documentElement;
            const currentTheme = localStorage.getItem('theme') || 'light';

            // Apply saved theme on page load
            if (currentTheme === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }

            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                // Update icon
                themeToggle.innerHTML = newTheme === 'dark' ?
                    '<i class="fas fa-sun"></i>' :
                    '<i class="fas fa-moon"></i>';
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
