<?php

namespace Kingsquare\Banking;

/**
 * @property array rawData used for debugging purposes
 *
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
    private $startTimestamp = 0;
    private $endTimestamp = 0;
    private $number = '';
    private $currency = '';

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param string $var
     */
    public function setBank($var)
    {
        $this->bank = (string)$var;
    }

    /**
     * @param string $var
     */
    public function setAccount($var)
    {
        $this->account = (string)$var;
    }

    /**
     * @param Transaction[] $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = (array)$transactions;
    }

    /**
     * @param float $var
     */
    public function setStartPrice($var)
    {
        $this->startPrice = (float)$var;
    }

    /**
     * @param float $var
     */
    public function setEndPrice($var)
    {
        $this->endPrice = (float)$var;
    }

    /**
     * @deprecated
     *
     * @param int $var
     */
    public function setTimestamp($var)
    {
        trigger_error('Deprecated in favor of splitting the start and end timestamps for a statement. ' .
            'Please use setStartTimestamp($format) or setEndTimestamp($format) instead. ' .
            'setTimestamp is now setStartTimestamp', E_USER_DEPRECATED);
        return $this->setStartTimestamp($var);
    }

    /**
     * @param $var
     */
    public function setStartTimestamp($var)
    {
        $this->startTimestamp = (int)$var;
    }

    /**
     * @param $var
     */
    public function setEndTimestamp($var)
    {
        $this->endTimestamp = (int)$var;
    }

    /**
     * @param string $var
     */
    public function setNumber($var)
    {
        $this->number = (string)$var;
    }

    /**
     * @param string $var
     */
    public function setCurrency($var)
    {
        $this->currency = (string)$var;
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
     * @deprecated This method will be removed in favor of getStartTimestamp / getEndTimestamp this is slated for removal in next major
     *
     * @return string
     */
    public function getTimestamp($format = 'U')
    {
        trigger_error('Deprecated in favor of splitting the start and end timestamps for a statement. ' .
            'Please use getStartTimestamp($format) or getEndTimestamp($format) instead. ' .
            'getTimestamp is now getStartTimestamp', E_USER_DEPRECATED);

        return $this->getStartTimestamp($format);
    }

    /**
     * @param string $format
     *
     * @return bool|string
     */
    public function getStartTimestamp($format = 'U')
    {
        return date($format, $this->startTimestamp);
    }

    /**
     * @param string $format
     *
     * @return bool|string
     */
    public function getEndTimestamp($format = 'U')
    {
        return date($format, $this->endTimestamp);
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
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
        return $this->getStartPrice() - $this->getEndPrice();
    }
}
