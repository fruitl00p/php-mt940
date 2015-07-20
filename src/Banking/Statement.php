<?php
namespace Kingsquare\Banking;

/**
 * @property array rawData used for debugging purposes
 *
 * @package Kingsquare\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Statement implements \JsonSerializable
{
    private $bank = '';
    private $account = '';
    private $transactions = [];
    private $startPrice = 0.0;
    private $endPrice = 0.0;
    private $timestamp = 0;
    private $number = '';

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param string $var
     */
    public function setBank($var)
    {
        $this->bank = (string) $var;
    }

    /**
     * @param string $var
     */
    public function setAccount($var)
    {
        $this->account = (string) $var;
    }

    /**
     * @param Transaction[] $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = (array) $transactions;
    }

    /**
     * @param float $var
     */
    public function setStartPrice($var)
    {
        $this->startPrice = (float) $var;
    }

    /**
     * @param float $var
     */
    public function setEndPrice($var)
    {
        $this->endPrice = (float) $var;
    }

    /**
     * @param int $var
     */
    public function setTimestamp($var)
    {
        $this->timestamp = (int) $var;
    }

    /**
     * @param string $var
     */
    public function setNumber($var)
    {
        $this->number = (string) $var;
    }

    /**
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return float
     */
    public function getStartPrice()
    {
        return $this->startPrice;
    }

    /**
     * @return float
     */
    public function getEndPrice()
    {
        return $this->endPrice;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getTimestamp($format = 'U')
    {
        return date($format, $this->timestamp);
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction)
    {
        $this->transactions[] = $transaction;
    }

    /**
     * @return float
     */
    public function getDeltaPrice()
    {
        return ($this->getStartPrice() - $this->getEndPrice());
    }
}
