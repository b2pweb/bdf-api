<?php

namespace Bdf\JsonRpc;

/**
 * @todo le server ne devrait pas repondre aux notifications en 1.0
 */
class JsonRpcServer extends \Bdf\Api\Server\ApiServer
{
    /**#@+
     * Version Constants
     */
    const VERSION_1 = '1.0';
    const VERSION_2 = '2.0';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected $mappingCodes = array(
        400 => -32600,
        403 => -32603,
        404 => -32601,
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->setRequest(new Server\Request());
        $this->setResponse(new Server\Response());
    }

    /**
     * {@inheritdoc}
     *
     * @return Server\Error
     */
    protected function instanciateFault($fault = null, $code = null, $data = null)
    {
        if (empty($code)) {
            $code = -32000;
        }

        return new Server\Error($fault, $code, $data);
    }
}
