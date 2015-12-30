<?php

namespace Kingsquare\Contracts;

use Kingsquare\Banking\Iban;
use Kingsquare\Objects\TransactionType;

interface TransactionInterface
{


    /**
     * @return IbanInterface|null The IBAN for this transaction, or null if unknown.
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

    /**
     * @return TransactionType
     */
    public function getType();
}