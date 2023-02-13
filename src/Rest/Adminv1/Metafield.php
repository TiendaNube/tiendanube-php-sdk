<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Metafield extends Base
{
    protected string $created_at;
    protected ?string $description;
    protected int $id;
    protected ?string $key;
    protected ?string $namespace;
    protected int $owner_id;
    protected string $owner_resource;
    protected ?string $value;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [
        "images" => ProductImage::class,
        "variants" => ProductVariant::class
    ];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["owner_resource"],
            "path" => "metafields/<owner_resource>"],

        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "metafields"],

        ["http_method" => "get", "operation" => "get", "ids" => ["id"],
            "path" => "metafields/<id>"],

        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "metafields/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "metafields/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Metafield|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Metafield {
        $result = parent::baseFind(
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     owner_id,
     *     namespace,
     *     key,
     *     created_at_max,
     *     created_at_min,
     *     updated_at_min,
     *     updated_at_max,
     *     page,
     *     per_page,
     *     fields,
     *
     * @return Metafield[]
     */
    public static function all(
        Session $session,
        string $owner_resource,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            array_merge(["owner_resource" => $owner_resource], $urlIds),
            $params,
        );
    }

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params
     *
     * @return array|null
     */
    public static function delete(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
