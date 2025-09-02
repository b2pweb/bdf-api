<?php

namespace Bdf\Fixtures\Service;

final class MyService
{
    public function hello(string $name): Response
    {
        if ($name === 'error') {
            throw new \RuntimeException('invalid name');
        }

        return new Response('Hello ' . $name);
    }
}
