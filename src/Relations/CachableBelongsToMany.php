<?php

namespace liuwei73\SimpleModelCache\Relations;

use Chelout\RelationshipEvents\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CachableBelongsToMany extends BelongsToMany
{
	public function __construct(Builder $query, Model $parent, $table, $foreignPivotKey,
	                            $relatedPivotKey, $parentKey, $relatedKey, $relationName = null)
	{
		parent::__construct($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
	}

	public function get($columns = ['*'])
	{
		if( !( count($columns ) == 1 && $columns[0] === "*" ) )
		{
			return parent::get( $columns );
		}

		// get 方法在非 lazy load 的时候也会被调用，这种情况下 eagerModels 就是 NULL 了，需要处理这个情况
		if( $this->eagerModels !== NULL ){
			$parent_keys = $this->getKeys($this->eagerModels, $this->parentKey);
		}
		else{
			$parent_keys = array();
			$parent_key_name = $this->parentKey;
			$parent_keys[] = $this->getParent()->$parent_key_name;
		}

		$foreign_key_column_name = $this->relatedPivotKey;
		$parent_key_column_name = $this->foreignPivotKey;

		//查询中间表
		$foreign_key_objs = DB::table( $this->table )
			->select( [$foreign_key_column_name, $parent_key_column_name] )
			->whereIn( $this->foreignPivotKey, $parent_keys )
			->get();

		//找出所有的 foreign_key 来
		$foreign_models = array();
		foreach( $foreign_key_objs as $foreign_key_obj )
		{
			$foreign_key = $foreign_key_obj->$foreign_key_column_name;
			$foreign_models[ $foreign_key ] = $foreign_key;
		}

		//获取所有需要加载的 foreign_models
		$models = $this->query->getModel()->findMany( array_keys( $foreign_models ) );

		//映射到 $foreign_models 数组
		foreach( $models as $model )
		{
			$model_key_name = $model->getKeyName();
			$model_key = $model->$model_key_name;
			$foreign_models[ $model_key ] = $model;
		}

		//组织所有的 pivot_models 对象， models 对象数组是不重复的，pivot_models 可以重复
		$pivot_models = array();
		foreach( $foreign_key_objs as $foreign_key_obj )
		{
			$foreign_key = $foreign_key_obj->$foreign_key_column_name;
			$parent_key = $foreign_key_obj->$parent_key_column_name;
			$foreign_model = $foreign_models[ $foreign_key ];
			$pivot_model = clone $foreign_model;
			$pivot_model->setAttribute("pivot_".$parent_key_column_name, $parent_key);
			$pivot_model->setAttribute("pivot_".$foreign_key_column_name, $foreign_key);
			$pivot_models[] = $pivot_model;
		}

		//调用父类的 hydratePivotRelation 函数分别映射到多个 model 去
		$this->hydratePivotRelation($pivot_models);

		//TODO for pivot_models's lazy load, fix it later.
//		if (count($pivot_models) > 0) {
//			$builder = $this->query->applyScopes();
//			$models = $builder->eagerLoadRelations($pivot_models);
//		}

		return new Collection( $pivot_models );
	}

	private $eagerModels = NULL;

	public function addEagerConstraints(array $models)
	{
		$this->eagerModels = $models;
	}
}
