<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baol Distributions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/baol.png') }}">
    <style>
        .sidebar {
            width: 170px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: white;
            padding-top: 60px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .sidebar img {
            width: 185px;
            margin-bottom: 40px;
            margin-top: -40px;
        }
        .sidebar nav {
            margin-top: -80px;
        }
        .sidebar a {
            padding: 12px;
            text-decoration: none;
            font-size: 16px;
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 10px;
            transition: background 0.3s, color 0.3s;
        }
        .sidebar a i {
            margin-right: 8px;
            font-size: 18px;
            color: var(--bs-primary);
        }
        .sidebar a:hover {
            background: var(--bs-primary);
            color: white;
        }
        .sidebar a.active {
            background: var(--bs-primary);
            color: white;
            border-radius: 8px;
        }
        .sidebar a.active i {
            color: white;
        }
        .content {
            margin-left: 170px;
            padding: 30px;
        }
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar-left {
            display: flex;
            align-items: center;
        }
        .navbar-left h4 {
            margin-left: 10px;
            font-weight: bold;
        }
        .top-navbar input {
            width: 250px;
        }
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
            width: 51%;
            display: inline-block;
            position: relative;
            margin-left: 20px;
        }
        .marquee {
            display: inline-block;
            animation: marquee 15s linear infinite;
            font-weight: bold;
            color: var(--bs-primary);
            font-size: 25px;
            transform: translateX(50%);
            animation-delay: 0s;
        }
        @keyframes marquee {
            0% { transform: translateX(70%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="{{ asset('images/logos.png') }}" alt="Logo">
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('liste_livraisons') }}" class="{{ request()->is('livraisons*') ? 'active' : '' }}">
                <i class="fas fa-truck"></i> Livraisons
            </a>
            <a href="{{ route('liste_rapports') }}" class="{{ request()->is('rapports*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Rapports
            </a>
            @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i> Administration
                </a>
            @endif
        </nav>
    </div>

    <nav class="top-navbar navbar navbar-expand-lg bg-white">
        <div class="container-fluid">
            <div class="navbar-left">
                <i class="fas fa-box text-primary fa-2x"></i>
                <h4 class="text-primary mb-0">Baol Distributions</h4>
            </div>
            <div class="marquee-container">
                <span class="marquee">Welcome  --  Bienvenue sur la plateforme de Baols Distributions  -  Dakar  -  Sénégal  -- @MAN_TECH  -- Tous droits réservés</span>
            </div>
            <form class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Rechercher..." aria-label="Search">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            @auth
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
            </form>
            @endauth
        </div>
    </nav>

    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
