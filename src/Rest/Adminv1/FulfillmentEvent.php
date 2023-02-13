<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class FulfillmentEvent extends Base
{
    protected ?string $city;
    protected ?string $country;
    protected string $created_at;
    protected ?string $description;
    protected ?string $estimated_delivery_at;
    protected ?string $happened_at;
    protected int $id;
    protected int $order_id;
    protected ?string $province;
    protected ?string $status;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["order_id"],
            "path" => "orders/<order_id>/fulfillments"],

        ["http_method" => "post", "operation" => "post", "ids" => ["order_id"],
            "path" => "orders/<order_id>/fulfillments"],

        ["http_method" => "get", "operation" => "get", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/fulfillments/<id>"],

        ["http_method" => "delete", "operation" => "delete", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/fulfillments/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return FulfillmentEvent|null
     */
    public static function find(
        Session $session,
        $order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?FulfillmentEvent {
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
     * @return FulfillmentEvent[]
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
