<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class ProductVariant extends Base
{
    protected ?string $age_group;
    protected ?string $barcode;
    protected ?string $cost;
    protected ?string $compare_at_price;
    protected string $created_at;
    protected ?string $depth;
    protected ?string $height;
    protected ?string $gender;
    protected int $id;
    protected ?int $image_id;
    protected ?string $mpn;
    protected ?string $price;
    protected int $position;
    protected int $product_id;
    protected ?string $promotional_price;
    protected ?string $sku;
    protected ?int $stock;
    protected bool $stock_management;
    protected string $updated_at;
    protected ?array $values;
    protected ?string $weight;
    protected ?string $width;


    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["product_id"],
            "path" => "products/<product_id>/variants"],

        ["http_method" => "post", "operation" => "post", "ids" => ["product_id"],
            "path" => "products/<product_id>/variants"],

        ["http_method" => "put", "operation" => "put", "ids" => ["product_id"],
            "path" => "products/<product_id>/variants"],

        ["http_method" => "patch", "operation" => "patch", "ids" => ["product_id"],
            "path" => "products/<product_id>/variants"],

        ["http_method" => "get", "operation" => "get", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/variants/<id>"],

        ["http_method" => "put", "operation" => "put", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/variants/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["product_id", "id"],
            "path" => "products/<product_id>/variants/<id>"],

        ["http_method" => "post", "operation" => "stock", "ids" => ["product_id"],
            "path" => "products/<product_id>/variants/stock"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return ProductVariant|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?ProductVariant {
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
        $product_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge(["id" => $id, "product_id" => $product_id], $urlIds),
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
     *     created_at_max,
     *     created_at_min,
     *     updated_at_max,
     *     updated_at_min,
     *     page
     *     per_page
     *     fields
     *
     * @return ProductVariant[]
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
     * @param int|string $id
     * @param array $urlIds Allowed indexes:
     *     product_id
     * @param mixed[] $params
     *
     * @return array|null
     */
    public static function stock(
        Session $session,
        $product_id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "post",
            "stock",
            $session,
            array_merge(["product_id" => $product_id], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
