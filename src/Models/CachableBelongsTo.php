<?php


namespace liuwei73\SimpleModelCache\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CachableBelongsTo extends BelongsTo
{
	public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relationName)
	{
		parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);
	}

	public function getResults()
	{
		if (is_null($this->child->{$this->foreignKey})) {
			return $this->getDefaultFor($this->parent);
		}

		return $this->getModel()->find( $this->child->{$this->foreignKey} );
	}

	public function get($columns = ['*'])
	{
		if( !( count($columns ) == 1 && $columns[0] === "*" ) )
		{
			return parent::get( $columns );
		}

		if( $this->eagerModels !== NULL )
		{
			return $this->getModel()->find( $this->getEagerModelKeys( $this->eagerModels ) );
		}
		else
		{
			return $this->getModel()->find( $this->child->{$this->foreignKey} );
		}
	}

	private $eagerModels = NULL;

	public function addEagerConstraints(array $models)
	{
		$this->eagerModels = $models;
	}
}
