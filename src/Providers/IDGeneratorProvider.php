<?php

namespace liuwei73\SimpleModelCache\Providers;

use Illuminate\Support\ServiceProvider;
use liuwei73\SimpleModelCache\Generators\RedisIDGenerator;

class IDGeneratorProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton( "IDG", function($app){
			$generator = new RedisIDGenerator();
			$generator->init();
			return $generator;
		});
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}
}
