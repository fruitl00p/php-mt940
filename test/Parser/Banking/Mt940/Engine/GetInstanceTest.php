<?php
namespace Kingsquare\Parser\Banking\Mt940;

/**
 *
 */
class GetInstanceTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testUnknownEngineRaisesANotice()
    {
        $error_reporting = error_reporting();
        error_reporting(E_ALL);
        try {
            Engine::__getInstance('this is an unknown format :)');
        } catch (\PHPUnit_Framework_Error $exptected) {
            error_reporting($error_reporting);
            $this->assertInstanceOf('PHPUnit_Framework_Error', $exptected);

            return;
        }
        error_reporting($error_reporting);
        $this->fail('Did not receive the notice');
    }

    /**
     * @dataProvider enginesProvider
     */
    public function testEngine($engineString, $source)
    {
        $engine = @Engine::__getInstance($source);
        $this->assertInstanceOf('\\Kingsquare\\Parser\\Banking\\Mt940\\Engine\\' . $engineString, $engine);
    }

    /**
     * @return array
     */
    public function enginesProvider()
    {
        return array(
                array('Abn', file_get_contents(__DIR__ . '/Abn/sample')),
                array('Ing', file_get_contents(__DIR__ . '/Ing/sample')),
                array('Rabo', file_get_contents(__DIR__ . '/Rabo/sample')),
                array('Spk', file_get_contents(__DIR__ . '/Spk/sample')),
                array('Unknown', 'this is an unknown format :)'),
        );
    }
}
