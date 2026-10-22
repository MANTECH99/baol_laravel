<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/baol.png') }}">
    <title>Baol Distribution</title>
    <style>
        .input-container {
            position: relative;
            width: 100%;
        }
        .input-container input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .input-container i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #512da8;
        }
        .logo {
            max-width: 80px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container active" id="container">
        <div class="form-container sign-in">
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <img src="{{ asset('images/baol.png') }}" alt="Logo" class="logo">
                <h1>Réinitialiser le mot de passe</h1>
                <div class="input-container">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                @if(session('status'))
                    <p style="color: green; font-size: 14px; margin-top: 10px;">{{ session('status') }}</p>
                @endif
                @if($errors->any())
                    <p style="color: red; font-size: 14px; margin-top: 10px;">{{ $errors->first() }}</p>
                @endif
                <a href="{{ route('login') }}">Retour à la connexion</a>
                <button type="submit">Envoyer le lien</button>
            </form>
        </div>
    </div>
</body>
</html>
