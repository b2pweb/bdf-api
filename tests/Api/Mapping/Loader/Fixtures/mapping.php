<?php

use Bdf\Api\Mapping\Builder\MethodBuilder;
use Bdf\Api\Mapping\Builder\ServiceBuilder;
use Bdf\Api\Mapping\Builder\TypeBuilder;

/** @var ServiceBuilder $builder */
$builder
    ->method('method1', function(MethodBuilder $builder) {
        $builder->string('param1');
        $builder->integer('param2');
        $builder->returns('double');
    })

    ->type('Offer', 'Bdf\Api\Mapping\Loader\Fixtures\Offer', function(TypeBuilder $builder) {
        $builder->string('name');
        $builder->string('date');
    })
;
