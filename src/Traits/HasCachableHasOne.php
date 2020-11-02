<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasOneEvents;

trait HasCachableHasOne
{
	use HasOneEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableHasOne()
	{
		static::hasOneCreated( function($parent, $related){
			$parent->clearCache();
		});
		static::hasOneSaved( function($parent, $related){
			$parent->clearCache();
		});
		static::hasOneUpdated( function($parent, $related){
			$parent->clearCache();
		});
	}
}
