<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasBelongsToEvents;
use Chelout\RelationshipEvents\Concerns\HasMorphToEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use liuwei73\SimpleModelCache\Relations\CachableBelongsTo;

trait HasCachableMorphTo
{
	use HasMorphToEvents;

	public static function bootHasCachableMorphTo()
	{
		static::morphToAssociated(function( $relation_name, $related, $parent ){
			$related->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToDissociated(function( $relation_name, $related, $parent ){
			$related->checkRelationNeedClearCache( $relation_name );
		});
		static::morphToUpdated(function( $relation_name, $related, $parent ){
			$related->checkRelationNeedClearCache( $relation_name );
		});
	}
}
