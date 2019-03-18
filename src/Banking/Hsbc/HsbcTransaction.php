<?php

namespace Kingsquare\Banking\Hsbc;

use Kingsquare\Banking\Transaction;

/**
 * HSBC's Transaction class with additional information such as virtual account.
 *
 * @author  jun (jun.chen@meetsocial.cn)
 * @license http://opensource.org/licenses/MIT MIT
 */
class HsbcTransaction extends Transaction
{
    private $virtualAccount = '';

    /**
     * @param string $var
     */
    public function setVirtualAccount($var)
    {
        $this->virtualAccount = (string)$var;
    }

    /**
     * @return string
     */
    public function getVirtualAccount()
    {
        return $this->virtualAccount;
    }

}
