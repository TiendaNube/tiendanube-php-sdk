<?php

declare(strict_types=1);

namespace Tiendanube\Auth;

use Tiendanube\Context;
use Tiendanube\BaseTestCase;

final class SessionTest extends BaseTestCase
{
    public function testSessionGetterAndSetterFunctions()
    {
        $session = new Session('12345', 'my_access_token', 'read_products');

        $this->assertEquals('12345', $session->getStoreId());
        $this->assertEquals('my_access_token', $session->getAccessToken());
        $this->assertEquals('read_products', $session->getScope());
    }

    public function testIsValidReturnsTrue()
    {
        Context::$scopes = new Scopes('read_products');

        $session = new Session('12345', 'my_access_token', 'read_products');

        $this->assertTrue($session->isValid());
    }

    public function testIsValidReturnsFalseIfScopesHaveChanged()
    {
        Context::$scopes = new Scopes('read_products,write_orders');

        $session = new Session('12345', 'my_access_token', 'read_products');

        $this->assertFalse($session->isValid());
    }
}
