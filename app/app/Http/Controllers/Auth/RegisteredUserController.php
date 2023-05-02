<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $user = null;

        $request->validate([
            'fullname'  => ['required', 'string', 'max:255'],
            'cin'   => ['required', 'string', 'max:50', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone'     => ['required', 'string', 'max:255',  'unique:'.User::class],
            'address'  => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'profile_picture' => ['image','max:2048', 'mimes:jpeg,jpg,png,gif'],
            'gender' => ['required', 'string'],
            'password'  => ['required', 'confirmed', 'min:8', Rules\Password::defaults()],
        ]);

        $profile_picture = null;

        if ($request->hasFile('profile_picture')) {
            $validated = $request->validate([
                'profile_picture' => 'image','max:2048', 'mimes:jpeg,jpg,png,gif'
            ]);

            $profile_picture = $request->file('profile_picture');
            $filename = $profile_picture->getClientOriginalName();
            $path = $profile_picture->storeAs('public/profile_pictures', $filename);
            $profile_picture_url = Storage::url($path);
        }

        $user = User::create([
            'fullname' => $request->fullname,
            'cin'  => $request->cin,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'birthdate' => $request->birthdate,
            'gender' => $request->gender,
            'profile_picture' => $profile_picture_url ?? null,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);
        
        return redirect()->intended(route('profile.edit'));

    }
}
