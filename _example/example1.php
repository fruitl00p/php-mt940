<?php

// composer autoloader
require dirname(__DIR__) . 'vendor/autoload.php';

// instantiate the actual parser
// and parse them from a given file, this could be any file or a posted string
$parser = new \Kingsquare\Parser\Banking\Mt940();
$tmpFile = __DIR__ . '/test.mta';
$parsedStatements = $parser->parse(file_get_contents($tmpFile));
