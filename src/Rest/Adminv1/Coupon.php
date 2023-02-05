<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Coupon extends Base
{
    protected ?string $code;
    protected ?array $categories;
    protected ?string $deleted_at;
    protected ?string $end_date;
    protected int $id;
    protected int $max_uses;
    protected ?string $min_price;
    protected ?string $type;
    protected string $updated_at;
    protected ?string $start_date;
    protected bool $valid;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [
        "categories" => Category::class,
    ];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "coupons"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "coupons"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "coupons/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "coupons/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "coupons/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Coupon|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Coupon {
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
     *     language,
     *     since_id,
     *     q,
     *     handle,
     *     category_id,
     *     published,
     *     free_shipping,
     *     max_stock,
     *     min_stock,
     *     has_promotional_price,
     *     has_weight,
     *     has_all_dimensions,
     *     has_weight_and_all_dimensions,
     *     created_at_max,
     *     created_at_min,
     *     updated_at_min,
     *     updated_at_max,
     *     sort_by,
     *     page,
     *     per_page,
     *     fields,
     *
     * @return Coupon[]
     */
    public static function all(
        Session $session,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            [],
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
