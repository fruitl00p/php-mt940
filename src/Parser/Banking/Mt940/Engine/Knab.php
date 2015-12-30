<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;

use IBAN\Generation\IBANGenerator;
use Kingsquare\Banking\Iban;
use Kingsquare\Parser\Banking\Mt940\Engine;

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
     * returns the name of the bank
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'KNAB';
    }

    protected function parseTransactionAccount()
    {
        $results = [];

        $pattern = '/^:86:.*?REK:\s*(?<account>' . self::IBAN . '|\d+)/ims';
        if (preg_match($pattern, $this->getCurrentTransactionData(), $results)
            && !empty($results['account'])
        ) {
//            if ($results['account'] == '90000014'){
//                return null;
//            }

            try {
                $result = new Iban($results['account']);
            } catch (\InvalidArgumentException $e) {
                $result = new Iban(IBANGenerator::NL('KNAB', $results['account']));
            }
            return $result;

        }
    }

    protected function parseTransactionAccountName()
    {
        $results = [];
        if (preg_match('/^:86:.*?\/NAAM: (.*?)\s*:\d{2}.?:/ims', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $results[1];
        }
    }

    protected function parseTransactionDescription()
    {
        if (preg_match('/^:86:(.*?)REK:/ims', $this->getCurrentTransactionData(), $results)) {
            return $results[1];
        }
    }

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
            default:
                var_dump($code);
                var_dump($this->getCurrentTransactionData()); die();
                throw new \RuntimeException("Don't know code $code for RABOBANK");
        }

        return $result;
    }

}
