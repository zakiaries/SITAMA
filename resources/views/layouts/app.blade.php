<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - SITAMA</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styling */
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            letter-spacing: 0.5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            margin-left: 0.5rem;
            transition: color 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--secondary-color) !important;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 40px 0;
        }

        .container-main {
            min-height: 400px;
        }

        /* Page Title */
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
        }

        /* Footer Styling */
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
        }

        footer p {
            margin: 0;
            opacity: 0.8;
        }

        footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Content Styling */
        .content-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .content-section p {
            line-height: 1.8;
            color: #555;
            text-align: justify;
        }

        /* Responsive */
        @media (max-width: 768px) {
            main {
                padding: 20px 0;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .content-section {
                padding: 20px;
            }
        }
    </style>

    @yield('extra-css')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-lg">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-house-fill"></i> SITAMA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" 
                           href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" 
                           href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" 
                           href="{{ route('contact') }}">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container-lg">
            <div class="container-main">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-lg">
            <p>&copy; {{ date('Y') }} SITAMA Web. Semua hak dilindungi. | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('extra-js')
</body>
</html>
