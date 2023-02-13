<?php

declare(strict_types=1);

namespace Tiendanube\Webhooks;

interface Handler
{
    /**
     * Handles a webhook event from Tiendanube/Nuvemshop. If this method finishes executing,
     * the webhook is considered successful.
     *
     * @param string $event The webhook event that was triggered
     * @param string $store_id  The store that triggered the event
     * @param array  $body  The payload of the webhook request from Tiendanube/Nuvemshop
     */
    public function handle(string $event, string $store_id, array $body): void;
}
