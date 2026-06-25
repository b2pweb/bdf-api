<?php

namespace Bdf\Api\Validator;

use Symfony\Component\Validator\Constraint;

use function array_key_exists;

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

    public function __construct(array $allowedIps, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct(null, $groups, $payload);

        if (array_key_exists('allowedIps', $allowedIps)) {
            $this->allowedIps = $allowedIps['allowedIps'];
            $this->message = $allowedIps['message'] ?? $this->message;
        } else {
            $this->allowedIps = $allowedIps;
            $this->message = $message ?? $this->message;
        }
    }
}
