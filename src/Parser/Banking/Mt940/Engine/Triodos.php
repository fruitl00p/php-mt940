<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Triodos extends Engine
{
    /**
     * returns the name of the bank
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'Triodos';
    }

    /**
     * Overloaded: the bankaccount is always prefixed
     *
     * @inheritdoc
     */
    protected function parseStatementAccount()
    {
        $results = array();
        if (preg_match('#:25:TRIODOSBANK/([\d\.]+)#', $this->getCurrentStatementData(), $results) && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return parent::parseStatementAccount();
    }

    /**
     * Overloaded: According to spec, field :28: is always 1
     *
     * @inheritdoc
     */
    protected function parseStatementNumber()
    {
        return 1;
    }

    /**
     * Overloaded: According to spec, field :28: is always 000
     *
     * @inheritdoc
     */
    protected function parseTransactionCode()
    {
        return '000';
    }

    /**
     * Overloaded: It might be IBAN or not and depending on that return a different part of the description
     *
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $parts = $this->getDescriptionParts();
        $account = $parts[0];
        if (preg_match('#[A-Z]{2}[0-9]{2}[A-Z]{4}(.*)#', $parts[2], $results)) {
            $account = $parts[2];
        } elseif (preg_match('#10(\d+)#', $parts[0], $results)) {
            $account = $results[1];
        }

        return $this->sanitizeAccount($account);
    }

    /**
     * Overloaded: It might be IBAN or not and depending on that return a different part of the description
     *
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $parts = $this->getDescriptionParts();
        array_shift($parts); // remove BBAN / BIC code
        if (preg_match('#[A-Z]{2}[0-9]{2}[A-Z]{4}(.*)#', $parts[1], $results)) {
            array_shift($parts); // remove IBAN too
            array_shift($parts); // remove IBAN too
        }
        array_pop($parts);// remove own account / BBAN
        return $this->sanitizeAccountName(substr(array_shift($parts), 2));
    }

    /**
     * Crude parsing of the combined iban / non iban description field
     *
     * @inheritdoc
     */
    protected function parseTransactionDescription()
    {
        $parts = $this->getDescriptionParts();
        array_shift($parts); // remove BBAN / BIC code
        if (preg_match('#[A-Z]{2}[0-9]{2}[A-Z]{4}(.*)#', $parts[1], $results)) {
            array_shift($parts); // remove IBAN too
            array_shift($parts); // remove IBAN too
        }

        array_pop($parts);// remove own account / BBAN
        foreach ($parts as &$part) {
            $part = substr($part, 2);
        }

        return $this->sanitizeDescription(implode('', $parts));
    }

    /**
     * In Triodos everything is put into :86: field with '>\d{2}' seperators
     * This method parses that out and returns the array
     *
     * @return array
     */
    private function getDescriptionParts() {
        $description = parent::parseTransactionDescription();
        $parts = explode('>', $description);
        array_shift($parts); // remove 000 prefix
        return $parts;
    }

    /**
     * Overloaded: Do not skip a header
     *
     * @inheritdoc
     */
    protected function parseStatementData()
    {
        return preg_split(
                '/(^:20:|^-X{,3}$|\Z)/sm',
                $this->getRawData(),
                -1,
                PREG_SPLIT_NO_EMPTY
        );
    }

}
