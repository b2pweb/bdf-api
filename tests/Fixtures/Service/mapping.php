<?php

use Bdf\Api\Mapping\Builder\MethodBuilder;
use Bdf\Api\Mapping\Builder\ServiceBuilder;
use Bdf\Api\Mapping\Builder\TypeBuilder;
use Bdf\Fixtures\Service\Response;

/** @var ServiceBuilder $builder */
$builder
    ->method('hello', function (MethodBuilder $builder) {
        $builder->string('name');
        $builder->returns('StringResponse');
    })
    ->type('StringResponse', Response::class, function (TypeBuilder $builder) {
        $builder->string('result');
        $builder->boolean('success');
    })
;
