<?php

namespace liuwei73\SimpleModelCache\Traits;

trait ColumnMapping
{
	protected $columns = [];

	public static function bootColumnMapping()
	{
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if( array_key_exists( $key, $this->columns ) )
			return $this->getAttribute( $this->columns[ $key ] );
		else
			return $this->getAttribute( $key );
	}

	/**
	 * Dynamically set attributes on the model.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$real_value = $value;
		if( $value instanceof \Illuminate\Database\Eloquent\Model ) {
			$real_value = $value->getKey();
		}
		if( array_key_exists( $key, $this->columns ) )
			$this->setAttribute( $this->columns[ $key ], $real_value );
		else
			$this->setAttribute( $key, $real_value );
	}
}
