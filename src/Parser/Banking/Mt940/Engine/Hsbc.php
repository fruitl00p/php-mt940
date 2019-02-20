<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Banking\Transaction;
use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @author  jun (jun.chen@meetsocial.cn)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Hsbc extends Engine
{
    const PATTERN_TAG_61 = '/^:61:(\d{6})(\d{4}?)(C|D|EC|ED|RC|RD)[A-Z](\d+,\d+)(F|N|S)([A-Z]{3})(.{16})/m';

    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'HSBC';
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
     * Overloaded: 16x, like 808XXXXXX292 .
     *
     * @return string accountnumber
     */
    protected function parseStatementAccount()
    {
        $results = [];
        if (preg_match('/:25:([0-9X]+)*/', $this->getCurrentStatementData(), $results)
            && ! empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return '';
    }

    /**
     * Overloaded: HSBC has different way of storing account info.
     *
     * {@inheritdoc}
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        // YYMMDD[MMDD]2a[1!a]15d1!a3!c16x[//16x][34x]
        // eg.: :61:1203290329DD20000,00NTRF1004688128      //6128522200250001
        // Reference for the Account Owner (16x): 1004688128
        // Reference of the Account Servicing Institution [//16x]: 6128522200250001
        // Supplementary Details [34x]: null
        if (preg_match(self::PATTERN_TAG_61, $this->getCurrentTransactionData(), $results)) {
            return $this->sanitizeAccount($results[7]);
        }

        return '';
    }

    /**
     * Overloaded: debit or credit of the transaction.
     *
     * @return string
     */
    protected function parseTransactionDebitCredit()
    {
        $results = [];
        if (preg_match(self::PATTERN_TAG_61, $this->getCurrentTransactionData(), $results)) {
            return $this->sanitizeDebitCredit($results[3]);
        }

        return '';
    }

    /**
     * Overloaded: HSBC has different way of storing account name.
     *
     * {@inheritdoc}
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('#/NAME/(.+?)\n?/(REMI|ADDR|ISDT|CSID)/#ms', $this->getCurrentTransactionData(), $results)) {
            $accountName = trim($results[1]);
            if ( ! empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/^:61:.*? (.+)/m', $this->getCurrentTransactionData(), $results)) {
            $accountName = trim($results[1]);
            if ( ! empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/(.*) Betaalautomaat/', $this->parseTransactionDescription(), $results)) {
            $accountName = trim($results[1]);
            if ( ! empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }
        return '';
    }

    /**
     * Overloaded: HSBC has different way of storing transaction value timestamps (ymd).
     *
     * {@inheritdoc}
     */
    protected function parseTransactionEntryTimestamp()
    {
        $results = [];
        if (preg_match('/^:60F:[C|D]([\d]{6})/m', $this->getCurrentStatementData(), $results)) {
            return $this->sanitizeTimestamp($results[1], 'ymd');
        }

        return 0;
    }

    /**
     * Overloaded: HSBC has different way of storing transaction value timestamps (ymd).
     *
     * {@inheritdoc}
     */
    protected function parseTransactionValueTimestamp()
    {
        $results = [];
        if (preg_match(self::PATTERN_TAG_61, $this->getCurrentTransactionData(), $results)) {
            return $this->sanitizeTimestamp($results[1], 'ymd');
        }

        return 0;
    }

    /**
     * Overloaded: HSBC encapsulates the description with /REMI/ for SEPA.
     *
     * {@inheritdoc}
     */
    protected function sanitizeDescription($string)
    {
        $description = parent::sanitizeDescription($string);
        if (strpos($description, '/REMI/') !== false
            && preg_match('#/REMI/(.*?)(/((PURP|ISDT|CSID|RTRN)/)|$)#s', $description, $results) && ! empty($results[1])
        ) {
            return $results[1];
        }
        if (strpos($description, '/EREF/') !== false
            && preg_match('#/EREF/(.*?)/(ORDP)/#s', $description, $results) && ! empty($results[1])
        ) {
            return $results[1];
        }

        return $description;
    }

    /**
     * Overloaded: HSBC has some specific debit/credit marks.
     *
     * @param string $string
     *
     * @return string
     */
    protected function sanitizeDebitCredit($string)
    {
        $debitOrCredit = strtoupper(substr((string) $string, -1, 1));
        if ($debitOrCredit !== Transaction::DEBIT && $debitOrCredit !== Transaction::CREDIT) {
            trigger_error('wrong value for debit/credit ('.$string.')', E_USER_ERROR);
            $debitOrCredit = '';
        }

        return $debitOrCredit;
    }

    /**
     * Overloaded: Is applicable if first line starts with :20:AI.
     *
     * {@inheritdoc}
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, ':20:AI') !== false && strlen($firstline) === 20;
    }
}
