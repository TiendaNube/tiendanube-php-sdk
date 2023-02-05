<?php

declare(strict_types=1);

namespace Tiendanube;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Tiendanube\Clients\HttpClientFactory;
use Tiendanube\Exception\HttpRequestException;
use Tiendanube\Clients\MockRequest;

define('RUNNING_TIENDANUBE_TESTS', 1);

class BaseTestCase extends TestCase
{
    /** @var string */
    protected $domain = 'api.tiendanube.com';
    /** @var string */
    protected $storeId = '1';
    /** @var string */
    protected $version;

    public function setUp(): void
    {
        // Initialize Context before each test
        Context::initialize(
            'my_api_key',
            'my_api_secret_key',
            'www.my-super-app.com',
            'My Super App (support@my-super-app.com)',
            ['write_products', 'write_orders'],
        );
        Context::$initialNetworkRetryDelay = 0;
        Context::$maxNetworkRetryDelay = 0;
        $this->version = Context::VERSION;

        // Make sure we always mock the transport layer so we don't accidentally make real requests
        $this->mockTransportRequests([]);
    }

    /**
     * Builds a mock HTTP response that can optionally also validate the parameters of the cURL call.
     *
     * @param int|null          $statusCode The HTTP status code to return
     * @param string|array|null $body       The body of the HTTP response
     * @param array             $headers    The headers expected in the response
     * @param string|null       $error      The cURL error message to return
     *
     * @return array
     */
    protected function buildMockHttpResponse(
        int $statusCode = null,
        $body = null,
        array $headers = [],
        string $error = null
    ): array {
        if ($body && !is_string($body)) {
            $body = json_encode($body);
        }

        return [
            'statusCode' => $statusCode,
            'body' => $body,
            'headers' => $headers,
            'error' => $error,
        ];
    }

    /**
     * Sets up a transport layer mock that expects the given requests to happen.
     *
     * @param MockRequest[] $requests
     */
    public function mockTransportRequests(array $requests): void
    {
        $requestMatchers = [];
        $newResponses = [];
        foreach ($requests as $request) {
            $matcher = new HttpRequestMatcher(
                $request->url,
                $request->method,
                "/$request->userAgent/",
                $request->headers,
                $request->body ?? "",
                true,
                $request->identicalBody
            );

            $requestMatchers[] = [$matcher];

            $newResponses[] = $request->error ? 'TEST EXCEPTION' : new Response(
                $request->response['statusCode'],
                $request->response['headers'],
                $request->response['body'],
            );
        }

        $client = $this->createMock(ClientInterface::class);

        $i = 0;
        $client->expects($this->exactly(count($requestMatchers)))
            ->method('sendRequest')
            ->withConsecutive(...$requestMatchers)
            ->willReturnCallback(
                function () use (&$i, $newResponses) {
                    $response = $newResponses[$i++];
                    if ($response === 'TEST EXCEPTION') {
                        throw new HttpRequestException();
                    } else {
                        return $response;
                    }
                }
            );

        $factory = $this->createMock(HttpClientFactory::class);

        $factory->expects($this->any())
            ->method('client')
            ->willReturn($client);

        Context::$httpClientFactory = $factory;
    }
}
