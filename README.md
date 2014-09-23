[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/fruitl00p/php-mt940/badges/quality-score.png?s=1f4b01cd64b366d6fdfe942e042739902cd4e7cd)](https://scrutinizer-ci.com/g/fruitl00p/php-mt940/)
[![Build Status](https://travis-ci.org/fruitl00p/php-mt940.png?branch=master)](https://travis-ci.org/fruitl00p/php-mt940)
[![Latest Stable Version](https://poser.pugx.org/kingsquare/php-mt940/v/stable.png)](https://packagist.org/packages/kingsquare/php-mt940)
[![License](https://poser.pugx.org/kingsquare/php-mt940/license.png)](https://packagist.org/packages/kingsquare/php-mt940)
[![wercker status](https://app.wercker.com/status/1b20215cc9fee0e4effbe7ad81da1328/s/ "wercker status")](https://app.wercker.com/project/bykey/1b20215cc9fee0e4effbe7ad81da1328)

README
======

What is php-mt940?
----------------

The php-mt940 package provides a simple lightweight parser for mt940 (dutch bank file format) parsing. The output
is transformed into easy to use dataclasses Transaction_banking which itself contains Statement_banking objects. Pretty
straight forward.

Requirements
------------

* Atleast php5 :) (possibly even PHP5.3+ I haven't tested it below 5.3)

How to use the parsers?
-------------------

I've attached a simple script in the examples directory to explain it a bit more in detail, but after loading the
required classes, the usage should be pretty simple:
		<?php
		// ... load everything ... //

		// instantiate the actual parser
        // and parse them from a given file, this could be any file or a posted string
        $parser = new \Kingsquare\Parser\Banking\Mt940();
        $tmpFile = __DIR__.'/test.mta';
        $parsedStatements = $parser->parse(file_get_contents($tmpFile));

        ?>

Known issues
------------

1. I've provided a phpunit test for some engines, but am missing some test-data...

Contact
-------

This is GitHub, you know where to find me :)

License
-------

PHP-MT940 is licensed under the MIT License - see the LICENSE file for details
