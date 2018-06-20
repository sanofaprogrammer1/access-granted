<?php

use Zaichaopan\AccessGranted\Models\{Permission, Role};

class BladeTest extends TestCase
{
    const HAS_ROLE = 'has role';
    const DOES_NOT_HAS_ROLE = 'does not have role';
    const HAS_PERMISSION = 'has permission';
    const DOES_NOT_HAS_PERMISSION = 'does not have permission';

    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['email' => 'john@example.com']);
        $this->userRole = Role::create(['name' => 'user']);
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->readPermission = Permission::create(['name' => 'read']);
        $this->writePermission = Permission::create(['name' => 'write']);
    }

    /** @test */
    public function it_shows_appropriate_content_if_user_has_a_role()
    {
        auth()->setUser($this->user);

        $this->assertEquals(static::DOES_NOT_HAS_ROLE, $this->renderView('role', ['role' => $this->userRole->name]));

        $this->user->giveRole($this->userRole->name);
        auth()->setUser($this->user->fresh());

        $this->assertEquals(static::HAS_ROLE, $this->renderView('role', ['role' => $this->userRole->name]));
        $this->assertEquals(static::DOES_NOT_HAS_ROLE, $this->renderView('role', ['role' => $this->adminRole->name]));
    }

    /** @test */
    public function it_shows_appropriate_content_if_user_has_permission()
    {
        auth()->setUser($this->user);
        $this->assertEquals(static::DOES_NOT_HAS_PERMISSION, $this->renderView('can', ['permission' => $this->readPermission->name]));

        $this->user->givePermissionTo($this->readPermission->name);
        auth()->setUser($this->user->fresh());

        $this->assertEquals(static::HAS_PERMISSION, $this->renderView('can', ['permission' => $this->readPermission->name]));
        $this->assertEquals(static::DOES_NOT_HAS_PERMISSION, $this->renderView('can', ['permission' => $this->writePermission->name]));

        $this->adminRole->givePermissionTo($this->writePermission->name);
        $this->user->giveRole($this->adminRole->name);
        auth()->setUser($this->user->fresh());
        $this->assertEquals(static::HAS_PERMISSION, $this->renderView('can', ['permission' => $this->writePermission->name]));
    }

    protected function renderView($view, $parameters)
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }
        return trim((string)($view));
    }
}
