<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Accueil') | Administration | Baol Distributions</title>
    <link rel="icon" type="image/png" href="{{ asset('images/baol.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f8f8f8;
            color: #333;
            font-family: "Segoe UI", "DejaVu Sans", Verdana, sans-serif;
            font-size: 13px;
            line-height: 1.4;
        }
        a { color: #447e9b; text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ---- Barre supérieure bleue (Django #417690) ---- */
        #header {
            background-color: #417690;
            color: #f5f5f5;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #branding h1 {
            margin: 0;
            font-size: 18px;
            font-weight: normal;
        }
        #branding h1 a { color: #f5f5f5; }
        #branding h1 a:hover { text-decoration: none; color: #ffc; }
        #user-tools { text-align: right; font-size: 12px; color: #f5f5f5; }
        #user-tools strong { color: #fff; }
        #user-tools a { color: #ffd; display: inline-block; }
        #user-tools form { display: inline; margin-left: 4px; }
        #user-tools form button {
            background: none; border: 0; padding: 0; margin: 0;
            color: #ffd; font: inherit; text-decoration: underline; cursor: pointer;
        }

        /* ---- Fil d'Ariane ---- */
        .breadcrumbs {
            background: #eff6f0;
            padding: 8px 20px;
            color: #666;
            font-size: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .breadcrumbs a { color: #447e9b; }
        .breadcrumbs .separator { margin: 0 6px; color: #999; }

        /* ---- Contenu ---- */
        #content { padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { font-size: 18px; font-weight: normal; margin: 0 0 15px; }

        /* ---- Module (bloc blanc avec en-tête bleu #79aec8) ---- */
        .module { margin-bottom: 20px; background: #fff; border: 1px solid #ddd; }
        .module h2, .module .module h2 {
            margin: 0;
            padding: 8px 12px;
            background: #79aec8;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .module h2 a, .module .addlink {
            color: #fff;
            font-weight: normal;
            background: #417690;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .module .module-caption { padding: 8px 12px; color: #666; font-size: 12px; }

        /* ---- Tableaux façon admin ---- */
        .module table { width: 100%; border-collapse: collapse; }
        .module thead th {
            padding: 6px 10px;
            background: #eff6f0;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 12px;
        }
        .module tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .module tbody tr:nth-child(even) { background: #f8f8f8; }
        .module tbody tr:hover { background: #ecf6f0; }
        .addlink { color: #447e9b !important; }
        .deletelink, button.deletelink {
            color: #ba2121 !important;
            background: none;
            border: 0;
            font: inherit;
            cursor: pointer;
            padding: 0;
            text-decoration: none;
        }
        .deletelink:hover, button.deletelink:hover { text-decoration: underline; }

        /* ---- Formulaires ---- */
        .form-module { background: #fff; border: 1px solid #ddd; padding: 15px; }
        .form-row { margin-bottom: 12px; }
        .form-row label { display: block; font-weight: bold; margin-bottom: 4px; }
        .form-row input[type="text"],
        .form-row input[type="number"],
        .form-row input[type="date"],
        .form-row select {
            width: 100%;
            max-width: 420px;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            background: #fff;
        }
        .form-row .help { color: #666; font-size: 11px; margin-top: 3px; }
        .errorlist { color: #ba2121; margin: 4px 0; padding-left: 16px; font-size: 12px; }

        .submit-row {
            background: #eff6f0;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-top: 15px;
            text-align: right;
        }
        .submit-row input[type="submit"],
        .submit-row .button {
            background: #417690;
            color: #fff;
            border: 0;
            padding: 7px 16px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            margin-left: 6px;
        }
        .submit-row input[type="submit"]:hover { background: #205067; }
        .submit-row .cancel { background: #666; }
        .submit-row .cancel:hover { background: #444; }
        .submit-row .deletelink { color: #ba2121 !important; float: left; text-decoration: none; }

        .object-tools { text-align: right; margin-bottom: 10px; }
        .object-tools a {
            background: #417690;
            color: #fff;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: normal;
        }
        .object-tools a:hover { background: #205067; text-decoration: none; }

        .paginator { padding: 8px 12px; }
        .result-count { padding: 8px 12px; color: #666; }
        .messagelist { list-style: none; padding: 0; margin: 0 0 15px; }
        .messagelist li { padding: 8px 12px; margin-bottom: 6px; border-radius: 4px; }
        .messagelist li.success { background: #dfd; border: 1px solid #9c9; color: #272; }
        .messagelist li.error { background: #fdd; border: 1px solid #e99; color: #721; }
    </style>
</head>
<body>
    <div id="header">
        <div id="branding">
            <h1><a href="{{ route('admin.dashboard') }}">Baol Distributions administration</a></h1>
        </div>
        <div id="user-tools">
            Bienvenue, <strong>{{ auth()->user()->username ?? 'admin' }}</strong>.
            <a href="{{ route('dashboard') }}">Retour au site</a> /
            <form action="{{ route('logout') }}" method="post" style="display:inline;">
                @csrf
                <button type="submit">Déconnexion</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="content" style="padding-bottom:0;">
            <ul class="messagelist"><li class="success">{{ session('success') }}</li></ul>
        </div>
    @endif
    @if($errors->any())
        <div class="content" style="padding-bottom:0;">
            <ul class="messagelist"><li class="error">{{ $errors->first() }}</li></ul>
        </div>
    @endif

    @hasSection('breadcrumbs')
        <div class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Home</a>
            @yield('breadcrumbs')
        </div>
    @endif

    <div id="content">
        @yield('content')
    </div>
</body>
</html>
