<?php

namespace liuwei73\SimpleModelCache\Models;

use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Traits\Cachable;

abstract class CachableModel extends Model {
	use Cachable;
}
