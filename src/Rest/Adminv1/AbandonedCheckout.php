<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class AbandonedCheckout extends Base
{
    protected ?string $abandoned_checkout_url;
    protected ?array $attributes;
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
    protected ?array $clearsale;
    protected ?string $completed_at;
    protected ?string $contact_accepts_marketing;
    protected ?string $contact_accepts_marketing_updated_at;
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
    protected ?string $gateway_id;
    protected ?string $gateway_name;
    protected int $id;
    protected ?string $language;
    protected ?string $next_action;
    protected ?string $note;
    protected ?array $payment_details;
    protected ?array $products;
    protected ?array $promotional_discount;
    protected ?string $shipping;
    protected ?string $shipping_address;
    protected ?string $shipping_city;
    protected ?string $shipping_country;
    protected ?string $shipping_cost_owner;
    protected ?string $shipping_cost_customer;
    protected ?string $shipping_floor;
    protected ?string $shipping_locality;
    protected ?int $shipping_max_days;
    protected ?int $shipping_min_days;
    protected ?string $shipping_name;
    protected ?string $shipping_number;
    protected ?string $shipping_option;
    protected ?string $shipping_option_code;
    protected ?string $shipping_option_reference;
    protected ?array $shipping_pickup_details;
    protected ?string $shipping_pickup_type;
    protected ?string $shipping_province;
    protected ?string $shipping_phone;
    protected ?string $shipping_store_branch_name;
    protected ?array $shipping_suboption;
    protected ?string $shipping_tracking_number;
    protected ?string $shipping_tracking_url;
    protected ?string $shipping_zipcode;
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
        "customer" => Customer::class
    ];

    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "checkouts"],

        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "checkouts/<id>"],

        ["http_method" => "post", "operation" => "coupons", "ids" => ["id"],
            "path" => "checkouts/<id>/coupons"],
    ];


    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     since_id,
     *     created_at_max,
     *     updated_at_max,
     *     page,
     *     per_page,
     *     fields,
     *
     * @return AbandonedCheckout[]
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
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return AbandonedCheckout|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?AbandonedCheckout {
        $result = parent::baseFind(
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param mixed[] $params
     * @param array|string $body
     *
     * @return array|null
     */
    public function coupons(
        array $params = [],
        array $body = []
    ): ?array {
        $response = parent::request(
            "post",
            "coupons",
            $this->session,
            ["id" => $this->id],
            $params,
            $body,
            $this,
        );

        return $response->getDecodedBody();
    }
}
