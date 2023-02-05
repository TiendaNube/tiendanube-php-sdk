<?php

declare(strict_types=1);

namespace Tiendanube;

use Psr\Log\LogLevel;
use ReflectionClass;
use Tiendanube\Auth\Scopes;

final class ContextTest extends BaseTestCase
{
    public function testCanCreateContext()
    {
        Context::initialize(
            'my_api_key',
            'my_api_secret_key',
            'www.my-super-app.com',
            'My Super App (support@my-super-app.com)',
            ['write_products', 'write_orders'],
        );

        $this->assertEquals('my_api_key', Context::$apiKey);
        $this->assertEquals('my_api_secret_key', Context::$apiSecretKey);
        $this->assertEquals(new Scopes(['write_products', 'write_orders']), Context::$scopes);
        $this->assertEquals('www.my-super-app.com', Context::$hostName);
        $this->assertEquals('https', Context::$hostScheme);

        // This should not trigger the exception
        Context::throwIfUninitialized();
    }

    // Context with different values has been set up in BaseTestCase
    public function testCanUpdateContext()
    {
        Context::initialize(
            'my_different_api_key',
            'my_different_api_secret_key',
            'www.my-super-different-app.com',
            'My Super App (support@my-super-app.com)',
            ['read_products', 'read_orders'],
        );

        $this->assertEquals('my_different_api_key', Context::$apiKey);
        $this->assertEquals('my_different_api_secret_key', Context::$apiSecretKey);
        $this->assertEquals(new Scopes(['read_products', 'read_orders']), Context::$scopes);
        $this->assertEquals('www.my-super-different-app.com', Context::$hostName);
    }

    public function testThrowsIfMissingArguments()
    {
        $this->expectException(\Tiendanube\Exception\MissingArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot initialize Tiendanube/Nuvemshop API Library. Missing values for: apiKey, apiSecretKey, hostName'
        );
        Context::initialize('', '', '', '');
    }

    public function testThrowsIfUninitialized()
    {
        // ReflectionClass is used in this test as isInitialized is a private static variable,
        // which would have been set as true due to previous tests
        $reflectedContext = new ReflectionClass('Tiendanube\Context');
        $reflectedIsInitialized = $reflectedContext->getProperty('isInitialized');
        $reflectedIsInitialized->setAccessible(true);
        $reflectedIsInitialized->setValue(false);

        $this->expectException(\Tiendanube\Exception\UninitializedContextException::class);
        Context::throwIfUninitialized();
    }


    public function testCanAddOverrideLogger()
    {
        $testLogger = new LogMock();

        Context::log('Logging something!', LogLevel::DEBUG);
        $this->assertEmpty($testLogger->records);

        Context::$logger = $testLogger;

        Context::log('Defaults to info');
        $this->assertTrue($testLogger->hasInfo('Defaults to info'));

        Context::log('Debug log', LogLevel::DEBUG);
        $this->assertTrue($testLogger->hasDebug('Debug log'));

        Context::log('Info log', LogLevel::INFO);
        $this->assertTrue($testLogger->hasInfo('Info log'));

        Context::log('Notice log', LogLevel::NOTICE);
        $this->assertTrue($testLogger->hasNotice('Notice log'));

        Context::log('Warning log', LogLevel::WARNING);
        $this->assertTrue($testLogger->hasWarning('Warning log'));

        Context::log('Err log', LogLevel::ERROR);
        $this->assertTrue($testLogger->hasError('Err log'));

        Context::log('Crit log', LogLevel::CRITICAL);
        $this->assertTrue($testLogger->hasCritical('Crit log'));

        Context::log('Alert log', LogLevel::ALERT);
        $this->assertTrue($testLogger->hasAlert('Alert log'));

        Context::log('Emerg log', LogLevel::EMERGENCY);
        $this->assertTrue($testLogger->hasEmergency('Emerg log'));
    }

    /**
     * @dataProvider canSetHostSchemeProvider
     */
    public function testCanSetHostScheme($host, $expectedScheme, $expectedHost)
    {
        Context::initialize(
            'my_api_key',
            'my_api_secret_key',
            $host,
            'My Super App (support@my-super-app.com)',
            ['write_products', 'write_orders'],
        );

        $this->assertEquals($expectedHost, Context::$hostName);
        $this->assertEquals($expectedScheme, Context::$hostScheme);
    }

    public function canSetHostSchemeProvider()
    {
        return [
            ['my-super-app.com', 'https', 'my-super-app.com'],
            ['https://my-super-app.com', 'https', 'my-super-app.com'],
            ['http://my-super-app.com', 'http', 'my-super-app.com'],
            ['http://localhost', 'http', 'localhost'],
            ['http://localhost:1234', 'http', 'localhost:1234'],
        ];
    }

    public function testFailsOnInvalidHost()
    {
        $this->expectException(\Tiendanube\Exception\InvalidArgumentException::class);
        Context::initialize(
            'may_api_key',
            'my_api_secret_key',
            'my-super-wrong--host-!@#$%^&*',
            'My Super App (support@my-super-app.com)',
        );
    }
}
