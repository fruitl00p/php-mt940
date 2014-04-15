<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Ing extends Engine {
	/**
	 * returns the name of the bank
	 * @return string
	 */
	protected function _parseStatementBank() {
		return 'ING';
	}

	/**
	 * @return string accountnumber
	 */
	protected function _parseTransactionAccount() {
		$account = parent::_parseTransactionAccount();
		if ($account !== '') {
			return $account;
		}

		// IBAN
		$transactionData = str_replace('Europese Incasso, doorlopend ', '', $this->getCurrentTransactionData());
		$transactionData = preg_replace('![\r\n]+!', '', $transactionData);
		if (preg_match('#:86:([A-Z]{2}[0-9]{2}[A-Z]{4}[\d]+?) [A-Z]{6}[A-Z0-9]{0,4} #', $transactionData, $results)) {
			$account = trim($results[1]);
			if (!empty($account)) {
				return $this->_sanitizeAccount($account);
			}
		}
		return '';
	}

	/**
	 * @return string accountnumber
	 */
	protected function _parseTransactionAccountName() {
		$name = parent::_parseTransactionAccountName();
		if ($name !== '') {
			return $name;
		}

		// IBAN
		$transactionData = str_replace('Europese Incasso, doorlopend ', '', $this->getCurrentTransactionData());
		$transactionData = preg_replace('![\r\n]+!', '', $transactionData);
		if (preg_match('#:86:.*? [^ ]+ (.*)#', $transactionData, $results) !== 1) {
			return '';
		}
		$transactionData = $results[1];
		if (preg_match('#(.*) (Not-Provided|NOTPROVIDED)#', $transactionData, $results) === 1) {
			$name = trim($results[1]);
			if (!empty($name)) {
				return $this->_sanitizeAccountName($name);
			}
		}

		if (preg_match('#\D+#', $transactionData, $results)) {
			$name = trim($results[0]);
			if (!empty($name)) {
				return $this->_sanitizeAccountName($name);
			}
		}
		return '';
	}

}