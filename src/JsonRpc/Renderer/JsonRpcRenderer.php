<?php

namespace Bdf\JsonRpc\Renderer;

use Bdf\Api\Definition\Renderer\RendererInterface;
use Bdf\Api\Definition\ServiceDefinition;

/**
 * @package Bdf\JsonRpc\Renderer
 */
class JsonRpcRenderer implements RendererInterface
{
    /**
     * {@inheritDoc}
     */
    public function render(ServiceDefinition $definition, $uri)
    {
        $smd = new Smd();

        $smd->setTarget($uri);

        foreach ($definition->getMethods() as $method) {
            $smd->addService($method);
        }

        return $smd->toJson();
    }
}
