<?php


namespace Kingsquare\Objects;


use ValueObjects\Enum\Enum;

class TransactionType extends Enum
{
    const TRANSFER = 10;
    const SEPA_TRANSFER = 11;
    const SAVINGS_TRANSFER = 12;


    const SEPA_DIRECTDEBIT = 20;

    // Codes indicating transfers by the bank.
    const BANK_COSTS = 30;
    const BANK_INTEREST = 31;



    const ATM_WITHDRAWAL = 40;


    const PAYMENT_TERMINAL = 50;
    const UNKNOWN = 99;


}