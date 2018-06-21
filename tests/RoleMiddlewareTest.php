<?php

use Illuminate\Http\Request;
use Zaichaopan\AccessGranted\Models\Role;
use Zaichaopan\AccessGranted\Middleware\RoleMiddleware;

class RoleMiddlewareTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['email' => 'john@example.com']);
        $this->adminRole = Role::create(['name' => 'admin']);
    }

    /** @test */
    public function user_will_receive_401_if_not_login()
    {
        $request = Request::create('/admin', 'GET');

        $roleMiddleware = new RoleMiddleware();

        $role = $this->adminRole->name;

        try {
            $response = $roleMiddleware->handle($request, function () {
            }, $role);
        } catch (\Exception $e) {
            $this->assertEquals(401, $e->getStatusCode());
        }
    }

    /** @test */
    public function user_will_receive_403_if_does_not_has_needed_role()
    {
        $request = Request::create('/admin', 'GET');

        $request->setUserResolver(function () { return $this->user; });

        $roleMiddleware = new RoleMiddleware();

        $role = $this->adminRole->name;

        try {
            $response = $roleMiddleware->handle($request, function () {
            }, $role);
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function it_will_go_to_next_if_user_has_needed_role()
    {
        $request = Request::create('/admin', 'GET');

        $this->user->giveRole($this->adminRole->name);

        $request->setUserResolver(function () {
            return $this->user->fresh();
        });

        $roleMiddleware = new RoleMiddleware();

        $role = $this->adminRole->name;

        $response = $roleMiddleware->handle($request, function () { }, $role);

        $this->assertNull($response);
    }
}
