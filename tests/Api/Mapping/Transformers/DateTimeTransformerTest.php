<?php

namespace Bdf\Api\Mapping\Transformers;

use Bdf\Api\Mapping\Context;
use DI\Container;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Class DateTimeTransformerTest
 *
 * @package Bdf\Api\Mapping\Transformers
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Transformers
 * @group Bdf_Api_Mapping_Transformers_DateTimeTransformer
 *
 * @coversDefaultClass Bdf\Api\Mapping\Transformers\DateTimeTransformer
 */
class DateTimeTransformerTest extends TestCase
{
    /**
     * @dataProvider doTransformProvider
     *
     * @param array $options
     * @param string $input
     * @param DateTime $output
     */
    public function test_doTransform(array $options, $input, $output)
    {
        $this->assertEquals(
            $output, (new DateTimeTransformer($options))->doTransform($input, new Context(new Container()))
        );
    }

    /**
     * @return array
     */
    public function doTransformProvider()
    {
        return [
            [
                [],
                '2016/02/23 20:15',
                DateTime::createFromFormat('Y/m/d H:i', '2016/02/23 20:15')
            ],
            [
                ['format' => 'd-m-Y H:i'],
                '23-02-2016 10:20',
                DateTime::createFromFormat('d-m-Y H:i', '23-02-2016 10:20')
            ],
            [
                ['timezone' => 'Asia/Tokyo'],
                '2016/02/23 20:15',
                DateTime::createFromFormat('Y/m/d H:i', '2016/02/23 20:15', new \DateTimeZone('Asia/Tokyo'))
            ]
        ];
    }

    /**
     * @dataProvider doTransformExceptionProvider
     *
     * @param array $options
     * @param string $expectedExceptionMessage
     */
    public function test_doTransform_should_throws_exception_on_error(array $options, $expectedExceptionMessage)
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage($expectedExceptionMessage);

        (new DateTimeTransformer($options))->doTransform('2016/02/23', new Context(new Container()));
    }

    /**
     * @return array
     */
    public function doTransformExceptionProvider()
    {
        return [
            [
                [],
                'DateTime value does not match the expected format : Y/m/d H:i'
            ],
            [
                ['errorMessage' => 'Mon message d\'erreur'],
                'Mon message d\'erreur'
            ]
        ];
    }

    /**
     * @dataProvider undoTransformProvider
     *
     * @param array $options
     * @param DateTime $input
     * @param string $output
     */
    public function test_undoTransform(array $options, $input, $output)
    {
        $this->assertEquals(
            $output, (new DateTimeTransformer($options))->undoTransform($input, new Context(new Container()))
        );
    }

    /**
     * @return array
     */
    public function undoTransformProvider()
    {
        return [
            [
                [],
                DateTime::createFromFormat('Y/m/d H:i', '2016/02/23 20:15'),
                '2016/02/23 20:15'
            ],
            [
                ['format' => 'd-m-Y H:i'],
                DateTime::createFromFormat('d-m-Y H:i', '23-02-2016 10:20'),
                '23-02-2016 10:20'
            ]
        ];
    }
}
