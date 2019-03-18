<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * **CAUTION** This is an untested / experimental engine for SNS
 *
 *
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Paul Olthof (hpolthof@gmail.com)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Sns extends Engine
{
    /**
     * returns the name of the bank
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'SNS';
    }

    /**
     * @override just return the unsanitized IBAN string
     * @inheritdoc
     */
    protected function sanitizeAccount($string)
    {
        return $string;
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        if (preg_match('/^:86:\s?([A-z\d]+)\s/im', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        if (preg_match('/^:86:\s?[A-z\d]+\s(.*?)$/im', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return strtoupper($this->sanitizeAccountName($results[1]));
        }

        return '';
    }

    /**
     * @override filter out the first two meta-data lines
     * @inheritdoc
     */
    protected function parseTransactionDescription()
    {
        $results = [];
        if (preg_match_all('/[\n]:86:(.*?)(?=\n(:6(1|2))|$)/s', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            // filter out meta data
            $lines = explode("\r\n", $results[1][0]);
            unset($lines[0], $lines[1]);
            $results[1][0] = implode("\r\n", $lines);

            return $this->sanitizeDescription(implode(PHP_EOL, $results[1]));
        }

        return '';
    }

}
