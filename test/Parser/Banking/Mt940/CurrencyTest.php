<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Banking\Statement;
use Kingsquare\Parser\Banking\Mt940\Engine;
use PHPUnit\Framework\TestCase;

class SingleStatementEngine extends Engine
{
    protected function parseStatementData()
    {
        return [$this->getRawData()];
    }
}

/**
 *
 */
class CurrencyTest extends TestCase
{
    /**
     * @dataProvider singleStatementProvider
     *
     * @param $expected
     * @param $input
     */
    public function singleStatement($expected, $input)
    {
        $engine = new SingleStatementEngine();
        $engine->loadString($input);

        $this->assertEquals($expected, current($engine->parse())->getCurrency());
    }

    public function singleStatementProvider()
    {
        return [
            [
                'TRY',
                ':20:20121030TRY
:25:TR/0111-01024/34690711
:28:12304/020
:60F:C121025TRY0000000000000,00
:61:121030CT0000000002787,29NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT27100433 Fatura?: AT27100433 Fatura No: 4300022921.
:61:121030CT0000000001025,99NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT27100433 Fatura?: AT27100433 Fatura No: 4300022922.
:61:121030CT0000000000143,97NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT27100433 Fatura?: AT27100433 Fatura No: 4300022923.
:61:121030DT0000000003957,25NMSCNONREF//
:86:001?METRO GROSMARKET BAKIRKÖY ALIÞVERÝÞ HÝZMETLERÝ TÝC.LTD.ÞTÝ., 3469?Ý TÝC.LTD.ÞTÝ., 34690711 NOLU HESAB
:61:121030CT0000000000969,73NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT34102273 Fatura?: AT34102273 Fatura No: 4300022926.
:61:121030CT0000000000657,76NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT23164460 Fatura?: AT23164460 Fatura No: 4300022918.
:61:121030CT0000000001005,98NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT37101369 Fatura?: AT37101369 Fatura No: 4300022927.
:61:121030CT0000000020667,07NMSCNONREF//
:86:001?DBS Fatura Tahsilatý - OTOMATÝK. Bayi Referans: AT21106594 Fatura?: AT21106594 Fatura No: 4300022917.
:62F:C121030TRY0000000023300,54'
            ],
            [
                'USD',
                ':20:GAL028USD11/13
:25:0062-00028/9004531
:28C:281/
:60F:C061113USD131738,21
:61:061113DD1625,NCHKNONREF
:86:062 TGAR 8519102 028 90045311  CekProv H
:61:061113DD655,NCHKNONREF
:86:062 TGAR 8519150 028 90045311  CekProv H
:61:061113CD23907,27NTRFNONREF
:86:00028 6695043 0000000000 DAVID KIRSCH FOR
:61:061113CD1489,NCHKNONREF
:86:134 DNZ  0395252 9960 00516210  CekProv H
:61:061113CD615,NTRFNONREF
:86:00075 9099671 4640044209 Havale - Hesapta
:61:061113CD2000,NMSCNONREF
:86:PARA YATIRMA
:61:061113CD5880,01NCHKNONREF
:86:032 TEB  6941697 0064 00233557  CekProv H
:61:061113CD807,96NCHKNONREF
:86:124 ANF  3894478 9260 00306263  CekProv H
:61:061113CD4193,94NTRFNONREF
:86:00120 9093877 8010096437 Havale - Hesapta
:61:061113DD1049,NTRFNONREF
:86:00277 9005506 3230092325 INT-HVL-galata t
:61:061113DD340,NTRFNONREF
:86:00121 9008494 0790020874 INT-HVL-
:61:061113DD247,NTRFNONREF
:86:00121 9098448 1800163747 INT-HVL-GLATA
:61:061113CD3087,53NTRFNONREF
:86:00047 9098377 1900305963 INT-HVL-GALATA T
:61:061113CD214,38NTRFNONREF
:86:00277 6201428 3230092325 INT-HVL-GALATA T
:61:061113CD82,6NTRFNONREF
:86:00277 6201428 0000000000 EKS ECZACIBASI K
:61:061113CD807,6NTRFNONREF
:86:00404 6299519 0070378702 INT-HVL-BLNoSHS0
:62F:C061113USD170907,5
:64:C061113USD170907,5'
            ],
        ];
    }

    /**
     * @dataProvider multipleStatementsProvider
     * @test
     *
     * @param array $currencies
     * @param $input
     */
    public function multipleStatements(array $currencies, $input)
    {
        $engine = @Engine::__getInstance($input);
        $this->assertEquals(
            $currencies,
            array_reduce($engine->parse(), static function (array $carry, Statement $statement) {
                $carry[] = $statement->getCurrency();
                return $carry;
            }, [])
        );

    }

    /**
     *
     */
    public function multipleStatementsProvider()
    {
        return [
          'AbnSample1' => [
              [
                'EUR',
                'EUR',
                'EUR',
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Abn/sample'),
          ],
          'IngSample1' => [
              [
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Ing/sample'),
          ],
          'KnabSample' => [
              [
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Knab/sample'),
          ],
          'SpkSample' => [
              [
                'EUR',
                'EUR',
                'EUR',
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Spk/sample'),
          ],
          'Triodos' => [
              [
                'EUR',
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Triodos/sample'),
          ],
          'RaboSample1' => [
              [
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'USD',
                'USD',
                'USD',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'JPY',
                'JPY',
                'JPY',
                'USD',
                'USD',
                'USD',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
                'EUR',
              ], file_get_contents(__DIR__.'/Engine/Rabo/sample'),
          ],

        ];
    }
}
