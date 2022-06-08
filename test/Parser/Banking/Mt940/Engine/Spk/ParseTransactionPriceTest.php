<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Spk;

use Kingsquare\Parser\Banking\Mt940\Engine\Spk;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTransactionPriceTest extends TestCase
{

    /**
     * @dataProvider getTransactions
     *
     * @param string $inputString
     * @param float $expected
     * @throws \ReflectionException
     */
    public function test($inputString, $expected) {
        $engine = new Spk;
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
          [':61:1002170217C10,N0520000000000000002', 10.00],
          [':61:1002170217D16,68N0050000000000123122D123', 16.68],
        ];
    }
}
