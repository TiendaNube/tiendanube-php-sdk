<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Tiendanube\Context;
use Tiendanube\Exception\MissingArgumentException;

class Rest extends Http
{
    /** @var string */
    private $accessToken;

    /**
     * Rest Client constructor.
     *
     * @param string      $storeId
     * @param string|null $accessToken
     *
     * @throws \Tiendanube\Exception\MissingArgumentException
     */
    public function __construct(string $storeId, ?string $accessToken = null)
    {
        parent::__construct($storeId);
        $this->accessToken = $accessToken;

        if (!$this->accessToken) {
            throw new MissingArgumentException('Missing access token when creating REST client');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function request(
        string $path,
        string $method,
        $body = null,
        array $headers = [],
        array $query = [],
        ?int $tries = null,
        string $dataType = self::DATA_TYPE_JSON
    ): RestResponse {
        $headers[HttpHeaders::AUTHENTICATION] = "bearer {$this->accessToken}";

        $response = parent::request($path, $method, $body, $headers, $query, $tries, $dataType);

        return new RestResponse(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase(),
            $this->getPageInfo($response)
        );
    }

    /**
     * @param \Tiendanube\Clients\HttpResponse $response
     *
     * @return \Tiendanube\Clients\PageInfo|null
     */
    private function getPageInfo(HttpResponse $response): ?PageInfo
    {
        $pageInfo = null;
        if ($response->hasHeader(HttpHeaders::PAGINATION_HEADER)) {
            $pageInfo = PageInfo::fromLinkHeader($response->getHeaderLine(HttpHeaders::PAGINATION_HEADER));
        }
        return $pageInfo;
    }
}
