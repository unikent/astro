<?php

namespace App\Services\Auth;

use Config;
use Exception;
use App\Models\User;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use Lcobucci\JWT\ValidationData;
use Illuminate\Auth\GuardHelpers;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;


class JwtGuard implements Guard
{
    use GuardHelpers;

    /**
     * Shared key to verify jwt signatures.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->secretKey = Config::get('auth.jwt_secret');
        $this->request = $request;
        $this->provider = $provider;
    }


    /**
     * retrieves user by JWT
     * fails if the jwt is invalid or its signature is invalid
     *
     * @param string $jwt
     * @param string $jwt
     * @return User | false
     */
    public function retrieveByJWT($secret, $jwt)
    {
		try {
			$token = (new Parser())->parse((string)$jwt);
			$data = new ValidationData();
			if (!$token->validate($data)) {
				return null;
			}

			if (!$token->verify(new Sha256(), $secret)) {
				return null;
			};
		}catch(\InvalidArgumentException $e) {
			return null;
		}
		return $this->provider->retrieveByCredentials(['username' => $token->getClaim('uid')]);
    }


    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            $user = $this->retrieveByJWT($this->secretKey, $token);
        }

        return $this->user = $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        return $this->request->bearerToken();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {

        if (empty($credentials['jwt'])) {
            return false;
        }

        $token = $credentials['jwt'];

        if (!empty($token)) {
            $user = $this->retrieveByJWT($this->secretKey, $token);
        }

        if (!$user) {
            return true;
        }

        return true;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
