<?php

namespace Bdf\Api\Mapping\Constraints;

use PHPUnit\Framework\TestCase;

/**
 * Class ClosureConstraintTest
 *
 * @package Bdf\Api\Mapping\Constraints
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Constraints
 * @group Bdf_Api_Mapping_Constraints_ClosureConstraint
 *
 * @coversDefaultClass Bdf\Api\Mapping\Constraints\ClosureConstraint
 */
class ClosureConstraintTest extends TestCase
{
    /**
     *
     */
    public function test_validate()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('erreur');

        $constraint = new ClosureConstraint(function($value, $context) {
            throw new \Exception('erreur');
        });

        $constraint->validate('test', $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface'));
    }
}
