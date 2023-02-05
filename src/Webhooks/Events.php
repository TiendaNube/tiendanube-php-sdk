<?php

declare(strict_types=1);

namespace Tiendanube\Webhooks;

/**
 * Contains a list of known webhook events.
 *
 * For an up-to-date list of events, you can visit
 * https://tiendanube.github.io/api-documentation/resources/webhook
 */
final class Events
{
    public const APP_UNINSTALLED = 'app/uninstalled';
    public const APP_SUSPENDED = 'app/suspended';
    public const APP_RESUMED = 'app/resumed';
    public const CATEGORY_CREATED = 'category/created';
    public const CATEGORY_UPDATED = 'category/updated';
    public const CATEGORY_DELETED = 'category/deleted';
    public const ORDER_CREATED = 'order/created';
    public const ORDER_UPDATED = 'order/updated';
    public const ORDER_PAID = 'order/paid';
    public const ORDER_PACKED = 'order/packed';
    public const ORDER_FULFILLED = 'order/fulfilled';
    public const ORDER_CANCELLED = 'order/cancelled';
    public const PRODUCT_CREATED = 'product/created';
    public const PRODUCT_UPDATED = 'product/updated';
    public const PRODUCT_DELETED = 'product/deleted';
    public const DOMAIN_UPDATED = 'domain/updated';
    public const THEME_UPDATED = 'theme/updated';
}
