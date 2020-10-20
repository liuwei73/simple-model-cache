<?php

namespace liuwei73\SimpleModelCache\Models;

use App\Models\Cachable\CachableBelongsToMany;
use App\Models\Cachable\CachableMorphToMany;
use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Traits\Cachable;

abstract class CachableModel extends Model {
	use Cachable;
}
