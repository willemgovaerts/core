<?php
namespace App\Domain\User;

use App\Domain\Base\User\BaseUser;

class User extends BaseUser
{
    /**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
	 	'password', 'remember_token', 
	];
}
