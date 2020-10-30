<?php

namespace liuwei73\SimpleModelCache\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use liuwei73\SimpleModelCache\Models\CachableBelongsTo;
use liuwei73\SimpleModelCache\Models\CachableBelongsToMany;
use liuwei73\SimpleModelCache\Models\CachableMorphToMany;
use liuwei73\SimpleModelCache\Models\CachedBuilder;

trait Cachable
{
	public $isCachable = true;
	public $cacheTime = 86400;  //one day
	public $cacheKeyPrefix = "EloquentModelCache";
	protected $update_using_timestamp = true;

	public static function bootCachable()
	{
		static::updated( function($model){
			if( $model->isCachable )
			{
				$cache = $model->cache();
				$cacheKey = $model->getCacheKey();
				$cache->forget( $cacheKey );
			}
		});
	}

	public function newEloquentBuilder($query)
	{
		return new CachedBuilder($query);
	}

	public function getCacheKey()
	{
		$keyName = $this->getKeyName();
		$modelClassName = get_class( $this );
		$id_key = $this->$keyName;
		return $this->genCacheKey( $modelClassName, $id_key );
	}

	public function genCacheKey( $modelClassName, $id_key )
	{
		return $this->cacheKeyPrefix.":".$modelClassName.":".$id_key;
	}

	/**
	 * Set the keys for a save update query.
	 * Add update_using_timestamp for make sure dirty data is not used.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function setKeysForSaveQuery($query)
	{
		$query->where( $this->getKeyName(), '=', $this->getKeyForSaveQuery() );
		if ( $this->update_using_timestamp && $this->usesTimestamps() ) {
			$updatedAtColumn = $this->getUpdatedAtColumn();
			$old_timestamp = $this->getOriginal( $updatedAtColumn );
			if( $old_timestamp ) {
				$query->where( $updatedAtColumn, "=", $old_timestamp );
			}
		}
		return $query;
	}

	protected function performUpdate(Builder $query)
	{
		// If the updating event returns false, we will cancel the update operation so
		// developers can hook Validation systems into their models and cancel this
		// operation if the model does not pass validation. Otherwise, we update.
		if ($this->fireModelEvent('updating') === false) {
			return false;
		}

		// First we need to create a fresh query instance and touch the creation and
		// update timestamp on the model which are maintained by us for developer
		// convenience. Then we will just continue saving the model instances.
		if ($this->usesTimestamps()) {
			$this->updateTimestamps();
		}

		// Once we have run the update operation, we will fire the "updated" event for
		// this model instance. This will allow developers to hook into these after
		// models are updated, giving them a chance to do any special processing.
		$dirty = $this->getDirty();

		if (count($dirty) > 0) {
			$ret = $this->setKeysForSaveQuery($query)->update($dirty);

			//if use update check timestamp, need check update return value.
			if( $this->update_using_timestamp && $this->usesTimestamps() && $ret == 0 )
			{
				$this->fireModelEvent('updated', false);
				throw new QueryException( "model is dirty, you need refresh first." );
			}

			$this->syncChanges();

			$this->fireModelEvent('updated', false);
		}

		return true;
	}

	protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
	{
		return new CachableBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
	}

	protected function newBelongsToMany(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey,
	                                    $parentKey, $relatedKey, $relationName = null)
	{
		return new CachableBelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
	}

	protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
	                                  $relatedPivotKey, $parentKey, $relatedKey,
	                                  $relationName = null, $inverse = false)
	{
		return new CachableMorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
			$relationName, $inverse);
	}
}
