<?php

namespace Kingsquare\Contracts;

interface ParserInterface
{


    /**
     * @param $string
     * @return StatementInterface[]
     */
    public function parse($string);
}