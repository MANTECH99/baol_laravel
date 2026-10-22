<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard/');
        }
        return view('auth.login_register');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/dashboard/');
        }

        return view('auth.login_register', ['login_error' => 'Identifiant ou mot de passe incorrect.']);
    }

    public function register(Request $request)
    {
        $username = $request->input('username');
        $password1 = $request->input('password1');
        $password2 = $request->input('password2');

        if ($password1 !== $password2) {
            return view('auth.login_register', ['register_error' => 'Les mots de passe ne correspondent pas.']);
        }

        if (User::where('username', $username)->exists()) {
            return view('auth.login_register', ['register_error' => "Ce nom d'utilisateur existe déjà."]);
        }

        $user = User::create([
            'username' => $username,
            'password' => Hash::make($password1),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showResetForm()
    {
        return view('auth.password_reset');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Un lien de réinitialisation a été envoyé si le compte existe.');
    }

    public function showResetFormToken(string $token)
    {
        return view('auth.password_reset_token', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', 'Mot de passe réinitialisé avec succès.');
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
