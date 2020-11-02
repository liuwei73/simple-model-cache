<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasMorphToManyEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Relations\CachableMorphToMany;

trait HasCachableMorphToMany
{
	use HasMorphToManyEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableMorphToMany()
	{
		static::morphToManyAttached( function($relation_name, $parent, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToManyDetached( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToManySynced( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToManyToggled( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToManyUpdatedExistingPivot( function($relation_name, $parent, $id, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
	}

	protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
	                                  $relatedPivotKey, $parentKey, $relatedKey,
	                                  $relationName = null, $inverse = false)
	{
		return new CachableMorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
			$relationName, $inverse);
	}
}
