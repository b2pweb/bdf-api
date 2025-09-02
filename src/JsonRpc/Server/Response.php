<?php

namespace Bdf\JsonRpc\Server;

use Bdf\JsonRpc\JsonRpcServer;
use Bdf\Api\Server\Response\ResponseInterface;
use Bdf\Api\Server\Request\RequestInterface;

/**
 * 
 */
class Response implements ResponseInterface
{
    /**
     * Response error
     * @var Error
     */
    protected $error;

    /**
     * Request ID
     * @var mixed
     */
    protected $id;

    /**
     * Result
     * @var mixed
     */
    protected $result;

    /**
     * JSON-RPC version
     * @var string
     */
    protected $version;
    
    /**
     * @see Bdf\Api\Server\Response\ResponseInterface::prepare
     */
    public function prepare(RequestInterface $request)
    {
        if (null !== ($id = $request->getId())) {
            $this->setId($id);
        }
        
        if (null !== ($version = $request->getVersion())) {
            $this->setVersion($version);
        }
    }
    
    /**
     * @see Bdf\Api\Server\Response\ResponseInterface::setResult
     */
    public function setResult($value)
    {
        $this->result = $value;
    }
    
    /**
     * @see Bdf\Api\Server\Response\ResponseInterface::__toString
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @see Bdf\Api\Server\Response\ResponseInterface::getResult
     */
    public function getResult()
    {
        return $this->result;
    }

    // RPC error, if response results in fault
    /**
     * Set result error
     *
     * @param Error $error
     */
    public function setError(\Exception $error)
    {
        if ($error instanceof Error) {
            $this->error = $error;
        } else {
            $this->error = new Error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Get response error
     *
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Is the response an error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->getError() instanceof Error;
    }

    /**
     * Set request ID
     *
     * @param  mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get request ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     */
    public function setVersion($version)
    {
        $version = is_array($version) ? implode(' ', $version) : (string)$version;
        
        if ($version == JsonRpcServer::VERSION_2) {
            $this->version = JsonRpcServer::VERSION_2;
        } else {
            $this->version = null;
        }
    }

    /**
     * Retrieve JSON-RPC version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Send headers
     *
     * If headers are already sent, do nothing. If null ID, send HTTP 204
     * header. Otherwise, send content type header based on content type of
     * service map.
     * 
     * @return array
     */
    public function getHeader()
    {
        if (!$this->isError() && (null === $this->getId())) {
            return [
                'HTTP/1.1 204 No Content',
//                'Access-Control-Allow-Origin'  => '*',
//                'Access-Control-Allow-Headers' => 'Origin,Content-Type,Accept,Authorization',
            ];
        }
        
        return [
            'Content-Type' => 'application/json',
//            'Access-Control-Allow-Origin'  => '*',
//            'Access-Control-Allow-Headers' => 'Origin,Content-Type,Accept,Authorization',
        ];
    }
    
    /**
     * @see Bdf\Api\Server\Response\ResponseInterface::send
     */
    public function send()
    {
        foreach ($this->getHeader() as $type => $value) {
            if (is_string($type)) {
                header($type . ':' . $value);
            } else {
                header($value);
            }
        }
        
        echo $this->toJson();
    }
    
    /**
     * Emit JSON
     *
     * Send appropriate HTTP headers. If no Id, then return an empty string.
     *
     * @return string
     */
    public function toJson()
    {
        if (!$this->isError() && null === $this->getId()) {
            return '';
        }
        
        $version = $this->getVersion();
        
        if ($this->isError()) {
            $response = array(
                'error'  => $this->getError()->toArray(),
                'id'     => $this->getId(),
            );
            
            if ($version != JsonRpcServer::VERSION_2) {
                $response['result'] = null;
            }
        } else {
            $response = array(
                'result' => $this->getResult(),
                'id'     => $this->getId(),
            );
            
            if ($version != JsonRpcServer::VERSION_2) {
                $response['error'] = null;
            }
        }

        if (JsonRpcServer::VERSION_2 == $version) {
            $response['jsonrpc'] = $version;
        }

        return json_encode($response);
    }
}
