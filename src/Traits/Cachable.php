<?php

namespace liuwei73\SimpleModelCache\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Models\CachableBelongsTo;
use liuwei73\SimpleModelCache\Models\CachableBelongsToMany;
use liuwei73\SimpleModelCache\Models\CachableMorphToMany;
use liuwei73\SimpleModelCache\Models\CachedBuilder;

trait Cachable
{
	public $isCachable = true;
	public $cacheTime = 86400;  //one day
	public $cacheKeyPrefix = "EloquentModelCache";

	public function newEloquentBuilder($query)
	{
		return new CachedBuilder($query);
	}

	public function getCacheKey()
	{
		$keyName = $this->getKeyName();
		$modelClassName = get_class( $this );
		$id_key = $this->$keyName;
		return $this->genCacheKey( $modelClassName, $id_key );
	}

	public function genCacheKey( $modelClassName, $id_key )
	{
		return $this->cacheKeyPrefix.":".$modelClassName.":".$id_key;
	}

	protected function finishSave(array $options)
	{
		parent::finishSave( $options );

		if( $this->isCachable )
		{
			$cache = $this->cache();
			$cacheKey = $this->getCacheKey();
			$cache->forget( $cacheKey );
		}
	}

	protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
	{
		return new CachableBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
	}

	protected function newBelongsToMany(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey,
	                                    $parentKey, $relatedKey, $relationName = null)
	{
		return new CachableBelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
	}

	protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
	                                  $relatedPivotKey, $parentKey, $relatedKey,
	                                  $relationName = null, $inverse = false)
	{
		return new CachableMorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
			$relationName, $inverse);
	}
}
