<?php

namespace Kingsquare\Parser\Banking\Mt940;

use Kingsquare\Parser\Banking\Mt940\Engine\Unknown;
use PHPUnit\Framework\Error;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class GetInstanceTest extends TestCase
{
    /**
     * @dataProvider enginesProvider
     *
     * @param string $engineString
     * @param string $source
     */
    public function testEngine($engineString, $source)
    {
        $engine = @Engine::__getInstance($source);
        $this->assertInstanceOf('\\Kingsquare\\Parser\\Banking\\Mt940\\Engine\\'.$engineString, $engine);
    }

    /**
     * @dataProvider enginesProvider
     *
     * @param string $engineString
     * @param string $source
     */
    public function testSingleEngine($engineString, $source)
    {
        Engine::resetEngines();
        $engine = @Engine::__getInstance($source);
        $this->assertInstanceOf(Unknown::class, $engine);
    }

    /**
     * @return array
     */
    public function enginesProvider()
    {
        return [
                ['Abn', file_get_contents(__DIR__.'/Abn/sample')],
                ['Ing', file_get_contents(__DIR__.'/Ing/sample')],
                ['Rabo', file_get_contents(__DIR__.'/Rabo/sample')],
                ['Spk', file_get_contents(__DIR__.'/Spk/sample')],
                ['Triodos', file_get_contents(__DIR__.'/Triodos/sample')],
                ['Unknown', 'this is an unknown format :)'],
        ];
    }
}
