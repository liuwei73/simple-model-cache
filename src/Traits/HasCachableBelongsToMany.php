<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Relations\CachableBelongsToMany;

trait HasCachableBelongsToMany
{
	use HasBelongsToManyEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableBelongsToMany()
	{
		static::belongsToManyAttached( function($relation_name, $parent, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToManyDetached( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToManyToggled( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToManySynced( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToManyUpdatedExistingPivot( function($relation_name, $parent, $ids, $attributes){
			$parent->checkRelationNeedClearCache( $relation_name );
		});
	}

	/**
	 * 重载 newBelongsToMany 使用 CachableBelongsToMany 对象，主要是批量 lazy 加载做了修改
	 *
	 * @param Builder $query
	 * @param Model $parent
	 * @param $table
	 * @param $foreignPivotKey
	 * @param $relatedPivotKey
	 * @param $parentKey
	 * @param $relatedKey
	 * @param null $relationName
	 * @return CachableBelongsToMany
	 */
	protected function newBelongsToMany(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey,
	                                    $parentKey, $relatedKey, $relationName = null)
	{
		return new CachableBelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
	}
}
