<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class FulfillmentOrder extends Base
{
    protected array $assigned_location;
    protected string $created_at;
    protected array $destination;
    protected ?string $fulfilled_at;
    protected int $id;
    protected array $line_items;
    protected string $number;
    protected array $recipient;
    protected array $shipping;
    protected string $status;
    protected int $total_quantity;
    protected string $total_price;
    protected string $total_weight;
    protected array $tracking_events;
    protected array $tracking_info;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["order_id"],
            "path" => "orders/<order_id>/fulfillment-orders"],

        ["http_method" => "get", "operation" => "get", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<id>"],

        ["http_method" => "patch", "operation" => "patch", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return FulfillmentOrder|null
     */
    public static function find(
        Session $session,
        $order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?FulfillmentOrder {
        $result = parent::baseFind(
            $session,
            array_merge(["order_id" => $order_id, "id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *
     * @return FulfillmentOrder[]
     */
    public static function all(
        Session $session,
        $order_id,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            array_merge(["order_id" => $order_id], $urlIds),
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
        $order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge(["order_id" => $order_id, "id" => $id], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
