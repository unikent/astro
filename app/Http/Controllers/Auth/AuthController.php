<?php

namespace App\Http\Controllers\Auth;
use Config;
use Illuminate\Http\Request;
use KentAuth\Http\Controllers\AuthController as KentAuthController;

class AuthController extends KentAuthController {

	protected $redirectTo = '/';

	/**
	 * Log the user out of the application. Overridden as the laravel default is to redirect to /
	 * @override AuthenticatesUsers@logout
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
		$redirect_url = Config::get('app.url');
		$this->guard()->logout($redirect_url);

		$request->session()->flush();

		$request->session()->regenerate();

		return redirect($redirect_url);
	}
}
