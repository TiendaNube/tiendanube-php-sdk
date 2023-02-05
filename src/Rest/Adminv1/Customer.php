<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Customer extends Base
{
    protected bool $active;
    protected array $addresses;
    protected ?string $billing_address;
    protected ?string $billing_city;
    protected ?string $billing_country;
    protected ?string $billing_floor;
    protected ?string $billing_locality;
    protected ?string $billing_name;
    protected ?string $billing_number;
    protected ?string $billing_phone;
    protected ?string $billing_province;
    protected ?string $billing_zipcode;
    protected string $created_at;
    protected ?string $deleted_at;
    protected ?array $default_address;
    protected ?string $email;
    protected ?array $extra;
    protected ?string $first_interaction;
    protected int $id;
    protected ?string $identification;
    protected ?int $last_order_id;
    protected ?string $name;
    protected ?string $note;
    protected ?string $phone;
    protected ?string $total_spent;
    protected ?string $total_spent_currency;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "customers"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "customers"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "customers/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "customers/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Customer|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Customer {
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
     * @return Customer[]
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
