<?php

namespace liuwei73\SimpleModelCache\Models;

use liuwei73\SimpleModelCache\Traits\IDGen;

abstract class BaseModel extends CachableModel
{
	public $timestamps = true;
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	protected $primaryKey = 'id';
	public $incrementing = false;

	use IDGen;

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
