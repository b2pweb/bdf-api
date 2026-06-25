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

    public function __construct(array $allowedIps, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct(null, $groups, $payload);

        if (array_key_exists('allowedIps', $allowedIps)) {
            $this->allowedIps = $allowedIps['allowedIps'];
        } else {
            $this->allowedIps = $allowedIps;
        }
    }

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
