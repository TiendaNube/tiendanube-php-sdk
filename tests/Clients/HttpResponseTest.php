<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use JsonException;
use Tiendanube\Clients\HttpResponse;
use Tiendanube\BaseTestCase;

final class HttpResponseTest extends BaseTestCase
{
    public function testGetters()
    {
        $response = new HttpResponse(
            200,
            ['Header-1' => ['ABCD'], 'Header-2' => ['DCBA'], 'x-request-id' => ['test-request-id']],
            '{"name": "Super store"}',
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'Header-1' => ['ABCD'],
                'Header-2' => ['DCBA'],
                'x-request-id' => ['test-request-id'],
            ],
            $response->getHeaders(),
        );
        $this->assertEquals(['name' => 'Super store'], $response->getDecodedBody());
        $this->assertEquals('test-request-id', $response->getRequestId());
    }

    public function testGetRequestIdReturnsNullIfHeaderIsMissing()
    {
        $response = new HttpResponse(200);
        $this->assertNull($response->getRequestId());
    }

    public function testGetDecodedBodyWillThrwoExceptionIfBodyIsNotJson()
    {
        $response = new HttpResponse(200, [], "not-json");

        $this->expectException(JsonException::class);
        $response->getDecodedBody();
    }
}
