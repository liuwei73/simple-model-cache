<?php

namespace liuwei73\SimpleModelCache\Models;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class CachableBuilder extends Builder
{
	public function get($columns = ["*"])
	{
		if (! $this->isCachable() ) {
			return parent::get($columns);
		}

		if( !$this->isColumnAll( $columns ) )
		{
			return parent::get($columns);
		}

		//先获取 ID
		$relatedModel = $this->model;
		$keyName = $relatedModel->getKeyName();
		$modelClassName = get_class( $relatedModel );

		//先查询获取所有的 IDs 数组
		$idObjects = $this->query->get( $relatedModel->getTable().".".$keyName)->all();
		$ids = array();
		foreach( $idObjects as $idObject )
			$ids[] = $idObject->$keyName;

		return $this->findMany( $ids, $columns );
	}

	public function find($id, $columns = ['*'])
	{
		if (! $this->isCachable() ) {
			return parent::find($id, $columns);
		}

		if( !$this->isColumnAll( $columns ) )
		{
			return parent::find($id, $columns);
		}

		if (is_array($id) || $id instanceof Arrayable) {
			return $this->findMany($id, $columns);
		}

		$ids = array();
		$ids[] = $id;

		return $this->findMany( $ids, $columns )->first();
	}

	public function findMany($ids, $columns = ['*'])
	{
		if (! $this->isCachable() ) {
			return parent::findMany($ids, $columns);
		}

		if( !$this->isColumnAll( $columns ) )
		{
			return parent::findMany($ids, $columns);
		}

		$ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

		if (empty($ids)) {
			return $this->model->newCollection();
		}

		//取到相关的 Model 对象
		$relatedModel = $this->model;
		//取到对应的 keyName
		$keyName = $relatedModel->getKeyName();
		//取到 Model 对象类名
		$modelClassName = get_class( $relatedModel );

		//先找出所有的 cacheKeys
		$cacheKeys = array();
		foreach( $ids as $id )
		{
			$cacheKeys[ $id ] = $relatedModel->genCacheKey( $modelClassName, $id );
		}

		//查询缓存
		$cache = $this->cache();
		$cacheModels = $cache->many( array_values( $cacheKeys ) );

		//找出缓存没有命中的 ID
		$cacheMissingIDs = array();
		foreach( $cacheKeys as $id => $cacheKey )
		{
			if( !$cacheModels[ $cacheKey ] )
			{
				$cacheMissingIDs[ $id ] = $cacheKey;
			}
		}

		//一次查询取出这些缓存没有命中的 Model
		$db_models = array();
		if( count( $cacheMissingIDs ) > 0 )
		{
			//关闭缓存这样直接查询数据库
			$relatedModel->isCachable = false;
			$db_models = $relatedModel->findMany( array_keys( $cacheMissingIDs ) );
			//查完之后，要恢复缓存
			$relatedModel->isCachable = true;
		}

		//将这些对象映射到 $cacheModels 并准备设置到 cache
		$cacheMissingMaps = array();
		foreach( $db_models as $db_model )
		{
			$id = $db_model->$keyName;
			$cacheKey = $relatedModel->genCacheKey( $modelClassName, $id );
			$cacheModels[ $cacheKey ] = $db_model;
			$cacheMissingMaps[ $cacheKey ] = $db_model;
		}

		//设置到 cache 注意要检查 cacheTime
		if( count( $cacheMissingMaps ) > 0 ) {
			if( $relatedModel->cacheTime > 0 ){
				Log::debug( "Cache putMany TTL ".$relatedModel->cacheTime." keys ".implode( ",", array_keys( $cacheMissingMaps ) ) );
				$cache->putMany( $cacheMissingMaps, $relatedModel->cacheTime );
			}
			else{
				Log::debug( "Cache putManyForever keys ".implode( ",", array_keys( $cacheMissingMaps ) ) );
				$cache->putManyForever( $cacheMissingMaps );
			}
		}
		//组织返回值
		$models = array();
		foreach( $ids as $id )
		{
			$models[] = $cacheModels[ $relatedModel->genCacheKey( $modelClassName, $id ) ];
		}

		return $this->getModel()->newCollection($models);
	}

	public function isColumnAll( $columns )
	{
		if( count($columns ) == 1 && $columns[0] === "*" )
		{
			return true;
		}
		return false;
	}

	public function isCachable() : bool
	{
		$relatedModel = $this->model;

		if( $relatedModel->isCachable )
			return true;

		return false;
	}

	public function cache(array $tags = [])
	{
		$cache = Container::getInstance()->make("cache" );
		$store_name = config( "cache.laravel-model-cache-store" );

		if ($store_name) {
			$cache = $cache->store( $store_name );
		}

		return $cache;
	}

	private $left_joins = array();
	public function leftJoin($table, $first, $operator = null, $second = null)
	{
		if( !array_key_exists( $table, $this->left_joins ) )
		{
			$this->left_joins[ $table ] = $table;
			return parent::leftJoin( $table, $first, $operator, $second );
		}
		else
			return $this;
	}
}
