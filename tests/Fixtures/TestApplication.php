<?php

namespace Bdf\Fixtures;

use Bdf\Api\Application;
use Bdf\Fixtures\Service\MyServiceProvider;

class TestApplication extends Application
{
    public function __construct()
    {
        parent::__construct(new TestKernel('test', true));

        $this->boot();
        $this->register(new MyServiceProvider());
    }
}
