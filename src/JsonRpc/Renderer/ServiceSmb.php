<?php

namespace Bdf\JsonRpc\Renderer;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\ParameterDefinition;
use Bdf\Api\Definition\TypeDefinition;
use Bdf\JsonRpc\Exception\InvalidArgumentException;

/**
 * Description of Service
 *
 * @author admin
 * 
 * @todo l'attribut 'return' est sérialisé en 'returns'. Formatter les noms des attributs et des méthodes
 */
class ServiceSmb
{
    /**#@+
     * Service metadata
     * @var string
     */
    protected $envelope = Smd::ENV_JSONRPC_2;
    protected $name;
    protected $return;
    protected $target;
    protected $transport = 'POST';
    /**#@-*/

    /**
     * Allowed envelope types
     * @var array
     */
    protected $envelopeTypes = array(
        Smd::ENV_JSONRPC_1,
        Smd::ENV_JSONRPC_2,
    );

    /**
     * Regex for names
     * @var string
     */
    protected $nameRegex = '/^[a-z][a-z0-9.\\\\_]+$/i';

    /**
     * Parameter option types
     * @var array
     */
    protected $paramOptionTypes = array(
        'name'        => 'is_string',
        'optional'    => 'is_bool',
        'default'     => null,
        'description' => 'is_string',
    );

    /**
     * Service params
     * @var array
     */
    protected $params = array();

    /**
     * Mapping of parameter types to JSON-RPC types
     * @var array
     */
    protected $paramMap = array(
        'any'     => 'any',
        'arr'     => 'array',
        'array'   => 'array',
        'assoc'   => 'object',
        'bool'    => 'boolean',
        'boolean' => 'boolean',
        'dbl'     => 'float',
        'double'  => 'float',
        'false'   => 'boolean',
        'float'   => 'float',
        'hash'    => 'object',
        'integer' => 'integer',
        'int'     => 'integer',
        'mixed'   => 'any',
        'nil'     => 'null',
        'null'    => 'null',
        'object'  => 'object',
        'string'  => 'string',
        'str'     => 'string',
        'struct'  => 'object',
        'true'    => 'boolean',
        'void'    => 'null',
    );

    /**
     * Allowed transport types
     * @var array
     */
    protected $transportTypes = array(
        'POST',
    );

    /**
     * Constructor
     *
     * @param string|array|MethodDefinition $definition
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException if no name provided
     */
    public function __construct($definition)
    {
        $this->load($definition);

        if (null == $this->getName()) {
            throw new InvalidArgumentException('SMD service description requires a name; none provided');
        }
    }

    /**
     * Set service name
     *
     * @param  string $name
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        $name = (string) $name;
        
        if (!preg_match($this->nameRegex, $name)) {
            throw new InvalidArgumentException("Invalid name '{$name} provided for service; must follow PHP method naming conventions");
        }
        
        $this->name = $name;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Transport
     *
     * Currently limited to POST
     *
     * @param  string $transport
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->transportTypes)) {
            throw new InvalidArgumentException("Invalid transport '{$transport}'; please select one of (" . implode(', ', $this->transportTypes) . ')');
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
     * Set service target
     *
     * @param  string $target
     */
    public function setTarget($target)
    {
        $this->target = (string) $target;
    }

    /**
     * Get service target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set envelope type
     *
     * @param  string $envelopeType
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    public function setEnvelope($envelopeType)
    {
        if (!in_array($envelopeType, $this->envelopeTypes)) {
            throw new InvalidArgumentException("Invalid envelope type '{$envelopeType}'; please specify one of (" . implode(', ', $this->envelopeTypes) . ')');
        }

        $this->envelope = $envelopeType;
    }

    /**
     * Get envelope type
     *
     * @return string
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Add a parameter to the service
     *
     * @param ParameterDefinition $definition
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    public function addParam(ParameterDefinition $definition)
    {
        $this->params[$definition->getPosition()] = array(
            'type'        => $this->_validateParamType($definition->getType()),
            'name'        => $definition->getName(),
            'optional'    => $definition->isOptional(),
            'default'     => $definition->getDefaultValue(),
            'description' => $definition->getDescription()
        );
    }

    /**
     * Add params
     *
     * Each param should be an array, and should include the key 'type'.
     *
     * @param ParameterDefinition[] $params
     */
    public function addParams(array $params)
    {
        foreach ($params as $param) {
            $this->addParam($param);
        }
    }

    /**
     * Overwrite all parameters
     *
     * @param ParameterDefinition[] $params
     */
    public function setParams(array $params)
    {
        $this->params = array();
        
        $this->addParams($params);
    }

    /**
     * Get all parameters
     *
     * Returns all params in specified order.
     *
     * @return ParameterDefinition[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set return type
     *
     * @param TypeDefinition $definition
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    public function setReturn(TypeDefinition $definition)
    {
        $this->return = $this->_validateParamType($definition, true);
    }

    /**
     * Get return type
     *
     * @return string|array
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Cast service description to array
     *
     * @return array
     */
    public function toArray()
    {
        $paramInfo = array(
            'envelope'   => $this->getEnvelope(),
            'transport'  => $this->getTransport(),
            'parameters' => $this->getParams(),
            'returns'    => $this->getReturn()
        );

        if ($this->getTarget()) {
            $paramInfo['target'] = $this->getTarget();
        }

        return $paramInfo;
    }

    /**
     * Return JSON encoding of service
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode(
            array($this->getName() => $this->toArray()),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param string|array|MethodDefinition $definition
     */
    protected function load($definition)
    {
        if ($definition instanceof MethodDefinition) {
            $this->setName($definition->getName());
            $this->setParams($definition->getParameters());

            if ($definition->getReturn()) {
                $this->setReturn($definition->getReturn());
            }
        } elseif (is_array($definition)) {
            if (isset($definition['name'])) {
                $this->setName($definition['name']);
            }

            if (isset($definition['params'])) {
                $this->setParams($definition['params']);
            }

            if (isset($definition['return'])) {
                $this->setReturn($definition['return']);
            }
        } else {
            $this->setName($definition);
        }
    }
    
    /**
     * Validate parameter type
     *
     * @param TypeDefinition $definition
     * @param boolean        $isReturn
     * 
     * @return string
     * 
     * @throws Bdf\JsonRpc\Exception\InvalidArgumentException
     */
    protected function _validateParamType(TypeDefinition $definition, $isReturn = false)
    {
        if ($definition instanceof ArrayTypeDefinition) {
            $type = 'array';
        } elseif (!isset($this->paramMap[$definition->getName()])) {
            $type = 'object';
        } else {
            $type = $definition->getName();
        }

        $paramType = $this->paramMap[$type];

        if (!$isReturn && ('null' == $paramType)) {
            throw new InvalidArgumentException("Invalid param type provided ('{$type}')");
        }

        return $paramType;
    }
}