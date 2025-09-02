<?php

namespace Bdf\Fixtures\Service;

class Response
{
    public function __construct(
        public mixed $result = null,
        public bool $success = true,
    ) {}
}
