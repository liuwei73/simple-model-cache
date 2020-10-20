<?php

namespace liuwei73\SimpleModelCache\Traits;

trait ColumnMapping
{
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
}
