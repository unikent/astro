<?php

namespace App\Http\Controllers\Auth;

use App\Models\Permission;
use App\Models\User;
use Config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class JWTController extends Controller
{
	use AuthenticatesUsers {
		login as public loginLocal;
	}

	/**
	 * Get the login username to be used by the controller.
	 * This overrides Illuminate\Foundation\Auth\AuthenticatesUsers.
	 *
	 * @return string
	 */
	public function username()
	{
		return 'username';
	}

	/**
	 * Create valid JWTs for development purposes
	 * @param Request $request
	 * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function devAuthenticate(Request $request)
	{
		$session = $request->session();
		if ($request->has('username') && $request->has('password')) {
			$this->loginLocal($request); //TODO: fix commented validation
		}

		if (Auth::check()) {
			return view('auth.jwt.jwt')->with('jwt', $this->generateJWT(
				Auth::user()->username,
				config('auth.jwt_lifetime')
			));
		} else {
			return view('auth.jwt.dev.form');
		}
	}

	/**
	 * Check if the array contains the attribute that allows access to site editor
	 * @param array $attributes - Array of sso attributes
	 * @return bool - True if the user has access to site editor, otherwise false
	 */
	public function hasSiteEditorAttribute($attributes)
	{
		return isset($attributes['unikentadminresource'])
			&& is_array($attributes['unikentadminresource'])
			&& in_array('siteeditor', $attributes['unikentadminresource']);
	}

	/**
	 * Adds or updates a user record in our database with the
	 * given attributes. Sets the role to 'user' and also sets a random
	 * (unused) password as the database table does not have a default and requires a value.
	 * @param array $attributes - Attributes, which MUST include 'username'
	 * @return User
	 */
	public function addOrUpdateUser($attributes)
	{
		$user = User::where('username', '=', $attributes['username'])->first();
		if(!$user) {
			$user = new User();
			$user->role = 'user';
			$user->username = $attributes['username'];
			$user->password = md5(mt_rand(0,0xffff) . time());
		}
		foreach($attributes as $name => $value) {
			$user->$name = $value;
		}
		$user->save();
		return $user;
	}

	/**
	 * Authenticates the user using SSO. Adds an entry for the user to
	 * our database if the user does not already exist, or updates the existing
	 * user details if it does exist.
	 * @param Request $request
	 * @return $this
	 */
	public function ssoAuthenticate(Request $request)
	{
		require_once '/var/www/html/sso-sp/vendor/simplesamlphp/simplesamlphp/lib/_autoload.php';
		$as = new \SimpleSAML\Auth\Simple('default-sp');
		$as->requireAuth();
		$attributes = $as->getAttributes();
		if(!$this->hasSiteEditorAttribute($attributes)) {
			return view('auth.jwt.nosso')
					->with('attributes', $attributes);
		}
		$this->addOrUpdateUser([
			'username' => $attributes['uid'][0],
			'name' => $attributes['displayName'][0],
			'email' => $attributes['mail'][0],
		]);
		return view('auth.jwt.jwt')->with('jwt', $this->generateJWT(
			$attributes['uid'][0],
			config('auth.jwt_lifetime')
		));
	}

	public function resetSSOToken()
	{
		require_once '/var/www/html/sso-sp/vendor/simplesamlphp/simplesamlphp/lib/_autoload.php';
		$as = new \SimpleSAML\Auth\Simple('default-sp');
		if($as->isAuthenticated()) {
			$as->logout(['ReturnTo' => config('app.url')]);
		}
	}

	/**
	 * Reset the dev token
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function resetDevToken(Request $request)
	{
		Auth::logout();
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
	* Validates login form input
	* @param Request $request http request
	* @param array $rules rule set
	* @return true | false
	*/
	public function validate($request, $rules)
	{
		return Validator::make(
			$request->all(),
			$rules
		)->validate();
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
