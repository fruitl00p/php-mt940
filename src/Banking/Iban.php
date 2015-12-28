<?php


namespace Kingsquare\Banking;


use IBAN\Validation\IBANValidator;
use Kingsquare\Contracts\IbanInterface;

class Iban implements IbanInterface
{
    protected $iban;
    /**
     * IbanInterface constructor.
     * @param string $iban The IBAN number.
     * @throws \InvalidArgumentException If the $iban is not valid.
     */
    public function __construct($iban)
    {
        if ($this->validate($iban)) {
            $this->iban = $iban;
        } else {
            throw new \InvalidArgumentException("$iban is not a valid IBAN");
        }
    }

    /**
     * @return string The IBAN. All letters must be capitals
     */
    public function __toString()
    {
        return strtoupper($this->iban);
    }

    public static function validate($iban)
    {
        return (new IBANValidator())->validate($iban);
    }
}