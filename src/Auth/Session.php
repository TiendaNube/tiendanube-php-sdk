<?php

declare(strict_types=1);

namespace Tiendanube\Auth;

use DateTime;
use Tiendanube\Context;

/**
 * Stores App information from logged in merchants so they can make authenticated requests to the API.
 */
class Session
{
    /** @var string */
    private $storeId;
    /** @var string */
    private $scope;
    /** @var string */
    private $accessToken;

    public function __construct(
        string $storeId,
        string $accessToken,
        string $scope = null
    ) {
        $this->storeId = $storeId;
        $this->accessToken = $accessToken;
        $this->scope = $scope;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * Checks whether this session has all the necessary settings to make requests to Tiendanube/Nuvemshop.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return (
            Context::$scopes->equals($this->scope) &&
            $this->accessToken
        );
    }
}
