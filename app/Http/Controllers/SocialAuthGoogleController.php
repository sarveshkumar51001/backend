<?php

namespace App\Http\Controllers;

use App\User, Socialite, Auth, Exception;

class SocialAuthGoogleController extends Controller
{
	public function redirect()  {
		return Socialite::driver('google')->redirect();
	}

	public function callback() {
		try {
			$googleUser = Socialite::driver('google')->stateless()->user();
			$existUser = User::where('email', $googleUser->email)->first();

			if($existUser) {
				Auth::loginUsingId($existUser->id);
			} else {
				$user = new User;
				$user->name = $googleUser->name;
				$user->email = $googleUser->email;
				$user->google_id = $googleUser->id;
				$user->password = md5(rand(1,10000));
				$user->last_login_at = time();
				$user->save();
				Auth::loginUsingId($user->id);
			}

			return redirect()->to('/home');
		}
		catch (Exception $e) {
			return 'There was some error handling the request. Error:' . $e->getMessage();
		}
	}
}