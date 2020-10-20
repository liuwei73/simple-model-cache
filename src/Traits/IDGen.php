<?php


namespace liuwei73\SimpleModelCache\Traits;

trait IDGen
{
	public $autoGenerateID = true;

	public static function genID( $day = false, $length = 8 )
	{
		if( $day === false )
			$gen_time = time();
		else
			$gen_time = strtotime( $day );
		$day_prefix = date( "ymd", $gen_time );
		$id_random = mt_rand( 1, ( pow( 10, $length ) - 1 ) );
		//随机数前面补0
		$surfix = str_pad( $id_random, $length, "0", STR_PAD_LEFT );
		return $day_prefix.$surfix;
	}

	public static function boot()
	{
		parent::boot();

		self::creating(function ($model) {
			if( $model->incrementing === false && $model->autoGenerateID === true )
			{
				$keyName = $model->getKeyName();
				$model->$keyName = self::genID();
			}
		});
	}
}
