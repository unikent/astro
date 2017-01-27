<?php

namespace App\Models;

use KentAuth\Models\User as KentUser;
use App\Models\Traits\Tracked;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends KentUser
{
	use Tracked;
}
