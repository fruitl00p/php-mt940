<?php

namespace Kingsquare\Banking;

/**
 * @package Kmt\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{

    public function testAccountAssesor()
    {
        $expected = '62.90.64.393';
        $transaction = new Transaction();
        $transaction->setAccount($expected);

        $this->assertEquals($expected, $transaction->getAccount());
    }

    public function testAccountNameAssesor()
    {
        $expected = 'Kingsquare BV';
        $transaction = new Transaction();
        $transaction->setAccountName($expected);

        $this->assertEquals($expected, $transaction->getAccountName());
    }

    public function testPriceAssesor()
    {
        $expected = '6250';
        $transaction = new Transaction();
        $transaction->setPrice($expected);

        $this->assertEquals($expected, $transaction->getPrice());
    }

    public function testDebitCreditAssesorDebit()
    {
        $expected = 'D';
        $transaction = new Transaction();
        $transaction->setDebitCredit($expected);

        $this->assertEquals($expected, $transaction->getDebitCredit());
    }

    public function testDebitCreditAssesorCredit()
    {
        $expected = 'C';
        $transaction = new Transaction();
        $transaction->setDebitCredit($expected);

        $this->assertEquals($expected, $transaction->getDebitCredit());
    }

    public function testDescriptionAssesor()
    {
        $expected = 'This is a description';
        $transaction = new Transaction();
        $transaction->setDescription($expected);

        $this->assertEquals($expected, $transaction->getDescription());
    }

    public function testValueTimestampAssesor()
    {
        $expected = time();
        $transaction = new Transaction();
        $transaction->setValueTimestamp($expected);

        $this->assertEquals($expected, $transaction->getValueTimestamp());
    }

    public function testEntryTimestampAssesor()
    {
        $expected = time();
        $transaction = new Transaction();
        $transaction->setEntryTimestamp($expected);

        $this->assertEquals($expected, $transaction->getEntryTimestamp());
    }

    public function testTransactionCodeAssesor()
    {
        $expected = '13G';
        $transaction = new Transaction();
        $transaction->setTransactionCode($expected);

        $this->assertEquals($expected, $transaction->getTransactionCode());
    }

    /**
     * @depends testValueTimestampAssesor
     */
    public function testGetValueTimestampWithFormat()
    {
        $expected = '2012-01-01 12:00';
        $transaction = new Transaction();
        $transaction->setValueTimestamp(strtotime($expected));

        $this->assertEquals($expected, $transaction->getValueTimestamp('Y-m-d H:i'));
    }

    /**
     * @depends testEntryTimestampAssesor
     */
    public function testGetEntryTimestampWithFormat()
    {
        $expected = '2012-01-01 12:00';
        $transaction = new Transaction();
        $transaction->setEntryTimestamp(strtotime($expected));

        $this->assertEquals($expected, $transaction->getEntryTimestamp('Y-m-d H:i'));
    }

    public function testIsDebit()
    {
        $transaction = new Transaction();
        $transaction->setDebitCredit('D');

        $this->assertTrue($transaction->isDebit());
    }

    public function testIsCredit()
    {
        $transaction = new Transaction();
        $transaction->setDebitCredit('C');

        $this->assertTrue($transaction->isCredit());
    }

    public function testJsonSerialization()
    {
        $expected = '{"account":"123123","accountName":"Kingsquare BV","price":110,"debitcredit":"D",' .
            '"description":"test","valueTimestamp":1231,"entryTimestamp":1234,"transactionCode":"13G"}';

        $params = array(
            'account' => '123123',
            'accountName' => 'Kingsquare BV',
            'price' => 110.0,
            'debitcredit' => Transaction::DEBIT,
            'description' => 'test',
            'valueTimestamp' => 1231,
            'entryTimestamp' => 1234,
            'transactionCode' => '13G',
        );
        $statement = new Transaction();
        foreach ($params as $key => $value) {
            $statement->{'set' . $key}($value);
        }
        $this->assertSame($expected, json_encode($statement));
    }
}
