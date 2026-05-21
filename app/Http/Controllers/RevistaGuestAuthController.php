<?php

namespace App\Http\Controllers;

use App\Services\TemporaryPhotoAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevistaGuestAuthController extends Controller
{
    public function __construct(
        private readonly TemporaryPhotoAccessService $accessService,
    ) {
    }

    public function showLogin(): View
    {
        return view('revista-armas.guest.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'access_code' => ['required', 'string', 'max:32'],
        ]);

        $grant = $this->accessService->validateGuestLogin(
            $data['email'],
            strtoupper(trim($data['access_code'])),
        );

        if (! $grant) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['access_code' => __('Correo o código incorrectos, o el acceso ya no está vigente.')]);
        }

        $request->session()->put([
            'revista_grant_id' => $grant->id,
            'revista_temp_user_id' => $grant->temporary_photo_user_id,
        ]);

        return redirect()->route('revista-armas.guest.weapons.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['revista_grant_id', 'revista_temp_user_id']);

        return redirect()->route('revista-armas.guest.login');
    }
}
