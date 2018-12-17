<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @author Timotheus Pokorra (timotheus.pokorra@solidcharity.com)
 * @license http://opensource.org/licenses/MIT MIT
 *
 * This is for german banks, for example Sparkasse
 */
class Spk extends Engine
{
    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'Spk';
    }

    /**
     * Overloaded: Sparkasse uses 60M and 60F.
     *
     * @inheritdoc
     */
    protected function parseStatementStartPrice()
    {
        return parent::parseStatementPrice('60[FM]');
    }

    /**
     * Overloaded: Sparkasse uses 60M and 60F.
     *
     * @inheritdoc
     */
    protected function parseStatementStartTimestamp()
    {
        return parent::parseTimestampFromStatement('60[FM]');
    }

    /**
     * Overloaded: Sparkasse uses 60M and 60F.
     *
     * @inheritdoc
     */
    protected function parseStatementEndTimestamp()
    {
        return parent::parseTimestampFromStatement('60[FM]');
    }

    /**
     * Overloaded: Sparkasse uses 62M and 62F.
     *
     * @inheritdoc
     */
    protected function parseStatementEndPrice()
    {
        return parent::parseStatementPrice('62[FM]');
    }

    /**
     * Overloaded: Sparkasse can have the 3rd character of the currencyname after the C/D
     * currency codes last letter is always a letter http://www.xe.com/iso4217.php.
     *
     * @inheritdoc
     */
    protected function parseTransactionPrice()
    {
        $results = [];
        if (preg_match('/^:61:.*?[CD][a-zA-Z]?([\d,\.]+)N/i', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizePrice($results[1]);
        }

        return 0;
    }

    /**
     * Overloaded: Sparkasse can have the 3rd character of the currencyname after the C/D and an "R" for cancellation befor the C/D.
     *
     * @inheritdoc
     */
    protected function parseTransactionDebitCredit()
    {
        $results = [];
        if (preg_match('/^:61:\d+R?([CD]).?\d+/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeDebitCredit($results[1]);
        }

        return '';
    }

    /**
     * Overloaded: Sparkasse use the Field 61 for cancellations
     *
     * @inheritdoc
     */
    protected function parseTransactionCancellation()
    {
        $results = [];
        return preg_match('/^:61:\d+(R)?[CD].?\d+/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1]);
    }

    /**
     * Overloaded: Sparkasse does not have a header line.
     *
     * @inheritdoc
     */
    protected function parseStatementData()
    {
        return preg_split(
            '/(^:20:|^-X{,3}$|\Z)/m',
            $this->getRawData(),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * Overloaded: Is applicable if first or second line has :20:STARTUMS or first line has -.
     *
     * @inheritdoc
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");
        $secondline = strtok("\r\n\t");

        return strpos($firstline, ':20:STARTUMS') !== false || ($firstline === '-' && $secondline === ':20:STARTUMS');
    }
}
