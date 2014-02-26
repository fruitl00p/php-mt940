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
}