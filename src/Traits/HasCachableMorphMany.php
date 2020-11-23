<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasMorphManyEvents;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
			if( $related instanceof Collection )
			{
				foreach( $related as $related_obj )
					$related_obj->clearCache();
			}
			else{
				$related->clearCache();
			}
		});
		static::morphManyUpdated( function($parent, $related){
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
