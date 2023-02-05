<?php

declare(strict_types=1);

namespace Tiendanube\Webhooks;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Tiendanube\Clients\HttpHeaders;
use Tiendanube\Exception\InvalidWebhookException;
use Tiendanube\Exception\MissingWebhookHandlerException;
use Tiendanube\BaseTestCase;

final class RegistryTest extends BaseTestCase
{
    /** @var array */
    private $processHeaders;

    /** @var array */
    private $processBody;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->processHeaders = [
            HttpHeaders::X_TIENDANUBE_HMAC => 'hM8r7V2szaFIyLhKCM9Oo3/kR4buy2h51xZPcJu0EOo=',
        ];

        $this->processBody = [
            'store_id' => $this->storeId,
            'event' => Events::PRODUCT_UPDATED,
            'foo' => 'bar',
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        // Clean up the registry for every test
        $reflection = new ReflectionClass(Registry::class);
        $property = $reflection->getProperty('REGISTRY');
        $property->setAccessible(true);
        $property->setValue([]);
    }

    public function testAddHandler()
    {
        $handler = $this->getMockHandler();
        Registry::addHandler(Events::APP_UNINSTALLED, $handler);

        $this->assertSame($handler, Registry::getHandler(Events::APP_UNINSTALLED));
    }

    public function testAddHandlerToExistingRegistry()
    {
        $handler = $this->getMockHandler();
        Registry::addHandler(Events::APP_UNINSTALLED, $handler);

        $this->assertSame($handler, Registry::getHandler(Events::APP_UNINSTALLED));

        // Now add a second webhook for a different topic
        $handler = $this->getMockHandler();
        Registry::addHandler(Events::PRODUCT_CREATED, $handler);

        $this->assertSame($handler, Registry::getHandler(Events::PRODUCT_CREATED));
    }

    public function testAddHandlerOverridesRegistry()
    {
        $handler = $this->getMockHandler();
        Registry::addHandler(Events::APP_UNINSTALLED, $handler);

        $this->assertSame($handler, Registry::getHandler(Events::APP_UNINSTALLED));

        // Now add a second handler for the same topic
        $handler = $this->getMockHandler();
        Registry::addHandler(Events::APP_UNINSTALLED, $handler);

        $this->assertSame($handler, Registry::getHandler(Events::APP_UNINSTALLED));
    }

    public function testCanRegisterAndUpdateWebhook()
    {
    }

    public function testSkipsUpdateIfCallbackIsTheSame()
    {
    }

    public function testThrowsOnRegistrationCheckError()
    {
    }

    public function testThrowsOnRegistrationError()
    {
    }

    public function testProcessWebhook()
    {
        $handler = $this->getMockHandler();
        $handler->expects($this->once())
            ->method('handle')
            ->with(
                Events::PRODUCT_UPDATED,
                $this->storeId,
                $this->processBody,
            );

        Registry::addHandler(Events::PRODUCT_UPDATED, $handler);

        $response = Registry::process($this->processHeaders, json_encode($this->processBody));
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getErrorMessage());
    }

    public function testProcessWebhookWithHandlerErrors()
    {
        $handler = $this->getMockHandler();
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException(new Exception('Something went wrong in the handler'));

        Registry::addHandler(Events::PRODUCT_UPDATED, $handler);

        $response = Registry::process($this->processHeaders, json_encode($this->processBody));
        $this->assertFalse($response->isSuccess());
        $this->assertEquals('Something went wrong in the handler', $response->getErrorMessage());
    }

    public function testProcessThrowsErrorOnMissingBody()
    {
        Registry::addHandler(Events::PRODUCT_UPDATED, $this->getMockHandler());

        $this->expectException(InvalidWebhookException::class);
        Registry::process($this->processHeaders, '');
    }

    public function testProcessThrowsErrorOnMissingHmac()
    {
        Registry::addHandler(Events::PRODUCT_UPDATED, $this->getMockHandler());

        $headers = $this->processHeaders;
        unset($headers[HttpHeaders::X_TIENDANUBE_HMAC]);

        $this->expectException(InvalidWebhookException::class);
        Registry::process($headers, json_encode($this->processBody));
    }

    public function testProcessThrowsErrorOnMissingTopic()
    {
        Registry::addHandler(Events::PRODUCT_UPDATED, $this->getMockHandler());

        $body = $this->processBody;
        unset($body['event']);

        $this->expectException(InvalidWebhookException::class);
        Registry::process($this->processHeaders, json_encode($body));
    }

    public function testProcessThrowsErrorOnMissingStoreId()
    {
        Registry::addHandler(Events::PRODUCT_UPDATED, $this->getMockHandler());

        $body = $this->processBody;
        unset($body['store_id']);

        $this->expectException(InvalidWebhookException::class);
        Registry::process($this->processHeaders, json_encode($body));
    }

    public function testProcessThrowsErrorOnInvalidHmac()
    {
        Registry::addHandler(Events::PRODUCT_UPDATED, $this->getMockHandler());

        $headers = $this->processHeaders;
        $headers[HttpHeaders::X_TIENDANUBE_HMAC] = 'whoops_this_is_wrong';

        $this->expectException(InvalidWebhookException::class);
        Registry::process($headers, json_encode($this->processBody));
    }

    public function testProcessThrowsErrorOnMissingHandler()
    {
        $this->expectException(MissingWebhookHandlerException::class);
        Registry::process($this->processHeaders, json_encode($this->processBody));
    }

    /**
     * Creates a new mock handler to be used for testing.
     *
     * @return MockObject|Handler
     */
    private function getMockHandler()
    {
        return $this->createMock(Handler::class);
    }
}
