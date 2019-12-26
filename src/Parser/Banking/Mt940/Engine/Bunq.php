<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

class Bunq extends Engine
{

    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::parseStatementBank()
     */
    protected function parseStatementBank()
    {
        return 'BUNQ';
    }

    /**
     *
     * {@inheritdoc}
     * @see \Kingsquare\Parser\Banking\Mt940\Engine::isApplicable()
     */
    public static function isApplicable($string)
    {
        $firstline = strtok($string, "\r\n\t");

        return strpos($firstline, 'BUNQ') !== false;
    }
}
