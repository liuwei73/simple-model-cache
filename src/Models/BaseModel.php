<?php

namespace liuwei73\SimpleModelCache\Models;

use liuwei73\SimpleModelCache\Traits\HasCachableBelongsTo;
use liuwei73\SimpleModelCache\Traits\HasCachableBelongsToMany;
use liuwei73\SimpleModelCache\Traits\HasCachableHasMany;
use liuwei73\SimpleModelCache\Traits\HasCachableHasOne;
use liuwei73\SimpleModelCache\Traits\HasCachableMorphMany;
use liuwei73\SimpleModelCache\Traits\HasCachableMorphOne;
use liuwei73\SimpleModelCache\Traits\HasCachableMorphTo;
use liuwei73\SimpleModelCache\Traits\HasCachableMorphToMany;
use liuwei73\SimpleModelCache\Traits\IDGen;

abstract class BaseModel extends CachableModel
{
	public $timestamps = true;
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	protected $primaryKey = 'id';
	public $incrementing = false;

	use IDGen;

	use HasCachableBelongsTo;
	use HasCachableBelongsToMany;
	use HasCachableHasMany;
	use HasCachableHasOne;
	use HasCachableMorphMany;
	use HasCachableMorphOne;
	use HasCachableMorphTo;
	use HasCachableMorphToMany;

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}

	protected function _getCodeNames( $codeStr, $className, $textColumn )
	{
		$codes = explode( ",", $codeStr );
		$names = array();
		foreach( $codes as $code )
		{
			$model = $className::find( $code );
			$names[] = $model->$textColumn;
		}
		return implode( ",", $names );
	}
}
