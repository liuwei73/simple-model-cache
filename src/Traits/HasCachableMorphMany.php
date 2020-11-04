<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasMorphManyEvents;

trait HasCachableMorphMany
{
	use HasMorphManyEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableMorphMany()
	{
		static::morphManyCreated( function($parent, $related){
			$parent->clearCache();
		});
		static::morphManySaved( function($parent, $related){
			$parent->clearCache();
			$related->clearCache();
		});
		static::morphManyUpdated( function($parent, $related){
			$parent->clearCache();
			$related->clearCache();
		});
	}
}
