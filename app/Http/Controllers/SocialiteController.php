<?php

namespace App\Http\Controllers;

use App\Enums\Provider;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
  public function redirect(Provider $provider)
  {
    return Socialite::driver($provider->value)
      ->redirect();
  }

  public function callback(Request $request, Provider $provider)
  {
    $providerUser = Socialite::driver($provider->value)->user();
    // $providerUser = Socialite::driver($provider->value)->stateless()->user();

    $user = User::updateOrCreate(
      [
        'provider_id' => $providerUser->getId(),
      ],
      [
        'name' => $providerUser->getName(),
        'email' => $providerUser->getEmail(),
        'provider_token' => $providerUser->token,
        'provider_refresh_token' => $providerUser->refreshToken,
      ]
    );

    Auth::login($user);

    return redirect('/dashboard');
  }
}
