<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Triodos;

use Kingsquare\Banking\Statement;
use Kingsquare\Banking\Transaction;
use Kingsquare\Parser\Banking\Mt940\Engine\Triodos;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class TransactionTest extends TestCase
{
    /**
     * @var Transaction[]
     */
    private $transactions = [];

    protected function setUp(): void
    {
        $engine = new Triodos();
        $engine->loadString(file_get_contents(__DIR__.'/sample'));
        $transactions = array_map(static function(Statement $statement) {
            return $statement->getTransactions();
        }, $engine->parse());
        $this->transactions = call_user_func_array('array_merge', $transactions);
    }

    public function testParsesAllFoundStatements()
    {
        $this->assertCount(8, $this->transactions);
    }

    public function testAccount()
    {
        /* @var Transaction $transaction */
        $known = [
            '555555555',
            '555555555',
            '555555555',
            '555555555',
            '888888888',
            '888888888',
            '888888888',
            '888888888',
        ];

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
        $known = [
            10.0,
            250.0,
            150.0,
            40.0,
            8.95,
            25.25,
            150.0,
            56.78,
        ];
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getPrice());
        }
    }

    public function testDebitCredit()
    {
        /* @var Transaction $transaction */
        $known = [
            Transaction::DEBIT,
            Transaction::DEBIT,
            Transaction::CREDIT,
            Transaction::DEBIT,
            Transaction::DEBIT,
            Transaction::DEBIT,
            Transaction::CREDIT,
            Transaction::DEBIT,
        ];
        foreach ($this->transactions as $i => $transaction) {
            $this->assertSame($known[$i], $transaction->getDebitCredit());
        }
    }

    public function testDescription()
    {
        /* @var Transaction $transaction */
        $known = [
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
            'TENAAMSTELLING TEGENREKENING 1111222233334444',
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE 1111222233334444',
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
            'TENAAMSTELLING TEGENREKENING 1111222233334444',
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE',
            'TENAAMSTELLING TEGENREKENING EN ADRES TEGENREKENING EN PLAATS TEGENREKENING EN EEN LANGE OMSCHRIJVING VAN DE TRANSACTIE 1111222233334444',
        ];
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
