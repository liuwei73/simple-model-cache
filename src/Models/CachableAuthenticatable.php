<?php

namespace liuwei73\SimpleModelCache\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use liuwei73\SimpleModelCache\Traits\Cachable;

abstract class CachableAuthenticatable extends Authenticatable
{
	use Cachable;
}
