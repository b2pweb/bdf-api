<?php

namespace Bdf\JsonRpc\Renderer;

use Bdf\Api\Definition\MethodDefinition;
use Bdf\JsonRpc\Exception;

/**
 * @package Bdf\JsonRpc\Renderer
 */
class Smd
{
    const ENV_JSONRPC_1 = 'JSON-RPC-1.0';
    const ENV_JSONRPC_2 = 'JSON-RPC-2.0';
    const SMD_VERSION   = '2.0';

    /**
     * Content type
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * Content type regex
     * @var string
     */
    protected $contentTypeRegex = '#[a-z]+/[a-z][a-z-]+#i';

    /**
     * Service description
     * @var string
     */
    protected $description;

    /**
     * Generate Dojo-compatible SMD
     * @var bool
     */
    protected $dojoCompatible;

    /**
     * Current envelope
     * @var string
     */
    protected $envelope = self::ENV_JSONRPC_2;

    /**
     * Allowed envelope types
     * @var array
     */
    protected $envelopeTypes = array(
        self::ENV_JSONRPC_1,
        self::ENV_JSONRPC_2,
    );

    /**
     * Service id
     * @var string
     */
    protected $id;

    /**
     * Services offered
     * @var array
     */
    protected $services = array();

    /**
     * Service target
     * @var string
     */
    protected $target;

    /**
     * Global transport
     * @var string
     */
    protected $transport = 'POST';

    /**
     * Allowed transport types
     * @var array
     */
    protected $transportTypes = array('POST');

    /**
     * Set object state via options
     *
     * @param  array $options
     * @return Smd
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set transport
     *
     * @param  string $transport
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->transportTypes)) {
            throw new Exception\InvalidArgumentException("Invalid transport '{$transport}' specified");
        }
        
        $this->transport = $transport;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set envelope
     *
     * @param  string $envelopeType
     */
    public function setEnvelope($envelopeType)
    {
        if (!in_array($envelopeType, $this->envelopeTypes)) {
            throw new Exception\InvalidArgumentException("Invalid envelope type '{$envelopeType}'");
        }
        
        $this->envelope = $envelopeType;
    }

    /**
     * Retrieve envelope
     *
     * @return string
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    // Content-Type of response; default to application/json
    /**
     * Set content type
     *
     * @param  string $type
     */
    public function setContentType($type)
    {
        if (!preg_match($this->contentTypeRegex, $type)) {
            throw new Exception\InvalidArgumentException("Invalid content type '{$type}' specified");
        }
        
        $this->contentType = $type;
    }

    /**
     * Retrieve content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set service target
     *
     * @param  string $target
     */
    public function setTarget($target)
    {
        $this->target = (string) $target;
    }

    /**
     * Retrieve service target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set service ID
     *
     * @param  string $Id
     */
    public function setId($id)
    {
        $this->id = (string) $id;
    }

    /**
     * Get service id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set service description
     *
     * @param  string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * Get service description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @see Bdf\Api\Server\ServiceMapInterface
     */
    public function setServiceName($name)
    {
//        $this->setName($name);
    }

    /**
     * @see Bdf\Api\Server\ServiceMapInterface
     */
    public function setUri($uri)
    {
        $this->setTarget($uri);
    }
    
    /**
     * Indicate whether or not to generate Dojo-compatible SMD
     *
     * @param  bool $flag
     */
    public function setDojoCompatible($flag)
    {
        $this->dojoCompatible = (bool) $flag;
    }

    /**
     * Is this a Dojo compatible SMD?
     *
     * @return bool
     */
    public function isDojoCompatible()
    {
        return $this->dojoCompatible;
    }

    /**
     * Add Service
     *
     * @param array|ServiceSmb|MethodDefinition $service
     */
    public function addService($service)
    {
        if ($service instanceof ServiceSmb) {
            $name = $service->getName();
        } elseif ($service instanceof MethodDefinition || is_array($service)) {
            $service = new ServiceSmb($service);
            $name = $service->getName();
        } else {
            throw new Exception\InvalidArgumentException('Invalid service passed to addService()');
        }

        if (array_key_exists($name, $this->services)) {
            throw new Exception\RuntimeException('Attempt to register a service already registered detected');
        }
        
        $this->services[$name] = $service;
    }

    /**
     * Add many services
     *
     * @param  array $services
     */
    public function addServices(array $services)
    {
        foreach ($services as $service) {
            $this->addService($service);
        }
    }

    /**
     * Overwrite existing services with new ones
     *
     * @param array $services
     */
    public function setServices(array $services)
    {
        $this->services = array();
        
        $this->addServices($services);
    }

    /**
     * Get service object
     *
     * @param  string $name
     * @return boolean|ServiceSmb
     */
    public function getService($name)
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        }
        
        return false;
    }

    /**
     * Return services
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Remove service
     *
     * @param  string $name
     * @return boolean
     */
    public function removeService($name)
    {
        if (array_key_exists($name, $this->services)) {
            unset($this->services[$name]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->isDojoCompatible()) {
            return $this->toDojoArray();
        }

        $service = array(
            'transport'     => $this->getTransport(),
            'envelope'      => $this->getEnvelope(),
            'contentType'   => $this->getContentType(),
            'SMDVersion'    => self::SMD_VERSION
        );
        
        if (null !== ($target = $this->getTarget())) {
            $service['target'] = $target;
        }
        if (null !== ($id = $this->getId())) {
            $service['id'] = $id;
        }

        $services = $this->getServices();
        if (!empty($services)) {
            $service['services'] = array();
            foreach ($services as $name => $svc) {
                $svc->setEnvelope($service['envelope']);
                $service['services'][$name] = $svc->toArray();
            }
        }

        return $service;
    }

    /**
     * Export to DOJO-compatible SMD array
     *
     * @return array
     */
    public function toDojoArray()
    {
        $service = array(
            'SMDVersion'    => '.1',
            'serviceType'   => 'JSON-RPC'
        );

        $target   = $this->getTarget();
        $services = $this->getServices();
        
        if (!empty($services)) {
            $service['methods'] = array();
            
            foreach ($services as $name => $svc) {
                $method = array(
                    'name'       => $name,
                    'serviceURL' => $target,
                );
                
                $params = array();
                
                foreach ($svc->getParams() as $param) {
                    $paramName = array_key_exists('name', $param) ? $param['name'] : $param['type'];
                    $params[] = array(
                        'name' => $paramName,
                        'type' => $param['type'],
                    );
                }
                
                if (!empty($params)) {
                    $method['parameters'] = $params;
                }
                
                $service['methods'][] = $method;
            }
        }

        return $service;
    }

    /**
     * Cast to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Cast to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
