<?php
namespace Astro\API\Http\Controllers\Api\v1;

use Astro\API\Transformers\Api\v1\RoleTransformer;
use Astro\API\Transformers\Api\v1\UserTransformer;
use Astro\API\Transformers\Api\v1\PermissionTransformer;
use Astro\API\Models\Permission;
use Astro\API\Models\Role;
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
	 * GET /api/v1/permissions?include=permissions:full
	 * @param Request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function permissions(Request $request)
	{
		$this->authorize('list', Permission::class);
		$permissions = Permission::with('roles')->get();
		return fractal($permissions, new PermissionTransformer())->parseIncludes($request->get('include'))->respond();
	}

	/**
	 * Get all the roles available in the system optionally including the permissions belonging to each role as a list
	 * of slugs or objects.
	 * GET /api/v1/roles?include=permissions:full
	 * @param Request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function roles(Request $request)
	{
		$this->authorize('list', Role::class);
		$roles = Role::with('permissions')->get();
		return fractal($roles, new RoleTransformer())->parseIncludes($request->get('include'))->respond();
	}
}
