<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @author Sevan Nerse (nerse.sevan@gmail.com)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Kontist extends Engine
{
    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'KONTIST';
    }

    /**
     * split the rawdata up into statementdata chunks.
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
     * Overloaded: Is applicable if second line has :25:TRIODOSBANK.
     *
     * @inheritdoc
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, 'KONTIST') !== false;
    }
}
