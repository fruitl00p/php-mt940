<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Banking\Statement;
use Kingsquare\Banking\Hsbc\HsbcTransaction;
use Kingsquare\Banking\Transaction;
use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @author  bart (infinwebs.be)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Fnom extends Engine
{

    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'FINOM';
    }

    protected function parseStatementAccount(): string
    {
        $data = $this->getCurrentStatementData();
        if (preg_match('/:25:FNOM([^:\r\n]+)/', $data, $results) && $results[1] !== '') {
            $account = trim(preg_replace('/EUR\s*$/i', '', trim($results[1])));
            return trim($account);
        }

        return parent::parseStatementAccount();
    }

    protected function parseTransactionPrice(): float
    {
        $data = $this->getCurrentTransactionData();
        if (preg_match('/^:61:.*?(DR|CR)([\d,\.]+)N/i', $data, $results) && isset($results[2])) {
            return $this->sanitizePrice($results[2]);
        }

        return parent::parseTransactionPrice();
    }

    protected function parseTransactionDebitCredit(): string
    {
        $data = $this->getCurrentTransactionData();
        if (preg_match('/^:61:.*?(DR|CR)/i', $data, $results) && $results[1] !== '') {
            $letter = strtoupper($results[1]) === 'DR' ? 'D' : 'C';

            return $this->sanitizeDebitCredit($letter);
        }

        return parent::parseTransactionDebitCredit();
    }

    protected function parseTransactionAccount(): string
    {
        $data = $this->getCurrentTransactionData();
        if (preg_match('/\?31([A-Z]{2}[0-9]{2}[A-Z0-9]{1,34})/i', $data, $results) && $results[1] !== '') {
            return $this->sanitizeAccount($results[1]);
        }

        return parent::parseTransactionAccount();
    }

    protected function parseTransactionAccountName(): string
    {
        $data = $this->getCurrentTransactionData();
        if (preg_match('/\?32([^?\r\n]+)/', $data, $results) && $results[1] !== '') {
            return $this->sanitizeAccountName(trim($results[1]));
        }

        return parent::parseTransactionAccountName();
    }

    protected function parseTransactionDescription(): string
    {
        $parent = parent::parseTransactionDescription();
        $data = $this->getCurrentTransactionData();
        if (preg_match('/\?00([^?]+)/', $data, $results) && $results[1] !== '') {
            $label = trim($results[1]);
            if ($label !== '') {
                return $this->sanitizeDescription($label);
            }
        }

        return $parent;
    }

    /**
     * Overloaded
     *
     * @return array
     */
    protected function parseStatementData()
    {
        $results = preg_split(
            '/(^:20:|^-X{,3}$|\Z)/m',
            $this->getRawData(),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        return $results;
    }

    /**
     * Overloaded: Is applicable if first line starts with :20:AI.
     *
     * {@inheritdoc}
     */
    public static function isApplicable($string)
    {
        return strpos($string, ':25:FNOM') !== false;
    }
}
