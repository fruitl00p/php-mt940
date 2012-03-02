<?php

/**
 * simple function to form an autoloader-like functionality
 * @param string $className
 * @param null $libPath
 * @return mixed
 */
function includeClass($className, $libPath = null) {
	static $classes = array();
	if (isset($classes[$className])) return;

	$class = ucfirst($className);
	if (is_null($libPath))  {
		$libPath = dirname(__FILE__);
	}
	$explodedClass = explode('_', $class);
	$fistClassPart = array_shift($explodedClass);
	$classFileName = $libPath.implode(DIRECTORY_SEPARATOR, array_reverse($explodedClass)).DIRECTORY_SEPARATOR
		.lcfirst($fistClassPart).'.php';

	$classes[$class] = is_readable($classFileName);
	if ($classes[$class]) {
		require $classFileName;
	} else {
		trigger_error('Class '.$class.' could not be imported into the scope when looking in '.$classFileName, E_USER_ERROR);
	}
}

includeClass('statement_banking');
includeClass('transaction_banking');
includeClass('banking_parser');
includeClass('Mt940_banking_parser');
includeClass('Engine_mt940_banking_parser');

// instantiate the actual parser
// and parse them from a given file, this could be any file or a posted string
$parser = new Mt940_banking_parser();
$tmpFile = __DIR__.'/test.mta';
$parsedStatements = $parser->parse(file_get_contents($tmpFile));