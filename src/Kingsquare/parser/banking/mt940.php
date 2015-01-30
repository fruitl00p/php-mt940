<?php
namespace Kingsquare\Parser\Banking;
use Kingsquare\Parser\Banking;

/**
 * @package Kingsquare\Parser\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Mt940 extends Banking {
	/** @var Banking\Mt940\Engine */
	protected $engine;

    /** @var bool */
    public static $removeIBAN = true; // defaults to true for BC

    /** @var bool */
    public $debug = false;

	/**
	 * Parse the given string into an array of Banking\Statement objects
	 * @param string $string
	 * @return \Kingsquare\Banking\Statement[]
	 */
	function parse($string) {
		if (!empty($string)) {
			// load engine
			$this->engine = Banking\Mt940\Engine::__getInstance($string);
            if($this->debug) {
                $this->engine->debug = $this->debug;
            }
			if ($this->engine instanceof Banking\Mt940\Engine) {
				// parse using the engine
				return $this->engine->parse();
			}
		}
		return array();
	}
}
