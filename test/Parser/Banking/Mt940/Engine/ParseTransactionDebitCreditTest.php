<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTransactionDebitCreditTest extends TestCase
{

    /**
     * @dataProvider getTransactions
     *
     * @param $inputString
     * @param $expected
     * @throws \ReflectionException
     */
    public function test($inputString, $expected) {
        $engine = new Unknown();
        $property = new \ReflectionProperty($engine, 'currentTransactionData');
        $property->setAccessible(true);
        $property->setValue($engine, $inputString);

        $method = new \ReflectionMethod($engine, 'parseTransactionDebitCredit');
        $method->setAccessible(true);
        $this->assertSame($expected, $method->invoke($engine));
    }

    public function getTransactions()
    {
        return [
            'sample-ABN' => [':61:0906240625D1027,91N422NONREF', 'D'],
            'sample-Ing' => [':61:100722C25,03NOV NONREF', 'C'],
            'sample-Rabo' => [':61:030111D000000000500.00NMSC1173113681      ROBECO', 'D'],
            'sample-Spk' => [':61:1002170217C10,N0520000000000000002', 'C'],
            'sample-Tri' => [':61:121123D40,00NET NONREF', 'D'],
            'issue-53-withMultiple-CD-chars' => [':61:1807300730D28,5N132000002018827922//B8G30PGA01UD901N', 'D'],
        ];
    }
}
