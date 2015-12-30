<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Banking\Iban;
use Kingsquare\Objects\TransactionType;
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
     * {@inheritdoc}
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('/^:61:.*\n(.*?)(\n|\:8)/im', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            $result =  $this->sanitizeAccount($results[1]);
        } elseif (preg_match('/^:61:.{26}(.{16})/im', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            $result = $this->sanitizeAccount($results[1]);
        }

        if ($result != 'NONREF') {
            return new Iban($result);
        }
        return null;

    }

    /**
     * Overloaded: Rabo has different way of storing account name.
     *
     * {@inheritdoc}
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('#/NAME/(.*?)/(REMI|ADDR|CSID|ISDT)/#ms', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {


            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/^:61:.*? (.*)/m', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }


//            var_dump(preg_match('#/NAME/(.*?)/(REMI|ADDR|CSID|ISDT)/#ms', $this->getCurrentTransactionData(), $results));
//
//            die($this->getCurrentTransactionData());


        return '';
    }

    /**
     * Overloaded: Rabo has different way of storing transaction value timestamps (ymd).
     *
     * {@inheritdoc}
     */
    protected function parseTransactionEntryTimestamp()
    {
        $results = [];
        if (preg_match('/^:60F:[C|D]([\d]{6})/m', $this->getCurrentStatementData(), $results) && !empty($results[1])) {
            return $this->sanitizeTimestamp($results[1], 'ymd');
        }

        return 0;
    }

    /**
     * Overloaded: Rabo has different way of storing transaction value timestamps (ymd).
     *
     * {@inheritdoc}
     */
    protected function parseTransactionValueTimestamp()
    {
        $results = [];
        if (preg_match('/^:61:([\d]{6})[C|D]/', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
            return $this->sanitizeTimestamp($results[1], 'ymd');
        }

        return 0;
    }

    /**
     * Overloaded: Rabo encapsulates the description with /REMI/ for SEPA.
     *
     * {@inheritdoc}
     */
    protected function sanitizeDescription($string)
    {
        $description = parent::sanitizeDescription($string);
        if (strpos($description, '/REMI/') !== false
                && preg_match('#/REMI/(.*?)/(ISDT|CSID|RTRN)/#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }
        if (strpos($description, '/EREF/') !== false
                && preg_match('#/EREF/(.*?)/(ORDP)/#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }
        return '';
//        var_dump(preg_match('#/REMI|BENM/(.*?)/(ISDT|CSID|RTRN)/#s', $description, $results));
//        var_dump($results);
        var_dump($this->getCurrentTransactionData());
        var_dump($string);
        die($description);

        return $description;
    }

    /**
     * Overloaded: Is applicable if first line has :940:.
     *
     * {@inheritdoc}
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, ':940:') !== false;
    }

    protected function parseTransactionType()
    {
        $code = $this->parseTransactionCode();
        switch ($code) {
            case 541:
            case 544:
            case 547:
                $result = TransactionType::get(TransactionType::SEPA_TRANSFER);
                break;
            case 64:
                $result = TransactionType::get(TransactionType::SEPA_DIRECTDEBIT);
                break;
            case 93:
                $result = TransactionType::get(TransactionType::BANK_COSTS);
                break;
            case 13:
            case 30:
                $result = TransactionType::get(TransactionType::PAYMENT_TERMINAL);
                break;
            case "MSC":
                $result = TransactionType::get(TransactionType::BANK_INTEREST);
                break;
            default:
                var_dump($code);
                var_dump($this->getCurrentTransactionData()); die();
                throw new \RuntimeException("Don't know code $code for RABOBANK");
        }

        return $result;
    }




}
