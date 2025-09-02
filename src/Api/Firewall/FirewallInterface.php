<?php

namespace Bdf\Api\Firewall;

/**
 * 
 */
interface FirewallInterface
{
    /**
     * @param string $ip
     *  
     * @return bool
     */
    public function isAllowed($ip);
}