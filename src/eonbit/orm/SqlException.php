<?php

namespace eonbit\orm;

use Exception;

class SqlException extends Exception {

	protected $code;
	private $sqlState;
	private $driverErrorCode;
	private $driverErrorMessage;

	function __construct($message, $code, $sqlState, $driverErrorCode, $driverErrorMessage) {
		/*
SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
1	Driver-specific error code.
2	Driver-specific error message.
		 */
		parent::__construct($message);
		$this->code = $code;
		$this->sqlState = $sqlState;
		$this->driverErrorCode = $driverErrorCode;
		$this->driverErrorMessage = $driverErrorMessage;
	}

	public function isNoSuchTable(): bool {
		return $this->driverErrorCode === 1146;
	}

}
