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
    $user = $this->getExistingUser($providerUser, $provider->value);

    if (!$user) {
      $user = User::create([
        'name' => $providerUser->getName(),
        'email' => $providerUser->getEmail(),
      ]);
    }

    if (!$this->needsToCreateSocial($user, $provider->value)) {
      $user->userSocials()->create([
        'provider_id' => $providerUser->getId(),
        'provider_name' => $provider->value,
      ]);
    }

    Auth::login($user);

    return redirect('/dashboard');
  }

  protected function getExistingUser($providerUser, $provider)
  {
    $user = User::where('email', $providerUser->getEmail())->orWhereHas(
      'userSocials',
      function ($social) use ($providerUser, $provider) {
        $social->where('provider_id', $providerUser->getId())->where('provider_name', $provider);
      }
    )->first();
    return $user;
  }

  protected function needsToCreateSocial(User $user, $provider)
  {
    return (bool) $user->userSocials
      ->where('provider_name', $provider)->count();
  }
}
