<?php


namespace liuwei73\SimpleModelCache\Traits;


use Chelout\RelationshipEvents\Concerns\HasBelongsToEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use liuwei73\SimpleModelCache\Relations\CachableBelongsTo;

trait HasCachableBelongsTo
{
	use HasBelongsToEvents;

	public static function bootHasCachableBelongsTo()
	{
		static::belongsToAssociated(function( $relation_name, $related, $parent ){
			if( $related instanceof Collection ){
				foreach( $related as $related_obj )
					$related_obj->checkRelationNeedClearCache( $relation_name );
			}
			else
				$related->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToDissociated(function( $relation_name, $related, $parent ){
			if( $related instanceof Collection ){
				foreach( $related as $related_obj )
					$related_obj->checkRelationNeedClearCache( $relation_name );
			}
			else
				$related->checkRelationNeedClearCache( $relation_name );
		});
		static::belongsToUpdated(function( $relation_name, $related, $parent ){
			if( $related instanceof Collection ){
				foreach( $related as $related_obj )
					$related_obj->checkRelationNeedClearCache( $relation_name );
			}
			else
				$related->checkRelationNeedClearCache( $relation_name );
		});
	}

	protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
	{
		return new CachableBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
	}
}
