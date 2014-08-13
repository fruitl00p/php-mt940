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
	 * Overloaded: Added simple IBAN transaction handling
	 * @inheritdoc
	 */
	protected function _parseTransactionAccount() {
		$account = parent::_parseTransactionAccount();
		if ($account !== '') {
			return $account;
		}

		// IBAN
		$transactionData = str_replace('Europese Incasso, doorlopend ', '', $this->getCurrentTransactionData());
		$transactionData = preg_replace('![\r\n]+!', '', $transactionData);
		if (preg_match('#/CNTP/(.*?)/#', $transactionData, $results)) {
			$account = trim($results[1]);
			if (!empty($account)) {
				return $this->_sanitizeAccount($account);
			}
		}
		if (preg_match('#:86:([A-Z]{2}[0-9]{2}[A-Z]{4}[\d]+?) [A-Z]{6}[A-Z0-9]{0,4} #', $transactionData, $results)) {
			$account = trim($results[1]);
			if (!empty($account)) {
				return $this->_sanitizeAccount($account);
			}
		}
		return '';
	}

	/**
	 * Overloaded: Added simple IBAN transaction handling
	 * @inheritdoc
	 */
	protected function _parseTransactionAccountName() {
		$name = parent::_parseTransactionAccountName();
		if ($name !== '') {
			return $name;
		}

		// IBAN
		$transactionData = str_replace('Europese Incasso, doorlopend ', '', $this->getCurrentTransactionData());
		$transactionData = preg_replace('![\r\n]+!', '', $transactionData);
		if (preg_match('#/CNTP/[^/]*/[^/]*/(.*?)/#', $transactionData, $results)) {
			$name = trim($results[1]);
			if (!empty($name)) {
				return $this->_sanitizeAccountName($name);
			}
		}
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

	/**
	 * Overloaded: ING encapsulates the description with /REMI/ for SEPA
	 * @inheritdoc
	 */
	function _sanitizeDescription($string) {
		$description = parent::_sanitizeDescription($string);
		if (strpos($description, '/REMI/USTD//') !== false
				&& preg_match('#/REMI/USTD//(.*?)/#s', $description, $results) && !empty($results[1])) {
			return $results[1];
		}
		if (strpos($description, '/REMI/STRD/CUR/') !== false
				&& preg_match('#/REMI/STRD/CUR/(.*?)/#s', $description, $results) && !empty($results[1])) {
			return $results[1];
		}
		return $description;
	}

}