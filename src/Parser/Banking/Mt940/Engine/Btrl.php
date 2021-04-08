<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

class Btrl extends Engine
{
    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::parseStatementBank()
     */
    protected function parseStatementBank()
    {
        return 'BTRL';
    }
	
   /**
     * uses the 61 field to determine amount/value of the transaction.
     *
     * @return float
     */
    protected function parseTransactionPrice()
    {
        $results = [];
        if (preg_match('/^:61:.*?[CD]([\d,\.]+)[NSF]/i', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizePrice($results[1]);
        }

        return 0;
    }    
	

    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::isApplicable()
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, 'BTRL') !== false;
    }
}
