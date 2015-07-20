<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Triodos;

use Kingsquare\Banking\Statement;
use Kingsquare\Parser\Banking\Mt940\Engine\Triodos;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Statement[]
     */
    private $statements;

    protected function setUp()
    {
        $engine = new Triodos;
        $engine->loadString(file_get_contents(__DIR__ . '/sample'));
        $this->statements = $engine->parse();
    }

    public function testmultipleStatementsInASingleFile()
    {
        $this->assertEquals(2, count($this->statements));
    }

    public function testBankFromStatement()
    {
        /* @var Statement $statement */
        foreach ($this->statements as $i => $statement) {
            $this->assertSame('Triodos', $statement->getBank());
        }
    }

    public function testAccountFromStatement()
    {
        /* @var Statement $statement */
        $known = [
                '666666666',
                '999999999',
        ];
        foreach ($this->statements as $i => $statement) {
            $this->assertSame($known[$i], $statement->getAccount());
        }
    }

    public function testStartPriceFromStatement()
    {
        /* @var Statement $statement */
        $known = [
                1000.0,
                950.12,
        ];
        foreach ($this->statements as $i => $statement) {
            $this->assertSame($known[$i], $statement->getStartPrice());
        }
    }

    public function testEndPriceFromStatement()
    {
        /* @var Statement $statement */
        $known = [
                850.0,
                1009.14,
        ];
        foreach ($this->statements as $i => $statement) {
            $this->assertSame($known[$i], $statement->getEndPrice());
        }
    }

    public function testTimestampFromStatement()
    {
        /* @var Statement $statement */
        $known = [
                '121123',
                '121123',
        ];
        foreach ($this->statements as $i => $statement) {
            $this->assertSame($known[$i], $statement->getTimestamp('ymd'));
        }
    }

    public function testNumberFromStatement()
    {
        /* @var Statement $statement */
        foreach ($this->statements as $i => $statement) {
            $this->assertSame('1', $statement->getNumber());
        }
    }

}
