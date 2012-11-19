<?php

require dirname(__DIR__).'/src/Kmt/autoload.php';
// instantiate the actual parser
// and parse them from a given file, this could be any file or a posted string
$parser = new Mt940_banking_parser();
$tmpFile = __DIR__.'/test.mta';
$parsedStatements = $parser->parse(file_get_contents($tmpFile));