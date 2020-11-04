<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Banking\Transaction\Type;

/**
 * Knab parser for Kingsquare mt940 package.
 *
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @author Sam Mousa (sam@mousa.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Knab extends Engine
{
    const IBAN = '[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}(?:[a-zA-Z0-9]?){0,16}';

    /**
     * @inheritdoc
     */
    protected function parseStatementBank()
    {
        return 'KNAB';
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementStartPrice()
    {
        return $this->parseStatementPrice('60[FM]');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementEndPrice()
    {
        return $this->parseStatementPrice('62[FM]');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementStartTimestamp()
    {
        return $this->parseTimestampFromStatement('60[FM]');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementEndTimestamp()
    {
        return $this->parseTimestampFromStatement('62[FM]');
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = [];

        // SEPA MT940 Structured
        if (preg_match('/^:86:.*?\/IBAN\/(' . self::IBAN . ')/ims', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }


        $pattern = '/^:86:.*?REK:\s*(?<account>' . self::IBAN . '|\d+)/ims';
        if (preg_match($pattern, $this->getCurrentTransactionData(), $results)
            && !empty($results['account'])
        ) {
            return $results['account'];
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = [];

        // SEPA MT940 Structured
        if (preg_match('#/NAME/(.*?)/(EREF|REMI|ADDR)/#ms', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/NAAM: (.+)/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return trim($results[1]);
        }
        if (preg_match('#/NAME/(.*?)\n?/(REMI|CSID)/#ms', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return trim($results[1]);
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionDescription()
    {
        $description = parent::parseTransactionDescription();

        // SEPA MT940 Structured
        if (strpos($description, '/REMI/') !== false
            && preg_match('#/REMI/(.*)[/:]?#', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }

        $accountIsInDescription = strpos($description, 'REK:');
        if ($accountIsInDescription !== false) {
            return trim(substr($description, 0, $accountIsInDescription));
        }

        $name = $this->parseTransactionAccountName();
        if ($name === '') {
            return $description;
        }
        $accountNameIsInDescription = strpos($description, $name);
        if ($accountNameIsInDescription !== false) {
            return trim(substr($description, 0, $accountNameIsInDescription - 6));
        }
        return $description;
    }

    /**
     * @inheritdoc
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");
        return strpos($firstline, 'F01KNABNL2HAXXX0000000000') !== false;
    }

    /**
     * @TODO WIP get this into the transaction somehow.. (possibly as a decorator over the transactions?)
     * @return int
     */
    protected function parseTransactionType()
    {
        static $map = [
            541 => Type::SEPA_TRANSFER,
            544 => Type::SEPA_TRANSFER,
            547 => Type::SEPA_TRANSFER,
            64 => Type::SEPA_DIRECTDEBIT,
            93 => Type::BANK_COSTS,
            13 => Type::PAYMENT_TERMINAL,
            30 => Type::PAYMENT_TERMINAL,
            'MSC' => Type::BANK_INTEREST,
            'TRF' => Type::UNKNOWN,
        ];

        $code = $this->parseTransactionCode();
        if (array_key_exists($code, $map)) {
            return $map[$code];
        }
        throw new \RuntimeException("Don't know code $code for this bank");
    }

}
