<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseDescriptionTest extends TestCase
{
    /**
     * @dataProvider statementProvider
     *
     * @param $input
     * @param $expected
     */
    public function testDebitCredit($input, $expected)
    {
        $engine = new Unknown();
        $property = new \ReflectionProperty($engine, 'currentTransactionData');
        $property->setAccessible(true);
        $property->setValue($engine, $input);

        $method = new \ReflectionMethod($engine, 'parseTransactionDescription');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($engine));
    }

    /**
     * @return array
     */
    public function statementProvider()
    {
        return [
                [':86:This is a test', ''],
                [
                        '
:86:This is a test',
                        'This is a test',
                ],
                [
                        '
:86:This is a test
',
                        'This is a test',
                ],
                [
                        '
:86:This is a test
:',
                        'This is a test:',
                ],
                [
                        '
:86:This is a test
:6',
                        'This is a test:6',
                ],
                [
                        '
:86:This is a test
:61',
                        'This is a test',
                ],
                [
                        '
:86:This is a test
:62',
                        'This is a test',
                ],
                [
                        '
:86:This is a test
: 62',
                        'This is a test: 62',
                ],
                [
                        '
:86:Spaarpot kantine',
                        'Spaarpot kantine',
                ],
                [
                        '
:86: ABN AMRO BANK>AMSTERDAM S1P468
22­07­2010 09:57 002 5595781
',
                        'ABN AMRO BANK>AMSTERDAM S1P46822­07­2010 09:57 002 5595781',
                ],
        ];
    }
}
