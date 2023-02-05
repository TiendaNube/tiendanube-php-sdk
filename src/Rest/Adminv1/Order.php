<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Order extends Base
{
    protected ?array $attributes;
    protected ?int $app_id;
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
    protected bool $checkout_enabled;
    protected ?string $cancel_reason;
    protected ?string $cancelled_at;
    protected ?array $clearsale;
    protected ?array $client_details;
    protected ?string $closed_at;
    protected string $completed_at;
    protected ?string $contact_email;
    protected ?string $contact_name;
    protected ?string $contact_phone;
    protected ?string $contact_identification;
    protected ?array $coupon;
    protected string $created_at;
    protected ?string $currency;
    protected ?Customer $customer;
    protected ?string $discount;
    protected ?string $discount_coupon;
    protected ?string $discount_gateway;
    protected ?array $extra;
    protected ?string $gateway;
    protected ?string $gateway_link;
    protected ?string $gateway_id;
    protected ?string $gateway_name;
    protected int $id;
    protected ?string $landing_url;
    protected ?string $language;
    protected ?string $next_action;
    protected ?string $note;
    protected int $number;
    protected ?string $owner_note;
    protected ?string $paid_at;
    protected ?array $payment_details;
    protected string $payment_status;
    protected ?array $products;
    protected ?array $promotional_discount;
    protected ?string $read_at;
    protected ?string $shipping;
    protected ?array $shipping_address;
    protected ?string $shipping_carrier_name;
    protected ?string $shipping_cost_owner;
    protected ?string $shipping_cost_customer;
    protected ?int $shipping_max_days;
    protected ?int $shipping_min_days;
    protected ?string $shipping_option;
    protected ?string $shipping_option_code;
    protected ?string $shipping_option_reference;
    protected ?array $shipping_pickup_details;
    protected ?string $shipping_pickup_type;
    protected string $shipping_status;
    protected ?string $shipping_store_branch_name;
    protected ?array $shipping_suboption;
    protected ?string $shipped_at;
    protected ?string $shipping_tracking_number;
    protected ?string $shipping_tracking_url;
    protected string $status;
    protected ?int $store_id;
    protected ?string $storefront;
    protected ?string $subtotal;
    protected ?string $token;
    protected ?string $total;
    protected ?string $total_usd;
    protected string $updated_at;
    protected ?string $weight;


    public static string $apiVersion = "v1";
    protected static array $hasOne = [
        "customer" => Customer::class,
    ];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "orders"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "orders"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "orders/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "orders/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "orders/<id>"],
        ["http_method" => "post", "operation" => "cancel", "ids" => ["id"], "path" => "orders/<id>/cancel"],
        ["http_method" => "post", "operation" => "close", "ids" => ["id"], "path" => "orders/<id>/close"],
        ["http_method" => "post", "operation" => "open", "ids" => ["id"], "path" => "orders/<id>/open"],
    ];


    /**
     * @param \Tiendanube\Auth\Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Order|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Order {
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

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     since_id,
     *     status,
     *     channels,
     *     payment_status,
     *     shipping_status,
     *     created_at_max,
     *     created_at_min,
     *     updated_at_min,
     *     updated_at_max,
     *     total_max,
     *     total_min,
     *     customer_ids,
     *     app_id,
     *     q,
     *     page,
     *     per_page,
     *     fields,
     * @return Order[]
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
     * @param mixed[] $params Allowed indexes:
     * @param array|string $body
     *
     * @return array|null
     */
    public function cancel(
        array $params = [],
        $body = []
    ): ?array {
        $response = parent::request(
            "post",
            "cancel",
            $this->session,
            ["id" => $this->id],
            $params,
            $body,
            $this,
        );

        return $response->getDecodedBody();
    }

    /**
     * @param mixed[] $params
     * @param array|string $body
     *
     * @return array|null
     */
    public function close(
        array $params = [],
        $body = []
    ): ?array {
        $response = parent::request(
            "post",
            "close",
            $this->session,
            ["id" => $this->id],
            $params,
            $body,
            $this,
        );

        return $response->getDecodedBody();
    }

    /**
     * @param mixed[] $params
     * @param array|string $body
     *
     * @return array|null
     */
    public function open(
        array $params = [],
        $body = []
    ): ?array {
        $response = parent::request(
            "post",
            "open",
            $this->session,
            ["id" => $this->id],
            $params,
            $body,
            $this,
        );

        return $response->getDecodedBody();
    }
}
