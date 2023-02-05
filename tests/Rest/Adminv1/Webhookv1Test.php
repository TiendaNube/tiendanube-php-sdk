<?php

/***********************************************************************************************************************
* This file is auto-generated. If you have an issue, please create a GitHub issue.                                     *
***********************************************************************************************************************/

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Context;
use Tiendanube\BaseTestCase;
use Tiendanube\Clients\MockRequest;
use Tiendanube\Webhooks\Events;

final class Webhookv1Test extends BaseTestCase
{
    /** @var Session */
    private $testSession;

    public function setUp(): void
    {
        parent::setUp();

        Context::$apiVersion = "v1";

        $this->testSession = new Session("store_id", "AAAFFF111");
    }

    public function testCreateWebhook(): void
    {
        $this->mockTransportRequests([
            new MockRequest(
                $this->buildMockHttpResponse(200, json_encode(
                    [
                        "webhook" => [
                            "id" => 123,
                            "address" => "https://www.test.com/webhook",
                            "event" => Events::CATEGORY_CREATED,
                            "created_at" => "2023-02-04T23:53:43-03:00",
                            "updated_at" => "2023-02-04T23:53:43-03:00",
                        ],
                    ]
                )),
                "https://api.tiendanube.com/v1/1/webhooks",
                "POST",
                null,
                [
                    "X-Tiendanube-Access-Token: my_test_token",
                ],
                json_encode(
                    [
                        "webhook" => [
                            "address" => "https://www.test.com/webhook",
                            "topic" => Events::CATEGORY_CREATED,
                            "format" => "json",
                        ],
                    ]
                ),
            ),
        ]);

        $webhook = new Webhook($this->testSession);
        $webhook->address = "https://www.test.com/webhook";
        $webhook->topic = Events::CATEGORY_CREATED;
        $webhook->save();
    }
}
