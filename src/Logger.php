<?php

class Logger {

	// Notes on the logger:
	// Logger must be initialized once per class with 
	// static $logger = null; self::logger = Logger::setLogger();
	// or 
	// $this->logger = Logger::setLogger();
	//
	// Then (pay attention to the BRACKETS!): 
	// (Logger::$logger)('DIRECT call from logger');
	// or:
	// (self::$logger)('Local call from this class'); or as non-static:
	// ($this->logger)('Local call from this class');

	private static ?string $logger = null;
	private static string $logFile = 'mylogging.txt';

	public static function setLogger(string $logFile = null) {
		self::$logger = self::$logger ?? "Logger::logger";	
		self::$logFile = $logFile ?? self::$logFile; 
		return self::$logger;
	}

    public static function logger(string $message): int|false {
		$trace = debug_backtrace();
		if (isset($trace[1])) {
			  // $trace[0] is ourself, $trace[1] is our caller and so on...
	
			  if (isset($trace[1]['class'])) { 
				  $class = $trace[1]['class']; 
			  } else {
					$class = null;
			  }
	
			  if (isset($trace[1]['function'])) {
				  $function = $trace[1]['function']; 
			  } else {
				  $function = null;
			  }
			  
		} else {
			$class = $function = null;
		}
		$message = "[" . date('d:m:Y-H:i:s') . "] $class::$function: $message" . PHP_EOL;
		return file_put_contents(self::$logFile, $message, FILE_APPEND);
	   }
}

// *******************************************************************
// * CLASS TEST ******************************************************
if (!debug_backtrace()) {  // analogous to __name__ == '__main__' in python
	// Code is only executed if *this* file is the entry point.
	require_once 'ClassInfo.php';
	$params = [];
    classInfo(Logger::class, ...$params);
}
