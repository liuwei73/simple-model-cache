<?php


namespace liuwei73\SimpleModelCache\Traits;

trait IDGen
{
	public $autoGenerateID = true;

	public static function bootIDGen()
	{
		static::creating(function ($model) {
			if( $model->incrementing === false && $model->autoGenerateID === true )
			{
				$keyName = $model->getKeyName();
				$generator = app( "IDG" );
				if( $generator )
					$model->$keyName = $generator->next();
			}
		});
	}
}
