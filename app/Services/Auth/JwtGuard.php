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
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        // TODO review these values and remove if not used
        $this->secretKey = Config::get('auth.jwt_secret');
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = 'api_token';
        $this->storageKey = 'api_token';

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

        $token = (new Parser())->parse((string) $jwt);
        $data = new ValidationData();

        if (!$token->validate($data)) {
            throw new Exception("JWT is invalid", 1);
        }

        if (!$token->verify(new Sha256(), $secret)) {
            throw new Exception("JWT has invalid signature", 1);
        };

        return User::where('username', '=', $token->getClaim('uid'))->firstOrFail();
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
        $token = null;

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
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
