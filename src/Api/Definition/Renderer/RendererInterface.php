<?php

namespace Bdf\Api\Definition\Renderer;

use Bdf\Api\Definition\ServiceDefinition;

/**
 * Interface RendererInterface
 *
 * @package Bdf\Api\Definition\Renderer
 */
interface RendererInterface
{
    /**
     * @param ServiceDefinition $definition
     * @param string            $uri
     *
     * @return string
     */
    public function render(ServiceDefinition $definition, $uri);
}
