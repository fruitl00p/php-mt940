<?php

namespace Kingsquare\Banking;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class StatementTest extends TestCase
{
    public function testBankAssesor()
    {
        $expected = 'ABN';
        $statement = new Statement();
        $statement->setBank($expected);

        $this->assertEquals($expected, $statement->getBank());
    }

    public function testAccountAssesor()
    {
        $expected = '62.90.64.393';
        $statement = new Statement();
        $statement->setAccount($expected);

        $this->assertEquals($expected, $statement->getAccount());
    }

    public function testTransactionsAssesor()
    {
        $expected = [
                new Transaction(),
                new Transaction(),
        ];
        $statement = new Statement();
        $statement->setTransactions($expected);

        $this->assertEquals($expected, $statement->getTransactions());
    }

    public function testStartPriceAssesor()
    {
        $expected = '6250';
        $statement = new Statement();
        $statement->setStartPrice($expected);

        $this->assertEquals($expected, $statement->getStartPrice());
    }

    public function testEndPriceAssesor()
    {
        $expected = '16250';
        $statement = new Statement();
        $statement->setEndPrice($expected);

        $this->assertEquals($expected, $statement->getEndPrice());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     * @expectedExceptionMessage Deprecated in favor of splitting the start and end timestamps for a statement. Please use setStartTimestamp($format) or setEndTimestamp($format) instead. setTimestamp is now setStartTimestamp
     */
    public function testDeprecatedTimestampAssesor()
    {
        $expected = time();
        $statement = new Statement();
        $statement->setTimestamp($expected);

        $this->assertEquals($expected, $statement->getTimestamp());
    }

    public function testTimestampAssesor()
    {
        $time = time();
        $expectedStart = $time - 1440;
        $expectedEnd = $time;
        $statement = new Statement();
        $statement->setStartTimestamp($expectedStart);
        $statement->setEndTimestamp($expectedEnd);

        $this->assertEquals($expectedStart, $statement->getStartTimestamp());
        $this->assertEquals($expectedEnd, $statement->getEndTimestamp());
    }

    public function testNumberAssesor()
    {
        $expected = '2665487AAF';
        $statement = new Statement();
        $statement->setNumber($expected);

        $this->assertEquals($expected, $statement->getNumber());
    }

    /**
     * @depends testStartPriceAssesor
     * @depends testEndPriceAssesor
     */
    public function testDeltaPrice()
    {
        $expected = '10000';
        $statement = new Statement();
        $statement->setStartPrice('16250');
        $statement->setEndPrice('6250');

        $this->assertEquals($expected, $statement->getDeltaPrice());
    }

    /**
     * @depends testTransactionsAssesor
     */
    public function testAddTransaction()
    {
        $statement = new Statement();
        $statement->setTransactions([
                new Transaction(),
                new Transaction(),
        ]);
        $statement->addTransaction(new Transaction());

        $this->assertCount(3, $statement->getTransactions());
    }

    /**
     * @depends testTimestampAssesor
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testGetTimestampWithFormat()
    {
        $expected = '2012-01-01 12:00';
        $statement = new Statement();
        $statement->setTimestamp(strtotime($expected));

        $this->assertEquals($expected, $statement->getTimestamp('Y-m-d H:i'));
    }

    public function testJsonSerialization()
    {
        $expected = '{"bank":"ABN","account":"62.90.64.393","transactions":[],'.
                '"startPrice":16250,"endPrice":6250,"startTimestamp":123,"endTimestamp":0,"number":"2665487AAF","currency":""}';
        $params = [
            'bank' => 'ABN',
            'account' => '62.90.64.393',
            'transactions' => [],
            'startPrice' => 16250,
            'endPrice' => 6250,
            'startTimestamp' => 123,
            'number' => '2665487AAF',
        ];
        $statement = new Statement();
        foreach ($params as $key => $value) {
            $statement->{'set'.$key}($value);
        }
        $this->assertSame($expected, json_encode($statement));
    }
}
