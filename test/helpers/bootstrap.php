<?php

function eonbit_orm_autoload($class) {
	$class = ltrim($class, '\\');
	$class = explode('\\', $class);
	if ($class[0] == 'test') {
		array_shift($class);
		$file  = __DIR__ . '/../../test/helpers/' . implode('/', $class) . '.php';
	} else if ($class[0] == 'eonbit') {
		$file  = __DIR__ . '/../../src/' . implode('/', $class) . '.php';
	} else {
		return;
	}
	@include($file);
}

spl_autoload_register("eonbit_orm_autoload");
