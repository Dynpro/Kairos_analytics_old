<?php

namespace LdapRecord\Lumen\Tests;

use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Laravel\Testing\EmulatedConnectionFake;
use LdapRecord\Lumen\LdapServiceProvider;
use LdapRecord\Models\ActiveDirectory\User;

class DirectoryEmulatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['ldap.default' => 'default']);
        config(['ldap.connections.default' => [
            'hosts' => ['localhost'],
            'username' => 'user',
            'password' => 'secret',
            'base_dn' => 'dc=local,dc=com',
        ]]);

        app()->register(LdapServiceProvider::class);
    }

    public function test_emulator_can_be_used()
    {
        $fake = DirectoryEmulator::setup();

        $this->assertInstanceOf(EmulatedConnectionFake::class, $fake);

        $user = User::create(['cn' => 'John Doe']);

        $this->assertTrue($user->exists);
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('cn=John Doe,dc=local,dc=com', $user->getDn());
    }
}
