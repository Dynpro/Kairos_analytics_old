<?php

namespace LdapRecord\Lumen\Tests;

use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\Lumen\LdapServiceProvider;

class LdapServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['ldap.default' => 'default']);
        config(['ldap.connections.default' => [
            'hosts' => ['localhost'],
            'username' => 'user',
            'password' => 'secret',
        ]]);

        app()->register(LdapServiceProvider::class);
    }

    public function test_connections_are_auto_loaded()
    {
        $this->assertIsArray($connections = Container::getInstance()->getConnections());
        $this->assertCount(1, $connections);

        $this->assertInstanceOf(Connection::class, $default = $connections['default']);

        $config = $default->getConfiguration();
        $this->assertEquals(['localhost'], $config->get('hosts'));
        $this->assertEquals('user', $config->get('username'));
        $this->assertEquals('secret', $config->get('password'));
    }
}
