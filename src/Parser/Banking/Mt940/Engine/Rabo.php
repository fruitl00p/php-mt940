<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Banking\Transaction\Type;

/**
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Rabo extends Engine
{
    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'Rabo';
    }

    /**
     * Overloaded: Rabo has different way of storing account info.
     *
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('/^:61:.*\n(.*?)(\n|\:8)/im', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        if (preg_match('/^:61:.{26}(.{16})/m', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return '';
    }

    /**
     * Overloaded: Rabo has different way of storing account name.
     *
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('#/NAME/(.+?)\n?/(REMI|ADDR|ISDT|CSID)/#ms', $this->getCurrentTransactionData(), $results)) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/^:61:.*? (.+)/m', $this->getCurrentTransactionData(), $results)) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/(.*) Betaalautomaat/', $this->parseTransactionDescription(), $results)) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }
        return '';
    }

    /**
     * Overloaded: Rabo has different way of storing transaction value timestamps (ymd).
     *
     * @inheritdoc
     */
    protected function parseTransactionEntryTimestamp()
    {
        $results = [];
        if (preg_match('/^:60F:[C|D]([\d]{6})/m', $this->getCurrentStatementData(), $results) && !empty($results[1])) {
            return $this->sanitizeTimestamp($results[1]);
        }

        return 0;
    }

    /**
     * Overloaded: Rabo has different way of storing transaction value timestamps (ymd).
     *
     * @inheritdoc
     */
    protected function parseTransactionValueTimestamp()
    {
        $results = [];
        if (preg_match('/^:61:([\d]{6})[C|D]/', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
            return $this->sanitizeTimestamp($results[1]);
        }

        return 0;
    }

    /**
     * Overloaded: Rabo uses longer strings for accountnumbers.
     *
     * @inheritdoc
     */
    protected function sanitizeAccount($string)
    {
        $account = parent::sanitizeAccount($string);
        if (strlen($account) > 20 && strpos($account, '80000') === 0) {
            $account = substr($account, 5);
        }

        return $account;
    }

    /**
     * Overloaded: Rabo encapsulates the description with /REMI/ for SEPA.
     *
     * @inheritdoc
     */
    protected function sanitizeDescription($string)
    {
        $description = parent::sanitizeDescription($string);
        if (strpos($description, '/REMI/') !== false
            && preg_match('#/REMI/(.*?)(/((PURP|ISDT|CSID|RTRN)/)|$)#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }
        if (strpos($description, '/EREF/') !== false
            && preg_match('#/EREF/(.*?)/(ORDP)/#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }

        if (strpos($description, '/PREF/') !== false
            && preg_match('#/PREF/(.*)/?#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }

        return $description;
    }

    /**
     * Overloaded: Is applicable if first line has :940:.
     *
     * @inheritdoc
     */
    public static function isApplicable($string)
    {
        return strpos(strtok($string, "\r\n\t"), ':940:') !== false;
    }

    /**
     * @return int
     */
    protected function parseTransactionType()
    {
        static $map = [
            102 => Type::SEPA_TRANSFER,  // "Betaalopdracht IDEAL"
            541 => Type::SEPA_TRANSFER,
            544 => Type::SEPA_TRANSFER,
            547 => Type::SEPA_TRANSFER,
            504 => Type::SAVINGS_TRANSFER,
            691 => Type::SAVINGS_TRANSFER,
            64 => Type::SEPA_DIRECTDEBIT,
            93 => Type::BANK_COSTS,
            12 => Type::PAYMENT_TERMINAL,
            13 => Type::PAYMENT_TERMINAL,
            30 => Type::PAYMENT_TERMINAL,
            29 => Type::ATM_WITHDRAWAL,
            31 => Type::ATM_WITHDRAWAL,
            79 => Type::UNKNOWN,
            'MSC' => Type::BANK_INTEREST,
        ];

        $code = $this->parseTransactionCode();
        if ($code === 404) {
            return (stripos($this->getCurrentTransactionData(),
                    'eurobetaling') !== false) ? Type::SEPA_TRANSFER : Type::TRANSFER;
        }

        if (array_key_exists($code, $map)) {
            return $map[$code];
        }

        throw new \RuntimeException("Don't know code $code for this bank");
    }

}
