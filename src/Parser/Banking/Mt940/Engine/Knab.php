<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Objects\TransactionType;

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
        return $this->parseStatementPrice('60M');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementEndPrice()
    {
        return $this->parseStatementPrice('62M');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementStartTimestamp()
    {
        return $this->parseTimestampFromStatement('60M');
    }

    /**
     * @inheritdoc
     */
    protected function parseStatementEndTimestamp()
    {
        return $this->parseTimestampFromStatement('62M');
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = [];

        $pattern = '/^:86:.*?REK:\s*(?<account>' . self::IBAN . '|\d+)/ims';
        if (preg_match($pattern, $this->getCurrentTransactionData(), $results)
                && !empty($results['account'])
        ) {
            return $results['account'];
        }
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        if (preg_match('/NAAM: (.+)/', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            return trim($results[1]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionDescription()
    {
        $description = parent::parseTransactionDescription();
        $accountIsInDescription = strpos($description, 'REK:');
        if ($accountIsInDescription !== false) {
            return trim(substr($description, 0, $accountIsInDescription));
        }

        $name = $this->parseTransactionAccountName();
        $accountNameIsInDescription = strpos($description, $name);
        if ($accountNameIsInDescription !== false) {
            return trim(substr($description, 0, $accountNameIsInDescription-6));
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
            case "TRF":
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
