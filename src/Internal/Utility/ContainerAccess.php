<?php

namespace OMT\Internal\Utility;

/**
 * Defines method for accessing global container.
 * 
 * @author Mateusz Tokarski
 * @created Apr 9, 2016
 */
trait ContainerAccess {

	/**
	 * Returns DI container used in this application.
	 *
	 * @return ContainerInterface Configured container.
	 */
	public function getContainer() {
		$result = false;
		if (isset($GLOBALS[DI_CONTAINER_OBJECT])) {
			$result = $GLOBALS[DI_CONTAINER_OBJECT];
		}

		return $result;
	}

	/**
	 * Returns single object from container.
	 *
	 * @param string $name Object name.
	 * @return mixed Returns single object from container or false
	 * if object is not set.
	 */
	public function getFromContainer($name) {
		$result = false;
		$container = $this->getContainer();
		if ($container->has($name)) {
			$result = $container->get($name);
		}

		return $result;
	}

}
