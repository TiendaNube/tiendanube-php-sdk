<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Tiendanube\Clients\Rest;
use Tiendanube\Context;
use Tiendanube\BaseTestCase;
use Tiendanube\HttpResponseMatcher;

class RestTest extends BaseTestCase
{
    use PaginationTestHelper;

    /** @var array */
    private $successResponse = [
        'products' => [
            'title' => 'Test Product',
            'amount' => 1,
        ],
    ];

    public function testFailsToInstantiateWithoutAccessTokenForNonPrivateApps()
    {
        $this->expectException(\Tiendanube\Exception\MissingArgumentException::class);

        new Rest($this->storeId);
    }

    public function testCanMakeGetRequest()
    {
        $headers = ['X-Test-Header' => 'test_value'];

        $client = new Rest($this->storeId, 'dummy-token');

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products",
                'GET',
                "Tiendanube Admin API Library for PHP v$this->version",
                ['X-Test-Header: test_value', 'Authentication: bearer dummy-token'],
                null,
                null,
                false,
            ),
        ]);

        $response = $client->get('products', $headers);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testAllowsFullPaths()
    {
        $headers = ['X-Test-Header' => 'test_value'];

        $client = new Rest($this->storeId, 'dummy-token');

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/{$this->storeId}/custom_path",
                'GET',
                "Tiendanube Admin API Library for PHP v$this->version",
                ['X-Test-Header: test_value', 'Authentication: bearer dummy-token'],
                null,
                null,
                false,
            ),
        ]);

        $response = $client->get("custom_path", $headers);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanMakeGetRequestWithPathInQuery()
    {
        $client = new Rest($this->storeId, 'dummy-token');

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products?path=some_path",
                'GET',
                "Tiendanube Admin API Library for PHP v$this->version",
                ['Authentication: bearer dummy-token'],
                null,
                null,
                false,
            ),
        ]);

        $response = $client->get('products', [], ["path" => "some_path"]);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanMakePostRequestWithJsonData()
    {
        $client = new Rest($this->storeId, 'dummy-token');

        $postData = [
            "title" => 'Test product',
            "amount" => 10,
        ];

        $body = json_encode($postData);
        $bodyLength = strlen($body);

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products",
                'POST',
                "Tiendanube Admin API Library for PHP v$this->version",
                [
                    'Content-Type: application/json',
                    "Content-Length: $bodyLength",
                    'Authentication: bearer dummy-token',
                ],
                $body,
                null,
                false,
            ),
        ]);

        $response = $client->post('products', $postData);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanMakePostRequestWithJsonDataAndPathInQuery()
    {
        $client = new Rest($this->storeId, 'dummy-token');

        $postData = [
            "title" => 'Test product',
            "amount" => 10,
        ];

        $body = json_encode($postData);
        $bodyLength = strlen($body);

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products?path=some_path",
                'POST',
                "Tiendanube Admin API Library for PHP v$this->version",
                [
                    'Content-Type: application/json',
                    "Content-Length: $bodyLength",
                    'Authentication: bearer dummy-token',
                ],
                $body,
                null,
                false,
            ),
        ]);

        $response = $client->post('products', $postData, [], ["path" => "some_path"]);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanMakePutRequestWithJsonData()
    {
        $client = new Rest($this->storeId, 'dummy-token');

        $postData = [
            "title" => 'Test product',
            "amount" => 10,
        ];

        $body = json_encode($postData);
        $bodyLength = strlen($body);

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products/123?path=some_path",
                'PUT',
                "Tiendanube Admin API Library for PHP v$this->version",
                [
                    'Content-Type: application/json',
                    "Content-Length: $bodyLength",
                    'Authentication: bearer dummy-token',
                ],
                $body,
                null,
                false,
            ),
        ]);

        $response = $client->put('products/123', $postData, [], ["path" => "some_path"]);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanMakeDeleteRequest()
    {
        $headers = ['X-Test-Header' => 'test_value'];

        $client = new Rest($this->storeId, 'dummy-token');

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $this->successResponse),
                "https://$this->domain/" . Context::$apiVersion . "/1/products?path=some_path",
                'DELETE',
                "Tiendanube Admin API Library for PHP v$this->version",
                ['X-Test-Header: test_value', 'Authentication: bearer dummy-token'],
                null,
                null,
                false,
            ),
        ]);

        $response = $client->delete('products', $headers, ["path" => "some_path"]);
        $this->assertThat($response, new HttpResponseMatcher(200, [], $this->successResponse));
    }

    public function testCanRequestNextAndPreviousPagesUntilTheyRunOut()
    {
        $firstPageLinkHeader = $this->getProductsLinkHeader(null, 2);
        $middlePageLinkHeader = $this->getProductsLinkHeader(1, 3);
        $lastPageLinkHeader = $this->getProductsLinkHeader(2, null);

        $this->mockTransportRequests(
            [
                new MockRequest(
                    $this->buildMockHttpResponse(200, $this->successResponse, ['Link' => $firstPageLinkHeader]),
                    $this->getAdminApiUrl($this->storeId, "products", "per_page=10&fields=test1%2Ctest2"),
                    "GET",
                    "Tiendanube Admin API Library for PHP v",
                    ['Authentication: bearer dummy-token'],
                ),
                new MockRequest(
                    $this->buildMockHttpResponse(200, $this->successResponse, ['Link' => $middlePageLinkHeader]),
                    $this->getProductsAdminApiPaginationUrl(2),
                    "GET",
                    "Tiendanube Admin API Library for PHP v",
                    ['Authentication: bearer dummy-token'],
                ),
                new MockRequest(
                    $this->buildMockHttpResponse(200, $this->successResponse, ['Link' => $lastPageLinkHeader]),
                    $this->getProductsAdminApiPaginationUrl(3),
                    "GET",
                    "Tiendanube Admin API Library for PHP v",
                    ['Authentication: bearer dummy-token'],
                ),
                new MockRequest(
                    $this->buildMockHttpResponse(200, $this->successResponse, ['Link' => $middlePageLinkHeader]),
                    $this->getProductsAdminApiPaginationUrl(2),
                    "GET",
                    "Tiendanube Admin API Library for PHP v",
                    ['Authentication: bearer dummy-token'],
                ),
                new MockRequest(
                    $this->buildMockHttpResponse(200, $this->successResponse, ['Link' => $firstPageLinkHeader]),
                    $this->getProductsAdminApiPaginationUrl(1),
                    "GET",
                    "Tiendanube Admin API Library for PHP v",
                    ['Authentication: bearer dummy-token'],
                ),

            ]
        );
        $client = new Rest($this->storeId, 'dummy-token');

        /** @var RestResponse */
        $response = $client->get('products', [], ["per_page" => "10", "fields" => 'test1,test2']);
        $this->assertNull($response->getPageInfo()->getPreviousPageUrl());

        $this->assertTrue($response->getPageInfo()->hasNextPage());
        /** @var RestResponse */
        $response = $client->get('products', [], $response->getPageInfo()->getNextPageQuery());
        /** @var RestResponse */
        $response = $client->get('products', [], $response->getPageInfo()->getNextPageQuery());
        $this->assertFalse($response->getPageInfo()->hasNextPage());
        $this->assertNull($response->getPageInfo()->getNextPageUrl());


        $this->assertTrue($response->getPageInfo()->hasPreviousPage());
        /** @var RestResponse */
        $response = $client->get('products', [], $response->getPageInfo()->getPreviousPageQuery());
        /** @var RestResponse */
        $response = $client->get('products', [], $response->getPageInfo()->getPreviousPageQuery());
        $this->assertFalse($response->getPageInfo()->hasPreviousPage());
        $this->assertNull($response->getPageInfo()->getPreviousPageUrl());
    }
}
