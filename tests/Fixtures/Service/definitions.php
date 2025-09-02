<?php

/** @var \Bdf\Api\Definition\Builder\ServiceDefinitionBuilder $builder */

use Bdf\Api\Definition\Builder\ComplexTypeDefinitionBuilder;
use Bdf\Api\Definition\Builder\MethodDefinitionBuilder;

$builder
    ->name('MyService')
    ->method('hello', function (MethodDefinitionBuilder $builder) {
        $builder->string('name', 'John');
        $builder->returns('StringResponse');
    })
    ->complexType('StringResponse', function (ComplexTypeDefinitionBuilder $builder) {
        $builder->string('result')->optional()->nillable();
        $builder->boolean('success');
    })
;
