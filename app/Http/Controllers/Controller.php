<?php namespace tcCore\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\User\Roles;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;

	protected function getUserRoles() {
		return Roles::getUserRoles();
	}

}
