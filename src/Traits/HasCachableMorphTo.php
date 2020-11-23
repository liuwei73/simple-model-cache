<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasBelongsToEvents;
use Chelout\RelationshipEvents\Concerns\HasMorphToEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use liuwei73\SimpleModelCache\Relations\CachableBelongsTo;

trait HasCachableMorphTo
{
	use HasMorphToEvents;

	public static function bootHasCachableMorphTo()
	{
		static::morphToAssociated(function( $relation_name, $related, $parent ){
			if( $related instanceof Collection ){
				foreach( $related as $related_obj )
					$related_obj->checkRelationNeedClearCache( $relation_name );
			}
			else
				$related->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToDissociated(function( $relation_name, $related, $parent ){
			if( $related instanceof Collection ){
				foreach( $related as $related_obj )
					$related_obj->checkRelationNeedClearCache( $relation_name );
			}
			else
				$related->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToUpdated(function( $relation_name, $related, $parent ){
//			Log::debug( "HasCachableMorphTo morphToUpdated related => ".get_class( $related ) );
//			Log::debug( "HasCachableMorphTo morphToUpdated parent => ".get_class( $parent ) );
//
//			$related->checkRelationNeedClearCache( $relation_name );
		});
	}
}
