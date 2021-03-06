<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasManyEvents;
use Illuminate\Support\Collection;

trait HasCachableHasMany
{
	use HasManyEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableHasMany()
	{
		static::hasManyCreated( function($parent, $related){
			$parent->clearCache();
		});
		static::hasManySaved( function($parent, $related){
			$parent->clearCache();
			if( $related instanceof Collection )
			{
				foreach( $related as $related_obj )
					$related_obj->clearCache();
			}
			else{
				$related->clearCache();
			}
		});
		static::hasManyUpdated( function($parent, $related){
			$parent->clearCache();
			if( $related instanceof Collection )
			{
				foreach( $related as $related_obj )
					$related_obj->clearCache();
			}
			else{
				$related->clearCache();
			}
		});
	}
}
