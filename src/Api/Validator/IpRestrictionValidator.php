<?php

namespace Bdf\Api\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * IpRestrictionValidator
 * 
 * This class is used to validate ip from a list of ips.
 * The class manage many ip format like:
 *  * classic ip: 192.168.0.2
 *  * wilcard   : 192.168.0.*
 *  * mask      : 192.168.0.1/255.255.0.0
 *  * section   : 192.168.0.1-192.168.0.10
 *  * CIDR      : 192.168.0.1/23
 * 
 * @author Seb
 */
class IpRestrictionValidator extends ConstraintValidator
{
    const IP_TYPE_SINGLE = 1;
    const IP_TYPE_WILDCARD = 2;
    const IP_TYPE_MASK = 3;
    const IP_TYPE_CIDR = 4;
    const IP_TYPE_SECTION = 5;
    
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IpRestriction) {
            throw new UnexpectedTypeException($constraint, IpRestriction::class);
        }

        if (null === $value || '' === $value) {
            return;
        }
        
        if (in_array($value, $constraint->allowedIps)) {
            return;
        }
        
        foreach ($constraint->allowedIps as $allowedIp) {
            switch($this->getIpType($allowedIp)) {
                case self::IP_TYPE_WILDCARD:
                    $isValid = $this->checkWildcard($allowedIp, $value);
                    break;
                
                case self::IP_TYPE_MASK:
                    $isValid = $this->checkMask($allowedIp, $value);
                    break;
                
                case self::IP_TYPE_CIDR:
                    $isValid = $this->checkCIDR($allowedIp, $value);
                    break;
                
                case self::IP_TYPE_SECTION:
                    $isValid = $this->checkSection($allowedIp, $value);
                    break;
                
                case self::IP_TYPE_SINGLE:
                    $isValid = $this->checkSingle($allowedIp, $value);
                    break;
                
                default:
                    $isValid = false;
                    break;
            }
            
            if ($isValid) {
                return;
            }
        }

        $this->context->addViolation($constraint->message, [
            '{{ value }}' => $this->formatValue($value),
        ]);
    }

    /**
     * Get the type of format from the given allowed ip
     * 
     * @param string $ip
     * @return int
     */
    protected function getIpType($ip)
    {
        if (strpos($ip, '*') !== false) {
            return self::IP_TYPE_WILDCARD;
        }

        if (strpos($ip, '/')) {
            $tmp = explode('/', $ip);
            
            if (strpos($tmp[1], '.')) {
                return self::IP_TYPE_MASK;
            } else {
                return self::IP_TYPE_CIDR;
            }
        }

        if (strpos($ip, '-')) {
            return self::IP_TYPE_SECTION;
        }

        if (ip2long($ip)) {
            return self::IP_TYPE_SINGLE;
        }

        return false;
    }

    /**
     * Validate the format 127.0.0.1
     * 
     * @param string $allowedIp
     * @param string $ip
     * 
     * @return bool
     */
    private function checkSingle($allowedIp, $ip)
    {
        return ip2long($allowedIp) == ip2long($ip);
    }

    /**
     * Validate the format 127.0.0.*
     * 
     * @param string $allowedIp
     * @param string $ip
     * 
     * @return bool
     */
    private function checkWildcard($allowedIp, $ip)
    {
        $allowedIpParts = explode('.', $allowedIp);
        $ipParts        = explode('.', $ip);
        
        for ($i = 0; $i < count($allowedIpParts); $i++) {
            if ($allowedIpParts[$i] !== $ipParts[$i] && $allowedIpParts[$i] !== '*') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate the format 127.0.0.1/255.255.0.0
     * 
     * @param string $allowedIp
     * @param string $ip
     * 
     * @return bool
     */
    private function checkMask($allowedIp, $ip)
    {
        list($allowedIp, $netmask) = explode('/', $allowedIp);
        
        $allowedIp = ip2long($allowedIp);
        $netmask   = ip2long($netmask);
        $ip        = ip2long($ip);
        
        return ($ip & $netmask) == ($allowedIp & $netmask);
    }

    /**
     * Validate the format 127.0.0.1-125.0.0.9
     * 
     * @param string $allowedIp
     * @param string $ip
     * 
     * @return bool
     */
    private function checkSection($allowedIp, $ip)
    {
        list($begin, $end) = explode('-', $allowedIp);
        
        $begin = ip2long($begin);
        $end   = ip2long($end);
        $ip    = ip2long($ip);
        
        return $ip >= $begin && $ip <= $end;
    }

    /**
     * Validate the format 127.0.0.1/23
     * 
     * @param string $allowedIp
     * @param string $ip
     * 
     * @return bool
     */
    private function checkCIDR($allowedIp, $ip)
    {
        list($subnet, $bits) = explode('/', $allowedIp);
        
        $ip     = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask   = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        
        return ($ip & $mask) == $subnet;
    }
}
