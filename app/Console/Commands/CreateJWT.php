<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Creates and signs a JWT for one or more users.
 * @package App\Console\Commands
 */
class CreateJWT extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:createjwt
								{lifetime : number of seconds before the tokens expire}
								{usernames : the comma separated list of usernames of the users to create tokens for, or "all"}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Creates JWTs for one or all users.";

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->createJWTs($this->argument('lifetime'), $this->argument('usernames'));
	}

	/**
	 * Generate a new jtw for the specified users with the given lifetime from now
	 * @param int $lifetime - Duration in seconds from now that the token should be valid for
	 * @param string $usernames A comma seperated list of user IDs' to generate new api tokents for. If
	 * user='all', leave out the astro-www user, since it is needed for the front end to funtion.
	 * To renew astro-www's token, provide its ID explicitly.
	 * @return mixed
	 */
	public function createJWTs($lifetime, $usernames)
	{
		$users = $usernames == 'all'
			? User::where('username', '!=', 'astro-www')->get() :
			User::whereIn('username', array_map('trim', explode(',', $usernames)))->get();
		
		foreach ($users as $user) {
			$token = $this->generateJWT($user, $lifetime);
			$signer = new Sha256();
			var_dump($token->verify($signer, config('auth.jwt_signature') . '1')); // false, because the key is different
			var_dump($token->verify($signer, config('auth.jwt_signature'))); // true, because the key is the same
			$user->api_token = $token;
//			$user->save();
			$this->info("JWT created for user: '$user->name': " . $user->api_token . ' with secret: ' . config('auth.jwt_signature'));
		}

		if(count($users) === 0){
			$this->warn("No matching users found");
		}
	}

	public function generateJWT($user, $lifetime)
	{
		$signer = new Sha256();
		$token = (new Builder())
			->setIssuer('https://napi.kent.ac.uk') // Configures the issuer (iss claim)
			->setId(time().$user->username.mt_rand(0,0xffff), true) // Configures the id (jti claim), replicating as a header item
			->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
			->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
			->setExpiration(time() + $lifetime) // Configures the expiration time of the token (exp claim)
			->set('uid', $user->username) // Configures a new claim, called "uid"
			->sign($signer, config('auth.jwt_signature')) // creates a signature
			->getToken(); // Retrieves the generated token
		return $token;
	}
}
