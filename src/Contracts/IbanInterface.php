<?php

namespace Kingsquare\Contracts;

interface IbanInterface
{

    /**
     * IbanInterface constructor.
     * @param string $iban The IBAN number.
     * @throws \InvalidArgumentException If the $iban is not valid.
     */
    public function __construct($iban);

    /**
     * @return string The IBAN. All letters must be capitals
     */
    public function __toString();
}