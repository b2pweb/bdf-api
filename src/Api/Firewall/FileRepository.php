<?php

namespace Bdf\Api\Firewall;

/**
 * 
 */
class FileRepository extends ArrayRepository
{
    /** 
     * @param string $keysFilename
     * @param string $arrayKey
     */
    public function __construct($keysFilename, $arrayKey = null)
    {
        parent::__construct(include $keysFilename, $arrayKey);
    }
}