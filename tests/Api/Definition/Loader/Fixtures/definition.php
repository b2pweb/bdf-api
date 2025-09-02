<?php

use Bdf\Api\Definition\Builder\ComplexTypeDefinitionBuilder;
use Bdf\Api\Definition\Builder\MethodDefinitionBuilder;
use Bdf\Api\Definition\Builder\ServiceDefinitionBuilder;

/** @param ServiceDefinitionBuilder $builder */
$builder
    ->name('Hello')

    ->method('test', function (MethodDefinitionBuilder $builder) {
        $builder->parameter('offer', 'Offer');
        $builder->returns('string');
    })

    ->complexType('Offer', function (ComplexTypeDefinitionBuilder $builder) {
        $builder->string('name');
        $builder->string('city');
        $builder->string('country');
    })
;
