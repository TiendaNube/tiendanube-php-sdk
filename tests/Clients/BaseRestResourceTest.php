<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Tiendanube\Auth\Session;
use Tiendanube\Context;
use Tiendanube\Exception\RestResourceException;
use Tiendanube\Exception\RestResourceRequestException;
use Tiendanube\BaseTestCase;

final class BaseRestResourceTest extends BaseTestCase
{
    use PaginationTestHelper;

    private ?Session $session = null;
    private string $prefix;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function setUp(): void
    {
        parent::setUp();

        Context::$apiVersion = "v50";
        $this->prefix = "https://{$this->domain}/" . Context::$apiVersion . "/{$this->storeId}";

        $this->session = new Session($this->storeId, "dummy-token");
    }

    public function testFindsResourceById()
    {
        $body = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/fake_resources/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResource::find($this->session, 1);
        $this->assertEquals([1, "attribute"], [$resource->id, $resource->attribute]);
    }

    public function testFindsWithParam()
    {
        $body = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/fake_resources/1?param=value",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResource::find($this->session, 1, ["param" => "value"]);
        $this->assertEquals([1, "attribute"], [$resource->id, $resource->attribute]);
    }

    public function testFindsResourceAndChildrenById()
    {
        $body = [
            "id" => 1,
            "attribute" => "attribute1",
            "has_one_attribute" => ["id" => 2, "attribute" => "attribute2"],
            "has_many_attribute" => [
                ["id" => 3, "attribute" => "attribute3"],
            ],
        ];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/fake_resources/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResource::find($this->session, 1);
        $this->assertEquals([1, "attribute1"], [$resource->id, $resource->attribute]);
        $this->assertEquals(
            [2, "attribute2"],
            [$resource->has_one_attribute->id, $resource->has_one_attribute->attribute]
        );
        $this->assertEquals(
            [3, "attribute3"],
            [$resource->has_many_attribute[0]->id, $resource->has_many_attribute[0]->attribute]
        );
    }

    public function testFindsResourceWithEmptyChildren()
    {
        $body = [
            "id" => 1,
            "attribute" => "attribute1",
            "has_one_attribute" => null,
            "has_many_attribute" => null,
        ];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/fake_resources/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResource::find($this->session, 1);
        $this->assertEquals([1, "attribute1"], [$resource->id, $resource->attribute]);
        $this->assertNull($resource->has_one_attribute);
        $this->assertEmpty($resource->has_many_attribute);
    }

    public function testFailsOnFindingNonexistentResourceById()
    {
        $body = ["errors" => "Not found"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(404, $body),
                "{$this->prefix}/fake_resources/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $this->expectException(RestResourceRequestException::class);
        FakeResource::find($this->session, 1);
    }

    public function testFindsAllResources()
    {
        $body = [
            ["id" => 1, "attribute" => "attribute1"],
            ["id" => 2, "attribute" => "attribute2"],
        ];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/fake_resources",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resources = FakeResource::all($this->session);
        $this->assertEquals([1, "attribute1"], [$resources[0]->id, $resources[0]->attribute]);
        $this->assertEquals([2, "attribute2"], [$resources[1]->id, $resources[1]->attribute]);
    }

    public function testSaves()
    {
        $requestBody = ["attribute" => "attribute"];
        $responseBody = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $responseBody),
                "{$this->prefix}/fake_resources",
                "POST",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($requestBody)
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->attribute = "attribute";

        $resource->save();
        $this->assertNull($resource->id);
    }

    public function testSavesAndUpdates()
    {
        $requestBody = ["attribute" => "attribute"];
        $responseBody = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $responseBody),
                "{$this->prefix}/fake_resources",
                "POST",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($requestBody)
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->attribute = "attribute";

        $resource->saveAndUpdate();
        $this->assertEquals(1, $resource->id);
    }

    public function testSavesExistingResource()
    {
        $requestBody = ["id" => 1, "attribute" => "attribute"];
        $responseBody = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $responseBody),
                "{$this->prefix}/fake_resources/1",
                "PUT",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($requestBody)
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->id = 1;
        $resource->attribute = "attribute";

        $resource->save();
    }

    public function testSavesWithChildren()
    {
        $requestBody = [
            "id" => 1,
            "has_one_attribute" => ["attribute" => "attribute1"],
            "has_many_attribute" => [["attribute" => "attribute2"],["attribute" => "attribute3"]],
        ];
        $responseBody = ["fake_resource" => ["id" => 1, "attribute" => "attribute"]];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $responseBody),
                "{$this->prefix}/fake_resources/1",
                "PUT",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($requestBody)
            ),
        ]);

        $child1 = new FakeResource($this->session);
        $child1->attribute = "attribute1";

        $child2 = new FakeResource($this->session);
        $child2->attribute = "attribute2";

        $child3 = new FakeResource($this->session);
        $child3->attribute = "attribute3";

        $resource = new FakeResource($this->session);
        $resource->id = 1;
        $resource->has_one_attribute = $child1;
        $resource->has_many_attribute = [$child2, $child3];

        $resource->save();
    }

    public function testLoadsUnknownAttribute()
    {
        $body = ["attribute" => "value", "unknown" => "some-value"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, json_encode($body)),
                "{$this->prefix}/fake_resources/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResource::find($this->session, 1);

        $this->assertEquals("value", $resource->attribute);
        $this->assertEquals("some-value", $resource->{"unknown"});
        $this->assertEquals("some-value", $resource->toArray()["unknown"]);
    }

    public function testSavesWithUnknownAttribute()
    {
        $body = ["unknown" => "some-value"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, ""),
                "{$this->prefix}/fake_resources",
                "POST",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($body)
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->unknown = "some-value";

        $resource->save();
    }

    public function testSavesWithForcedNullValue()
    {
        $body = ["id" => 1, "has_one_attribute" => null];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, ""),
                "{$this->prefix}/fake_resources/1",
                "PUT",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($body)
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->id = 1;
        $resource->has_one_attribute = null;

        $resource->save();
    }

    public function testIgnoresUnsaveableAttribute()
    {
        $requestBody = ["attribute" => "attribute"];
        $responseBody = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $responseBody),
                "{$this->prefix}/fake_resources",
                "POST",
                null,
                ["Authentication: bearer dummy-token"],
                json_encode($requestBody),
                null,
                true,
                false,
                true
            ),
        ]);

        $resource = new FakeResource($this->session);
        $resource->attribute = "attribute";
        $resource->unsaveable_attribute = "unsaveable_attribute";

        $resource->save();
        $this->assertNull($resource->id);
    }

    public function toArrayIncludesReadOnlyAttributes()
    {
        $resource = new FakeResource($this->session);
        $resource->attribute = "attribute";
        $resource->unsaveable_attribute = "unsaveable_attribute";

        $array = $resource->toArray();
        $this->assertEquals("attribute", $array["attribute"]);
        $this->assertEquals("unsaveable_attribute", $array["unsaveable_attribute"]);
    }

    public function toArrayExcludesReadOnlyAttributesWithSavingArgEqualTrue()
    {
        $resource = new FakeResource($this->session);
        $resource->attribute = "attribute";
        $resource->unsaveable_attribute = "unsaveable_attribute";

        $array = $resource->toArray(true);
        $this->assertEquals("attribute", $array["attribute"]);
        $this->assertArrayNotHasKey("unsaveable_attribute", $array);
    }

    public function testDeletesExistingResource()
    {
        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, ""),
                "{$this->prefix}/fake_resources/1",
                "DELETE",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        FakeResource::delete($this->session, 1);
    }

    public function testDeletesOtherResource()
    {
        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, ""),
                "{$this->prefix}/other_resources/2/fake_resources/1",
                "DELETE",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        FakeResource::delete($this->session, 1, array("other_resource_id" => 2));
    }

    public function testFailsDeletingNonexistentResource()
    {
        $body = ["errors" => "Not found"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(404, $body),
                "{$this->prefix}/fake_resources/2",
                "DELETE",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $this->expectException(RestResourceRequestException::class);
        FakeResource::delete($this->session, 2);
    }

    public function testMakesCustomRequests()
    {
        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, json_encode(["test body"])),
                "{$this->prefix}/other_resources/2/fake_resources/1/custom",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $response = FakeResource::custom($this->session, 1, array("other_resource_id" => 2));
        $this->assertEquals(["test body"], $response);
    }

    public function testPagination()
    {
        $body = [];

        $firstPaginationHeader = $this->getProductsLinkHeader(null, 2);
        $secondPaginationHeader = $this->getProductsLinkHeader(1, 3);

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(
                    200,
                    json_encode($body),
                    ["Link" => $firstPaginationHeader]
                ),
                "{$this->prefix}/fake_resources",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
            new MockRequest(
                $this->buildMockHttpResponse(
                    200,
                    json_encode($body),
                    ["Link" => $secondPaginationHeader]
                ),
                "{$this->prefix}/fake_resources?per_page=10&fields=test1%2Ctest2&page=2",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
            new MockRequest(
                $this->buildMockHttpResponse(
                    200,
                    json_encode($body),
                    ["Link" => $firstPaginationHeader]
                ),
                "{$this->prefix}/fake_resources?per_page=10&fields=test1%2Ctest2&page=1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        //We get the first page
        FakeResource::all($this->session);
        $this->assertEquals(
            ["page" => "2", "per_page" => "10", "fields" => "test1,test2"],
            FakeResource::$nextPageQuery
        );
        $this->assertNull(FakeResource::$prevPageQuery);

        //Then we go to the second one
        FakeResource::all($this->session, FakeResource::$nextPageQuery);
        $this->assertEquals(
            ["page" => "3", "per_page" => "10", "fields" => "test1,test2"],
            FakeResource::$nextPageQuery
        );
        $this->assertEquals(
            ["page" => "1", "per_page" => "10", "fields" => "test1,test2"],
            FakeResource::$prevPageQuery
        );

        //And now back to the first one
        FakeResource::all($this->session, FakeResource::$prevPageQuery);
        $this->assertEquals(
            ["page" => "2", "per_page" => "10", "fields" => "test1,test2"],
            FakeResource::$nextPageQuery
        );
        $this->assertNull(FakeResource::$prevPageQuery);
    }

    public function testAllowsCustomPrefixes()
    {
        $body = ["id" => 1, "attribute" => "attribute"];

        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, $body),
                "{$this->prefix}/custom_prefix/fake_resource_with_custom_prefix/1",
                "GET",
                null,
                ["Authentication: bearer dummy-token"],
            ),
        ]);

        $resource = FakeResourceWithCustomPrefix::find($this->session, 1);
        $this->assertEquals([1, "attribute"], [$resource->id, $resource->attribute]);
    }

    public function testThrowsOnMismatchedApiVersion()
    {
        Context::$apiVersion = "v1000";

        $this->expectException(RestResourceException::class);
        new FakeResource($this->session);
    }
}
