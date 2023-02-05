<?php

declare(strict_types=1);

namespace Tiendanube\Auth;

class AccessTokenResponse
{
    /** @var string */
    protected $storeId;
    /** @var string */
    protected $accessToken;
    /** @var string */
    protected $scope;

    public function __construct(
        string $storeId,
        string $accessToken,
        string $scope
    ) {
        $this->storeId = $storeId;
        $this->accessToken = $accessToken;
        $this->scope = $scope;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}
