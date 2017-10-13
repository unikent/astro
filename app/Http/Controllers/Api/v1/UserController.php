<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Transformers\Api\v1\UserTransformer;
use App\Http\Transformers\Api\v1\PermissionTransformer;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserController extends ApiController
{

	/**
	 * GET /api/v1/users
	 * This endpoint supports 'include'.
	 *
	 * @param  Request    $request
	 * @return Response
	 */
	public function index(Request $request){
		$this->authorize('index', User::class);
		$users = User::orderBy('username');
		if(str_contains($request->get('include'), 'roles')){
			$users = $users->with(['roles.site','roles.role']);
		}
		return fractal($users->get(), new UserTransformer())->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * View details for a single user by username, optionally including the roles they have on various sites.
	 * GET /api/v1/users/{username}?include=roles
	 * @param Request $request - The request object.
	 * @param string $username - The username of the user to get data for.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function view(Request $request, $username)
	{
		$user = User::where('username', '=', $username)->firstOrFail();
		$this->authorize('view', $user);
		return fractal($user, new UserTransformer())->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * Get all the permissions available in the system and the roles that have each one.
	 * GET /api/v1/permissions
	 * @param Request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function permissions()
	{
		$this->authorize('list', Permission::class);
		$permissions = Permission::with('roles')->get();
		return response()->json(['data' => Permission::toArrayWithRoles() ]);
	}

}
