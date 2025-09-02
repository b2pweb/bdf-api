<?php

namespace Bdf\Api\Firewall;

use Bdf\Api\Validator\IpRestriction;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * 
 */
class ArrayRepository implements FirewallInterface
{
    protected ValidatorInterface $validator;
    protected IpRestriction $constraint;
    
    /**
     * @param array  $ips
     * @param array-key|null $arrayKey
     */
    public function __construct(array $ips = array(), string|int|null $arrayKey = null)
    {
        if ($arrayKey !== null && isset($ips[$arrayKey])) {
            $ips = $ips[$arrayKey];
        }
        
        $this->constraint = new IpRestriction(['allowedIps' => $ips]);
        $this->validator  = (new ValidatorBuilder())->getValidator();
    }
    
    /**
     * @see FirewallInterface::isAllowed
     */
    public function isAllowed($ip): bool
    {
        $errors = $this->validator->validate($ip, $this->constraint);
        
        return count($errors) === 0;
    }
}
