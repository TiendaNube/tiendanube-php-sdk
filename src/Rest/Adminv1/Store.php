<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Store extends Base
{
    protected ?string $address;
    protected bool $admin_blocked;
    protected bool $admin_disabled;
    protected ?string $admin_language;
    protected ?string $blog;
    protected ?string $business_address;
    protected ?string $business_id;
    protected ?string $business_name;
    protected ?string $business_zipcode;
    protected ?string $contact_email;
    protected ?string $country;
    protected string $created_at;
    protected ?string $customer_accounts;
    protected ?string $custom_dimension;
    protected ?string $current_theme;
    protected ?string $customer_type;
    protected ?array $description;
    protected ?array $domains;
    protected ?string $email;
    protected ?string $first_payment_date;
    protected bool $is_last_payment_rejected;
    protected bool $is_mercadopago_recurring_pending;
    protected bool $is_trial;
    protected ?string $facebook;
    protected ?string $google_plus;
    protected bool $has_stock;
    protected int $id;
    protected ?string $instagram;
    protected ?array $languages;
    protected ?string $logo;
    protected ?string $main_currency;
    protected ?string $main_language;
    protected ?array $name;
    protected ?string $original_domain;
    protected ?string $paid_until;
    protected ?string $phone;
    protected ?array $plan;
    protected ?string $plan_name;
    protected int $product_count;
    protected ?string $pinterest;
    protected ?string $source;
    protected ?string $twitter;

    protected ?string $type;

    protected ?string $updated_at;
    protected ?string $url_with_protocol;
    protected ?string $user_type;

    protected ?array $tags;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "store"],
    ];

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Store|null
     */
    public static function get(
        Session $session,
        array $urlIds = [],
        array $params = []
    ): ?Store {
        $result = parent::baseFind(
            $session,
            $urlIds,
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }
}
