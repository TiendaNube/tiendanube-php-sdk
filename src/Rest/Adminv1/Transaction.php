<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Transaction extends Base
{
    protected array $authorized_amount;
    protected array $captured_amount;
    protected array $discount_amount;
    protected string $created_at;
    protected ?array $events;
    protected ?string $failure_code;
    protected ?array $info;
    protected int $id;
    protected ?array $payment_method;
    protected int $payment_provider_id;
    protected array $refunded_amount;
    protected array $status;
    protected array $voided_amount;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [
        "events" => TransactionEvent::class,
    ];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => ["order_id"],
            "path" => "orders/<order_id>/transactions"],

        ["http_method" => "post", "operation" => "post", "ids" => ["order_id"],
            "path" => "orders/<order_id>/transactions"],

        ["http_method" => "get", "operation" => "get", "ids" => ["order_id", "id"],
            "path" => "orders/<order_id>/transactions/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Product|null
     */
    public static function find(
        Session $session,
        $order_id,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Transaction {
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
     * @return Transaction[]
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
}
