<?php


namespace liuwei73\SimpleModelCache\Generators;

interface IDGenerator
{
	Const ID_DAY_LENGTH = 6;
	Const ID_RANDOM_LENGTH = 8;
	Const ID_MIN_GROUP_GEN_COUNT = 500;

	public function init();

	public function next();

	public function groupGen( $day, $count = FALSE );

	public function check( $day, $count = FALSE );
}
