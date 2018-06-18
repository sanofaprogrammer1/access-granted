<?php

use Illuminate\Http\Request;
use Zaichaopan\Permission\Models\Role;
use Zaichaopan\Permission\Models\Permission;
use Zaichaopan\Permission\Middlewares\PermissionMiddleware;

class PermissionMiddlewareTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['email' => 'john@example.com']);
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->readPermission = Permission::create(['name' => 'read']);
        $this->writePermission = Permission::create(['name' => 'write']);
    }

    /** @test */
    public function user_will_receive_401_if_not_login()
    {
        $request = Request::create('/read', 'GET');

        $permissionMiddleware = new PermissionMiddleware();

        try {
            $permissionMiddleware->handle($request, function () {
            }, $this->readPermission->name);
        } catch (\Exception $e) {
            $this->assertEquals(401, $e->getStatusCode());
        }
    }

    /** @test */
    public function user_will_receive_403_if_does_not_has_needed_permission()
    {
        $request = Request::create('/admin', 'GET');

        $request->setUserResolver(function () {
            return $this->user;
        });

        $permissionMiddleware = new PermissionMiddleware();

        try {
            $permissionMiddleware->handle($request, function () {
            }, $this->readPermission->name);
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function it_will_go_to_next_if_user_has_needed_permission()
    {
        $request = Request::create('/admin', 'GET');

        $this->user->givePermissionTo($this->readPermission->name);

        $request->setUserResolver(function () { return $this->user->fresh(); });

        $permissionMiddleware = new PermissionMiddleware();

        $response = $permissionMiddleware->handle($request, function () { }, $this->readPermission->name);

        $this->assertNull($response);

        // have not permission to write
        try {
            $permissionMiddleware->handle($request, function () {
            }, $this->writePermission->name);
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // give admin role write permission
        $this->adminRole->givePermissionTo($this->writePermission->name);
        $this->user->giveRole($this->adminRole->name);
        $request->setUserResolver(function () { return $this->user->fresh(); });

        $response = $permissionMiddleware->handle($request, function () { }, $this->writePermission->name);

        $this->assertNull($response);
    }
}
