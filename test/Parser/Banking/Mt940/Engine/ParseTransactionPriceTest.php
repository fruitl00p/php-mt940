<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTransactionPriceTest extends TestCase
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

        $method = new \ReflectionMethod($engine, 'parseTransactionPrice');
        $method->setAccessible(true);
        $this->assertSame($expected, $method->invoke($engine));
    }

    public function getTransactions()
    {
        return [
          'sample-ABN' => [':61:0906240625D1027,91N422NONREF', 1027.91],
          'sample-Ing' => [':61:100722C25,03NOV NONREF', 25.03],
          'sample-Rabo' => [':61:030111D000000000500.00NMSC1173113681      ROBECO', 500.00],
          'sample-Spk' => [':61:1002170217C10,N0520000000000000002', 10.00],
          'sample-Tri' => [':61:1002170217C10,N0520000000000000002', 10.00],
          'sample-Unknown' => [':61:1002170217C10,N0520000000000000002', 10.00],
          'issue-53-withMultiple-CD-chars' => [':61:1807300730D28,5N132000002018827922//B8G30PGA01UD901N', 28.50],
        ];
    }
}
