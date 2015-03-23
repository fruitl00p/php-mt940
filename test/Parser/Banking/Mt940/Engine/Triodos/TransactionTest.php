<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Triodos;

use Kingsquare\Banking\Statement;
use Kingsquare\Banking\Transaction;
use Kingsquare\Parser\Banking\Mt940\Engine\Triodos;

/**
 *
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction[]
     */
    private $transactions = array();

    protected function setUp()
    {
        $engine = new Triodos;
        $engine->loadString(file_get_contents(__DIR__ . '/sample'));
        foreach ($engine->parse() as $statement) {
            $this->transactions = array_merge($this->transactions, $statement->getTransactions());
        }
    }

    public function testParsesAllFoundStatements()
    {
        $this->assertEquals(8, count($this->transactions));
    }

    public function testAccount()
    {
        /* @var Transaction $transaction */
        $known = array(
                '555555555',
                '555555555',
                '555555555',
                '555555555',
                '888888888',
                '888888888',
                '888888888',
                '888888888',
        );
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getAccount());
        }
    }

    public function testAccountName()
    {
        /* @var Transaction $transaction */
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame('TENAAMSTELLING TEGENREKENIN', $transaction->getAccountName());
        }
    }

    public function testPrice()
    {
        /* @var Transaction $transaction */
        $known = array(
                10.0,
                250.0,
                150.0,
                40.0,
                8.95,
                25.25,
                150.0,
                56.78,
        );
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getPrice());
        }
    }

    public function testDebitCredit()
    {
        /* @var Transaction $transaction */
        $known = array(
                Transaction::DEBIT,
                Transaction::DEBIT,
                Transaction::CREDIT,
                Transaction::DEBIT,
                Transaction::DEBIT,
                Transaction::DEBIT,
                Transaction::CREDIT,
                Transaction::DEBIT,
        );
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getDebitCredit());
        }
    }

    public function testDescription()
    {
        /* @var Transaction $transaction */
        $known = array(
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
                'TENAAMSTELLING TEGENREKENING 1111222233334444',
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE 1111222233334444',
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
                'TENAAMSTELLING TEGENREKENING 1111222233334444',
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
                'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE 1111222233334444',
        );
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getDescription());
        }
    }

    public function testValueTimestamp()
    {
        /* @var Transaction $transaction */
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame('121123', $transaction->getValueTimestamp('ymd'));
        }
    }

    public function testEntryTimestamp()
    {
        /* @var Transaction $transaction */
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame('121123', $transaction->getEntryTimestamp('ymd'));
        }
    }

    public function testTransactionCode()
    {
        /* @var Transaction $transaction */
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame('000', $transaction->getTransactionCode());
        }
    }
}