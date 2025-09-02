<?php

namespace Bdf\Api\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * IpRestriction
 *
 * @author  Seb
 * @api
 */
class IpRestriction extends Constraint
{
    public $message = 'This ip is not allowed.';
    public $allowedIps = [];
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): string
    {
        return 'allowedIps';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['allowedIps'];
    }
}
