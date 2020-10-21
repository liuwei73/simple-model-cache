<?php


namespace liuwei73\SimpleModelCache\Traits;

trait IDGen
{
	public $autoGenerateID = true;

	public static function boot()
	{
		parent::boot();

		self::creating(function ($model) {
			if( $model->incrementing === false && $model->autoGenerateID === true )
			{
				$keyName = $model->getKeyName();
				$generator = app( "IDG" );
				$model->$keyName = $generator->next();
			}
		});
	}
}
