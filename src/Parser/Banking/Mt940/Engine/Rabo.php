<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

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

    protected function parseTransactionType()
    {
        $code = $this->parseTransactionCode();
        switch ($code) {
            case 541:
            case 544:
            case 102: // "Betaalopdracht IDEAL"
            case 547:
                $result = TransactionType::get(TransactionType::SEPA_TRANSFER);
                break;

            case 504:
            case 691:
                $result = TransactionType::get(TransactionType::SAVINGS_TRANSFER);
                break;
            case 64:
                $result = TransactionType::get(TransactionType::SEPA_DIRECTDEBIT);
                break;
            case 93:
                $result = TransactionType::get(TransactionType::BANK_COSTS);
                break;
            case 13:
            case 12:
            case 30:
                $result = TransactionType::get(TransactionType::PAYMENT_TERMINAL);
                break;
            case 29:
            case 31:
                $result = TransactionType::get(TransactionType::ATM_WITHDRAWAL);
                break;
            case "MSC":
                $result = TransactionType::get(TransactionType::BANK_INTEREST);
                break;
            case 404: // "Buitenland transactie credit"
                if (stripos($this->getCurrentTransactionData(), 'eurobetaling') !== false) {
                    $result = TransactionType::get(TransactionType::SEPA_TRANSFER);
                } else {
                    $result = TransactionType::get(TransactionType::TRANSFER);
                }

                break;
            case 79: // "Acceptgiro Mobiel Bankieren"
                $result = TransactionType::get(TransactionType::UNKNOWN);
                break;

            default:
                var_dump($code);
                var_dump($this->getCurrentTransactionData()); die();
                throw new \RuntimeException("Don't know code $code for RABOBANK");
        }

        return $result;
    }

}
