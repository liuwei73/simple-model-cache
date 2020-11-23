<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasMorphOneEvents;
use Illuminate\Support\Collection;

trait HasCachableMorphOne
{
	use HasMorphOneEvents;

	/**
	 * 监控所有的改动，调用 checkRelationNeedClearCache 来看是否需要清理缓存
	 */
	public static function bootHasCachableMorphOne()
	{
		static::morphOneCreated( function($parent, $related){
			$parent->clearCache();
		});
		static::morphOneSaved( function($parent, $related){
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
		static::morphOneUpdated( function($parent, $related){
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
