<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class ShippingCarrierOption extends Base
{
    protected bool $active;
    protected bool $allow_free_shipping;
    protected ?string $additional_cost;
    protected ?int $additional_days;
    protected string $code;
    protected int $id;
    protected array $name;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["shipping_carrier_id"],
            "path" => "shipping_carriers/<shipping_carrier_id>/options"],

        ["http_method" => "post", "operation" => "post", "ids" => ["shipping_carrier_id"],
            "path" => "shipping_carriers/<shipping_carrier_id>/options"],

        ["http_method" => "get", "operation" => "get", "ids" => ["shipping_carrier_id", "id"],
            "path" => "shipping_carriers/<shipping_carrier_id>/options/<id>"],

        ["http_method" => "put", "operation" => "put", "ids" => ["shipping_carrier_id", "id"],
            "path" => "shipping_carriers/<shipping_carrier_id>/options/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["shipping_carrier_id", "id"],
            "path" => "shipping_carriers/<shipping_carrier_id>/options/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return ShippingCarrierOption|null
     */
    public static function find(
        Session $session,
        $shipping_carrier_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?ShippingCarrierOption {
        $result = parent::baseFind(
            $session,
            array_merge(["shipping_carrier_id" => $shipping_carrier_id, "id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *
     * @return ShippingCarrierOption[]
     */
    public static function all(
        Session $session,
        $shipping_carrier_id,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            array_merge(["shipping_carrier_id" => $shipping_carrier_id], $urlIds),
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
        $shipping_carrier_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge(["shipping_carrier_id" => $shipping_carrier_id, "id" => $id], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
