<?php

namespace App\Http\Controllers\Auth;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class JWTController extends Controller
{
	/**
	 * Create valid JWTs for development purposes
	 * @param Request $request
	 * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function devAuthenticate(Request $request)
	{
		$session = $request->session();
		if ($request->has('jwt_username') && $request->has('jwt_lifetime')) {
			$session->put('jwt_username', $request->get('jwt_username'));
			$session->put('jwt_lifetime', $request->get('jwt_lifetime'));
		}
		if ($session->has('jwt_username') && $session->has('jwt_lifetime')) {
			return view('auth.jwt.dev.jwt')->with('jwt', $this->generateJWT(
				$session->get('jwt_username'),
				$session->get('jwt_lifetime')
			));
		} else {
			return view('auth.jwt.dev.form');
		}
	}

	/**
	 * Reset the dev token
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function resetDevToken(Request $request)
	{
		$session = $request->session();
		$session->forget('jwt_username');
		$session->forget('jwt_lifetime');
		return redirect()->route('auth.jwt.dev.authenticate');
	}

	/**
	 * Generate a signed jwt for the given user
	 * @param $user
	 * @param $lifetime
	 * @return string
	 */
	public function generateJWT($username, $lifetime)
	{
		$key = config('auth.jwt_secret');
		$payload = [
			'iat' => time(),
			'nbf' => time(),
			'exp' => time() + $lifetime,
		];
		$signer = new Sha256();
		$token = (new Builder())
			->setId($this->uniqueTokenID(), true) // Configures the id (jti claim), replicating as a header item
			->setIssuedAt($payload['iat']) // Configures the time that the token was issue (iat claim)
			->setNotBefore($payload['nbf']) // Configures the time that the token can be used (nbf claim)
			->setExpiration($payload['exp']) // Configures the expiration time of the token (exp claim)
			->set('uid', $username) // Configures a new claim, called "uid"
			->sign($signer, $key) // creates a signature
			->getToken(); // Retrieves the generated token*/
		return $token->__toString();
	}

	/**
	 * Generate a more-or-less unique id for each token we generate
	 * @return string
	 */
	public function uniqueTokenID()
	{
		return time() . mt_rand(0, 0xffff);
	}
}