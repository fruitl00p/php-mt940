<?php

namespace Kingsquare\Contracts;

interface TransactionInterface
{


    /**
     * @return string
     */
    public function getAccount();

    /**
     * @return string|null Return the account name associated with this transaction, if any.
     */
    public function getAccountName();

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getDebitCredit();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $format
     * @return string
     */
    public function getValueTimestamp($format = 'U');

    /**
     * @param string $format
     * @return string
     */
    public function getEntryTimestamp($format = 'U');

    /**
     * @return string
     */
    public function getTransactionCode();

    /**
     * @return bool
     */
    public function isDebit();

    /**
     * @return bool
     */
    public function isCredit();
}