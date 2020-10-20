<?php

namespace liuwei73\SimpleModelCache\Models;

use App\Models\Cachable\CachableBelongsToMany;
use App\Models\Cachable\CachableMorphToMany;
use App\Models\Generators\IDGen;
use Illuminate\Foundation\Auth\User as Authenticatable;
use liuwei73\SimpleModelCache\Traits\Cachable;

abstract class CachableAuthenticatable extends Authenticatable
{
	use Cachable;
}
