<?php

declare(strict_types=1);

namespace Tiendanube\Exception;

class RestResourceRequestException extends TiendanubeException
{
    private int $statusCode;

    public function __construct($message, $statusCode)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
