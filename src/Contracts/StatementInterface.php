<?php

namespace Kingsquare\Contracts;

interface StatementInterface
{

    /**
     * @return TransactionInterface[]
     */
    public function getTransactions();
}