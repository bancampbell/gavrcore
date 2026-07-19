<?php

namespace App\Http\Controllers\Auth\User;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function __construct(
        protected RegisterUserAction $registerAction
    ) {
    }

    public function create()
    {
        return Inertia::render('Auth/Register', [
            'loginUrl' => route('login'),
        ]);
    }

    public function store(RegisterRequest $request)
    {
        $user = $this->registerAction->execute($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }
}
