<?php

namespace liuwei73\SimpleModelCache\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use liuwei73\SimpleModelCache\Models\CachableBuilder;

trait Cachable
{
	public $isCachable = true;
	public $cacheTime = 86400;  //one day
	public $cacheKeyPrefix = "EloquentModelCache";
	protected $update_using_timestamp = true;

	public $postloads = [];
	public $postload_attributes = [];
	/**
	 * monitor updated event to clear cache.
	 *
	 */
	public static function bootCachable()
	{
		static::updated( function($model){
			//刷新后加载所有的属性
			foreach( $model->postloads as $postload ){
				$name = "get".$postload;
				$model->postload_attributes[ $postload ] = $model->$name();
			}
			$model->clearCache();
		});
		static::deleted( function($model) {
			$model->clearCache();
		});
		static::retrieved( function($model) {
			//后加载所有的属性
			foreach( $model->postloads as $postload ){
				$name = "get".$postload;
				$model->postload_attributes[ $postload ] = $model->$name();
			}
		});
		static::created( function($model) {
			//后加载所有的属性
			foreach( $model->postloads as $postload ){
				$name = "get".$postload;
				$model->postload_attributes[ $postload ] = $model->$name();
			}
		});
	}

	public function __get($key)
	{
		if( in_array( $key, $this->postloads ) )
			return $this->postload_attributes[ $key ];
		else
			return $this->getAttribute( $key );
	}

	protected $cache_cleared = false;

	public function clearCache()
	{
		if( $this->cache_cleared === false && $this->isCachable  )
		{
			$cache = $this->cache();
			$cacheKey = $this->getCacheKey();
			$ret = $cache->forget( $cacheKey );
			Log::debug( "Cache forget key ".$cacheKey." return ".$ret );
			$this->cache_cleared = true;
		}
	}

	public function checkRelationNeedClearCache( $relation_name )
	{
		if( $this->with && in_array( $relation_name, $this->with ) )
		{
			$this->clearCache();
		}
	}

	/**
	 * Overwrite newEloquentBuilder function to return CachableBuilder
	 *
	 * @param $query
	 * @return CachableBuilder
	 */
	public function newEloquentBuilder($query)
	{
		return new CachableBuilder($query);
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

	/**
	 * 重载这个函数是为了在 update 的时候可以强制检查 timestamp
	 *
	 * @param Builder $query
	 * @return bool
	 */
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
				throw new \Exception( "model is dirty, you need refresh first." );
			}

			$this->syncChanges();

			$this->fireModelEvent('updated', false);
		}

		return true;
	}
}
