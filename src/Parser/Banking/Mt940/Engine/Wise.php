<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Banking\Transaction\Type;
use Kingsquare\Parser\Banking\Mt940\Engine;

class Wise extends Engine
{
    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::parseStatementBank()
     */
    protected function parseStatementBank()
    {
        return 'WISE';
    }

    /**
     * uses field 25 to gather accoutnumber.
     *
     * @return string accountnumber
     */
    protected function parseStatementAccount()
    {
        $results = [];
        if (preg_match('/:25:([\d\.]+)*/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        // SEPA / IBAN
        if (preg_match('/:25:([A-Z0-9]{8}[\d\.]+)*/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return 'WISE';
    }


    /**
     * uses the 61 field to determine amount/value of the transaction.
     *
     * @return float
     */
    protected function parseTransactionPrice()
    {
        $results = [];
        if (preg_match('/^:61:.*?[CD]([\d,\.]+)F/i', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizePrice($results[1]);
        }

        return 0;
    }

    /**
     * uses the 61 field to get the bank specific transaction code.
     *
     * @return string
     */
    protected function parseTransactionCode()
    {
        $results = [];
        if (preg_match('/^:61:.*?F(.{3}).*/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return trim($results[1]);
        }
        return '';
    }
    
    /**
    * @TODO WIP get this into the transaction somehow.. (possibly as a decorator over the transactions?)
    * @return int
    */
   protected function parseTransactionType()
   {
       static $map = [
            'CHG' => Type::BANK_COSTS,
            'MSC' => Type::BANK_INTEREST,
            'TRF' => Type::TRANSFER,
            'FEX' => Type::UNKNOWN,
       ];

       $code = $this->parseTransactionCode();
       if (array_key_exists($code, $map)) {
           return $map[$code];
       }
       throw new \RuntimeException("Don't know code $code for this bank");
   }

    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::isApplicable()
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, 'F01TRWIGB2LAXXX0000000000') !== false;
    }
}
