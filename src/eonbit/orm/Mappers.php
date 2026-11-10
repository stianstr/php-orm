<?php

namespace eonbit\orm;

class Mappers {

	private $classes = [];
	private $namespaces = [];
	private $mappers = [];
	private $default;

	function register_class($entity_class, $mapper_class) {
		$entity_class = ltrim($entity_class, '\\');
		$mapper_class = ltrim($mapper_class, '\\');
		$this->classes[$entity_class] = $mapper_class;
	}

	function register_namespace($entity_namespace, $mapper_namespace) {
		$entity_namespace = ltrim($entity_namespace, '\\');
		$mapper_namespace = ltrim($mapper_namespace, '\\');
		$this->namespaces[$entity_namespace] = $mapper_namespace;
	}

	function register_default($mapper_class) {
		$this->default = $mapper_class;
	}

	function get($class) {
		if (empty($this->mappers[$class]))
			$this->mappers[$class] = $this->find_mapper_class($class);
		$mapper_class = $this->mappers[$class];
		return new $mapper_class($class);
	}

	private function find_mapper_class($class) {
		if (!empty($this->classes[$class])) {
			$mapper_class = $this->classes[$class];
			if (!class_exists($mapper_class))
				throw new OrmException("Mapper class does not exist. Mapper='{$mapper_class}' Class='{$class}'");
			return $mapper_class;
		}
		if (!empty($this->namespaces)) {
			foreach ($this->namespaces as $entity_namespace => $mapper_namespace) {
				if (strpos($class, $entity_namespace) === 0) {
					$class_part = substr($class, strlen($entity_namespace));
					$mapper_class = $mapper_namespace . $class_part;
					if (class_exists($mapper_class))
						return $mapper_class;
				}
			}
		}
		if ($this->default) {
			$mapper_class = $this->default;
			if (!class_exists($mapper_class))
				throw new OrmException("Default mapper class does not exist: $mapper_class");
			return $mapper_class;
		}
		return 'eonbit\\orm\\DefaultMapper';
	}

}
