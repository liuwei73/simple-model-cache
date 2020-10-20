<?php

namespace liuwei73\SimpleModelCache\Models;

use App\Models\Cachable\CachableBelongsToMany;
use App\Models\Cachable\CachableMorphToMany;
use App\Models\Generators\IDGen;
use liuwei73\SimpleModelCache\Traits\ColumnMapping;

abstract class BaseAuthenticatable extends CachableAuthenticatable
{
	protected $columns = [];

	public $timestamps = true;
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	protected $update_using_timestamp = true;

	use ColumnMapping;

	protected $primaryKey = 'id';
	public $incrementing = false;

	use IDGen;
}
