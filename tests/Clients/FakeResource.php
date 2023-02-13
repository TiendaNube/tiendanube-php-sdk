<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

final class FakeResource extends Base
{
    protected int $id;
    protected ?int $other_resource_id;
    protected ?string $attribute;
    protected ?FakeResource $has_one_attribute;
    protected ?array $has_many_attribute;

    protected ?string $unknown;
    protected ?string $unsaveable_attribute;


    public static string $apiVersion = "v50";

    protected static array $hasOne = [
        "has_one_attribute" => FakeResource::class,
    ];

    protected static array $hasMany = [
        "has_many_attribute" => FakeResource::class,
    ];

    protected static array $readOnlyAttributes = ["unsaveable_attribute"];

    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "fake_resources"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "fake_resources"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "fake_resources/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "fake_resources/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "fake_resources/<id>"],
        [
            "http_method" => "get", "operation" => "custom", "ids" => ["other_resource_id", "id"],
            "path" => "other_resources/<other_resource_id>/fake_resources/<id>/custom",
        ],
        [
            "http_method" => "delete", "operation" => "delete", "ids" => ["other_resource_id", "id"],
            "path" => "other_resources/<other_resource_id>/fake_resources/<id>",
        ],
    ];

    public static function find(Session $session, int $id, array $params = []): ?FakeResource
    {
        $result = parent::baseFind($session, ["id" => $id], $params);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @return FakeResource[]
     */
    public static function all(Session $session, array $params = []): array
    {
        return parent::baseFind($session, [], $params);
    }

    public static function delete(Session $session, int $id, array $otherIds = [])
    {
        parent::request("delete", "delete", $session, array_merge(["id" => $id], $otherIds));
    }

    public static function custom(Session $session, int $id, array $otherIds = []): array
    {
        return parent::request("get", "custom", $session, array_merge(["id" => $id], $otherIds))->getDecodedBody();
    }
}
