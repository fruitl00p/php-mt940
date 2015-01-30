<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Timotheus Pokorra (timotheus.pokorra@solidcharity.com)
 * @license http://opensource.org/licenses/MIT MIT
 * 
 * This is for german banks, for example Sparkasse
 * 
 */
class Spk extends Engine {
	/**
	 * returns the name of the bank
	 * @return string
	 */
	protected function parseStatementBank() {
		return 'Spk';
	}

	/**
	 * Overloaded: Sparkasse does not have a header line
	 * @inheritdoc
	 */
	protected function parseStatementData() {
		$results = preg_split('/(^:20:|^-X{,3}$|\Z)/sm',
				$this->getRawData(),
				-1,
				PREG_SPLIT_NO_EMPTY);
		//array_shift($results); // remove the header
		return $results;
	}
}
