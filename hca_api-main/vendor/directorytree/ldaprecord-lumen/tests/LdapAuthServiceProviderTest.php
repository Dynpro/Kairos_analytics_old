<?php

namespace LdapRecord\Lumen\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Auth\DatabaseUserProvider;
use LdapRecord\Laravel\Auth\NoDatabaseUserProvider;
use LdapRecord\Lumen\LdapAuthServiceProvider;
use LdapRecord\Lumen\LdapServiceProvider;
use LdapRecord\Models\ActiveDirectory\User;

class LdapAuthServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->register(LdapServiceProvider::class);
        app()->register(LdapAuthServiceProvider::class);
    }

    public function test_no_database_user_provider_is_loaded()
    {
        config(['auth.defaults.guard' => 'api']);
        config(['auth.guards.api' => [
            'driver' => 'api',
            'provider' => 'ldap',
        ]]);
        config(['auth.providers.ldap' => [
            'driver' => 'ldap',
            'model' => User::class,
        ]]);

        $this->assertInstanceOf(NoDatabaseUserProvider::class, Auth::createUserProvider('ldap'));
    }

    public function test_database_user_provider_is_loaded()
    {
        config(['auth.defaults.guard' => 'api']);
        config(['auth.guards.api' => [
            'driver' => 'api',
            'provider' => 'ldap',
        ]]);
        config(['auth.providers.ldap' => [
            'driver' => 'ldap',
            'model' => User::class,
            'database' => [
                'model' => Model::class,
            ],
        ]]);

        $this->assertInstanceOf(DatabaseUserProvider::class, Auth::createUserProvider('ldap'));
    }
}
