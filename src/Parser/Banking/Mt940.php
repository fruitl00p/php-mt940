<?php
namespace Kingsquare\Parser\Banking;

use Kingsquare\Parser\Banking;

/**
 * @package Kingsquare\Parser\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Mt940 extends Banking
{
    /** @var Banking\Mt940\Engine */
    protected $engine;

    /** @var bool */
    public static $removeIBAN = true; // defaults to true for BC

    /**
     * Parse the given string into an array of Banking\Statement objects
     *
     * @param string $string
     * @param Banking\Mt940\Engine $engine
     *
     * @return \Kingsquare\Banking\Statement[]
     */
    public function parse($string, Banking\Mt940\Engine $engine = null)
    {
        if (!empty($string)) {
            // load engine
            if ($engine === null) {
                $engine = Banking\Mt940\Engine::__getInstance($string);
            }

            $this->engine = $engine;

            if ($this->engine instanceof Banking\Mt940\Engine) {
                // parse using the engine
                $this->engine->loadString($string);
                return $this->engine->parse();
            }
        }

        return [];
    }
}
