<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class FulfillmentOrderTrackingEvent extends Base
{
    protected string $address;
    protected string $created_at;
    protected string $description;
    protected string $estimated_delivery_at;
    protected array $geolocation;
    protected string $happened_at;
    protected int $id;
    protected string $status;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["order_id", "fulfillment_order_id"],
            "path" => "orders/<order_id>/fulfillment-orders/<fulfillment_order_id>/tracking-events"],

        ["http_method" => "post", "operation" => "post", "ids" => ["order_id", "fulfillment_order_id"],
            "path" => "orders/<order_id>/fulfillment-orders/<fulfillment_order_id>/tracking-events"],

        ["http_method" => "get", "operation" => "get", "ids" => ["order_id", "fulfillment_order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<fulfillment_order_id>/tracking-events/<id>"],

        ["http_method" => "put", "operation" => "put", "ids" => ["order_id", "fulfillment_order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<fulfillment_order_id>/tracking-events/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["order_id", "fulfillment_order_id", "id"],
            "path" => "orders/<order_id>/fulfillment-orders/<fulfillment_order_id>/tracking-events/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $order_id
     * @param int|string $fulfillment_order_id
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return FulfillmentOrderTrackingEvent|null
     */
    public static function find(
        Session $session,
        $order_id,
        $fulfillment_order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?FulfillmentOrderTrackingEvent {
        $result = parent::baseFind(
            $session,
            array_merge([
                "order_id" => $order_id,
                "fulfillment_order_id" => $fulfillment_order_id,
                "id" => $id,
            ], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param int|string $order_id
     * @param int|string $fulfillment_order_id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *
     * @return FulfillmentOrderTrackingEvent[]
     */
    public static function all(
        Session $session,
        $order_id,
        $fulfillment_order_id,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            array_merge(["order_id" => $order_id, "fulfillment_order_id" => $fulfillment_order_id], $urlIds),
            $params,
        );
    }

    /**
     * @param Session $session
     * @param int|string $order_id
     * @param int|string $fulfillment_order_id
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params
     *
     * @return array|null
     */
    public static function delete(
        Session $session,
        $order_id,
        $fulfillment_order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge([
                "order_id" => $order_id,
                "fulfillment_order_id" => $fulfillment_order_id,
                "id" => $id,
            ], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
