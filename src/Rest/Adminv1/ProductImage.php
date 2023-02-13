<?php

/***********************************************************************************************************************
* This file is auto-generated. If you have an issue, please create a GitHub issue.                                     *
***********************************************************************************************************************/

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class ProductImage extends Base
{
    protected ?array $alt;
    protected string $created_at;
    protected int $id;
    protected ?int $position;
    protected int $product_id;
    protected ?string $src;
    protected string $updated_at;
    protected ?array $values;
    protected ?string $weight;
    protected ?string $width;


    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["product_id"],
            "path" => "products/<product_id>/images"],

        ["http_method" => "post", "operation" => "post", "ids" => ["product_id"],
            "path" => "products/<product_id>/images"],

        ["http_method" => "get", "operation" => "get", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/images/<id>"],

        ["http_method" => "put", "operation" => "put", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/images/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/images/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds Allowed indexes:
     *     product_id
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return ProductImage|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?ProductImage {
        $result = parent::baseFind(
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds Allowed indexes:
     *     product_id
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

    /**
     * @param Session $session
     * @param array $urlIds Allowed indexes:
     *     product_id
     * @param mixed[] $params Allowed indexes:
     *     since_id,
     *     fields
     *
     * @return ProductImage[]
     */
    public static function all(
        Session $session,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            $urlIds,
            $params,
        );
    }

    /**
     * @param Session $session
     * @param array $urlIds Allowed indexes:
     *     product_id
     * @param mixed[] $params Allowed indexes:
     *     since_id
     *
     * @return array|null
     */
    public static function count(
        Session $session,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "get",
            "count",
            $session,
            $urlIds,
            $params,
            [],
        );

        return $response->getDecodedBody();
    }
}
