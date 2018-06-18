<?php

use Zaichaopan\Permission\Models\Role;

class RoleDirectiveTest extends TestCase
{
    const HAS_ROLE = 'has role';
    const DOES_NOT_HAS_ROLE = 'does not have role';

    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['email' => 'john@example.com']);
        $this->userRole = Role::create(['name' => 'user']);
        $this->adminRole = Role::create(['name' => 'admin']);
    }

    /** @test */
    public function it_shows_correct_content_if_user_has_a_role()
    {
        auth()->setUser($this->user);

        $this->assertEquals(static::DOES_NOT_HAS_ROLE, $this->renderView('role', ['role' => $this->userRole->name]));

        $this->user->giveRole($this->userRole->name);
        auth()->setUser($this->user->fresh());

        $this->assertEquals(static::HAS_ROLE, $this->renderView('role', ['role' => $this->userRole->name]));
        $this->assertEquals(static::DOES_NOT_HAS_ROLE, $this->renderView('role', ['role' => $this->adminRole->name]));
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
