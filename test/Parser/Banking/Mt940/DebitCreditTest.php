<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class DebitCreditTest extends TestCase
{
    /**
     * @dataProvider statementProvider
     *
     * @param string $dOrC      D|C
     * @param string $statement
     */
    public function testDebitCredit($dOrC, $statement)
    {
        $engine = new Unknown();
        $property = new \ReflectionProperty($engine, 'currentTransactionData');
        $property->setAccessible(true);

        $method = new \ReflectionMethod($engine, 'parseTransactionDebitCredit');
        $method->setAccessible(true);

        $property->setValue($engine, $statement);
        $this->assertEquals($dOrC, $method->invoke($engine));
    }

    /**
     * @return array
     */
    public function statementProvider()
    {
        return [
                ['D', ':61:030111D000000000500.00NMSC1173113681      ROBECO'],
                ['C', ':61:100628C49,37NOV NONREF'],
                ['D', ':61:100628D49,37this is a Testds'],
                ['C', ':61:100628C49,37D is actually a'],
                ['C', ':61:100628C36,07NVZ NONREF'],
                ['C', ':61:1004080408C23,7N196NONREF'],
                ['D', ':61:030109D000000000110.00NMSC644530030       INSTANT-LOTERY, STG.NAT'],
                ['D', ':61:1004160416D1133,57N422NONREF'],
        ];
    }
}
