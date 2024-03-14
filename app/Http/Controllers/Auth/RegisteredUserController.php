<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Domain;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // SUBSTITUIR POSTERIORMENTE PO UM CAMPO ESPECIFICO QUE SERÁ CRIADO EM USERS
        $tenant_id = explode(' ',$request->name)[0];
        $tenant_id = preg_replace('/[^a-zA-Z0-9]/', '', $tenant_id);

        $tenant = Tenant::create([
            'id' => $tenant_id
        ]);
        
        // SUBSTITUIR POSTERIORMENTE PELO DOMINIO DA APLICAÇÃO
        $domain = Domain::create([
            'domain' => $tenant_id.'.localhost',
            'tenant_id' => $tenant_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
