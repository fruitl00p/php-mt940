<?php

namespace Kingsquare\Contracts;

interface ParserInterface
{


    /**
     * @param string $data The data to be parsed.
     * @return StatementInterface[]
     */
    public function parse($data);


    /**
     * @param string $data The data to be tested.
     * @return bool True if the parser can parse the data.
     */
    public static function isApplicable($data);
}