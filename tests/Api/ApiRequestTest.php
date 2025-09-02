<?php

namespace Bdf\Api;

use PHPUnit\Framework\TestCase;

/**
 * Class ApiRequestTest
 */
class ApiRequestTest extends TestCase
{
    /**
     *
     */
    public function test_getApiOptions_no_set()
    {
        $request = new ApiRequest();

        $this->assertSame([], $request->getApiOptions());
    }

    /**
     *
     */
    public function test_getApiOptions_with_one_true_option()
    {
        $request = new ApiRequest();
        $request->attributes->set('options', 'myOption');

        $this->assertSame(['myOption' => true], $request->getApiOptions());
    }

    /**
     *
     */
    public function test_getApiOptions_with_one_false_option()
    {
        $request = new ApiRequest();
        $request->attributes->set('options', '-myOption');

        $this->assertSame(['myOption' => false], $request->getApiOptions());
    }

    /**
     *
     */
    public function test_getApiOptions_with_multiple_options()
    {
        $request = new ApiRequest();
        $request->attributes->set('options', '-myOption:trueOption:-other');

        $this->assertSame(['myOption' => false, 'trueOption' => true, 'other' => false], $request->getApiOptions());
    }

    /**
     *
     */
    public function test_getApiOptions_invalid()
    {
        $request = new ApiRequest();
        $request->attributes->set('options', ':');

        $this->assertSame([], $request->getApiOptions());
    }
}
