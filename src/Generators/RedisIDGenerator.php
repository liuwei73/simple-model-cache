<?php

namespace liuwei73\SimpleModelCache\Generators;

use Illuminate\Support\Facades\Redis;

class RedisIDGenerator implements IDGenerator
{
	private $redis = NULL;

	public function init()
	{
		$store_name = config( "cache.laravel-model-id-generator.store" );
		$this->redis = Redis::connection( $store_name );
	}

	public function next()
	{
		//根据当前日期, 获取缓存ID队列KEY名字
		$day = date( "Y-m-d" );
		$list_key_name = $this->_get_key_name( "LIST", $day );

		$new_id = NULL;
		while( is_null( $new_id ) )
		{
			//从队列获取一个 ID
			$new_id = $this->redis->command( "LPOP", [ $list_key_name ] );

			//如果取不到，生成一批
			if( is_null( $new_id ) )
			{
				$this->groupGen( $day );
			}
		}
		return $new_id;
	}

	public function groupGen( $day, $count = FALSE )
	{
		//取 CACHE KEY
		$list_key_name = $this->_get_key_name( "LIST", $day );
		$hash_key_name = $this->_get_key_name( "HASH", $day );

		$gen_count = 0;
		$need_gen_count = $count;
		if( $count === FALSE )
		{
			$need_gen_count = IDGenerator::ID_MIN_GROUP_GEN_COUNT;
		}
		while( $gen_count < $need_gen_count )
		{
			$new_id = $this->_gen_new_id( $day );
			if( $this->redis->command( "HEXISTS", [ $hash_key_name, $new_id ] ) === 0 )
			{
				$this->redis->command( "HSET", [ $hash_key_name, $new_id, "1" ] );
				$this->redis->command( "rpush", [ $list_key_name, $new_id ] );
				$gen_count ++;
			}
		}
	}

	public function check( $day, $count = IDGenerator::ID_MIN_GROUP_GEN_COUNT )
	{
		$list_key_name = $this->_get_key_name( "LIST", $day );
		$remain_count = $this->redis->command( "LLEN", [ $list_key_name ] );
		if( $remain_count < $count )
			$this->groupGen( $day, $count );
	}

	private function _get_key_name( $use_for, $day )
	{
		return "IDG_{$use_for}_{$day}";
	}

	private function _gen_new_id( $day = false )
	{
		if( $day === false )
			$gen_time = time();
		else
			$gen_time = strtotime( $day );

		// 6位日期
		$day_prefix = date( "ymd", $gen_time );

		//8位随机数
		$id_random = mt_rand( 1, ( pow( 10, IDGenerator::ID_RANDOM_LENGTH ) - 1 ) );

		//随机数前面补0
		$suffix = str_pad( $id_random, IDGenerator::ID_RANDOM_LENGTH, "0", STR_PAD_LEFT );

		//返回
		return $day_prefix.$suffix;
	}
}
