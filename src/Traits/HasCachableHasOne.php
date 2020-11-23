<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasOneEvents;
use Illuminate\Support\Collection;

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
			if( $related instanceof Collection )
			{
				foreach( $related as $related_obj )
					$related_obj->clearCache();
			}
			else{
				$related->clearCache();
			}
		});
		static::hasOneUpdated( function($parent, $related){
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
