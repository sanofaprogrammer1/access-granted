<?php

use Illuminate\Database\Eloquent\Collection;
use Zaichaopan\AccessGranted\Models\{Permission, Role};

class UserTest extends TestCase
{
    use HasPermissionsTraitTests;

    public function setUp()
    {
        parent::setUp();

        $this->user = User::create([ 'email' => 'john@example.com' ]);
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->userRole = Role::create(['name' => 'user']);
    }

    /** @test */
    public function it_belongs_to_many_roles()
    {
        $roles = $this->user->roles;

        $this->assertInstanceOf(Collection::class, $roles);
    }

    /** @test */
    public function it_returns_false_when_user_given_a_invalid_role()
    {
        $invalidRoleName = 'invalid';
        $this->assertFalse($this->user->giveRole($invalidRoleName));
    }

    /** @test */
    public function it_can_give_user_a_valid_role()
    {
        $this->assertCount(0, $this->user->roles);

        $this->user->giveRole($this->userRole->name);
        $this->user = $this->user->fresh();

        $this->assertCount(1, $roles = $this->user->roles);
        $this->assertEquals($this->userRole->id, $roles->first()->id);
    }

    /** @test */
    public function it_can_give_user_valid_roles()
    {
        $this->assertCount(0, $this->user->roles);

        $this->user->giveRole($this->userRole->name, $this->adminRole->name);
        $this->user = $this->user->fresh();

        $this->assertCount(2, $roles = $this->user->roles->pluck('id')->toArray());
        $this->assertContains($this->userRole->id, $roles);
        $this->assertContains($this->adminRole->id, $roles);
    }

    /** @test */
    public function a_new_record_will_not_be_inserted_if_user_given_a_role_that_he_already_has()
    {
        $this->user->giveRole($this->userRole->name);

        $this->user = $this->user->fresh();
        $this->user->giveRole($this->userRole->name, $this->adminRole->name);

        $this->assertCount(2, $roles = $this->user->roles->pluck('id')->toArray());
        $this->assertContains($this->userRole->id, $roles);
        $this->assertContains($this->adminRole->id, $roles);
    }

    /** @test */
    public function it_can_remove_a_role_from_user()
    {
        $this->user->giveRole($this->userRole->name, $this->adminRole->name);
        $this->user->removeRole($this->userRole->name);
        $this->user = $this->user->fresh();

        $this->assertCount(1, $roles = $this->user->roles);
        $this->assertEquals($this->adminRole->id, $this->adminRole->id);
    }

    /** @test */
    public function it_can_remove_roles_from_users()
    {
        $this->user->giveRole($this->userRole->name, $this->adminRole->name);
        $this->user->removeRole($this->userRole->name, $this->adminRole->name, 'invalid');
        $this->user = $this->user->fresh();

        $this->assertCount(0, $roles = $this->user->roles);
    }

    /** @test */
    public function it_can_remove_all_roles_from_users()
    {
        $this->user->giveRole($this->userRole->name, $this->adminRole->name);
        $this->user->removeAllRoles($this->userRole->name, $this->adminRole->name, 'invalid');
        $this->user = $this->user->fresh();

        $this->assertCount(0, $roles = $this->user->roles);
    }

    /** @test */
    public function it_can_update_user_roles()
    {
        $this->user->giveRole($this->userRole->name);
        $this->user->updateRole($this->adminRole->name);

        $this->user = $this->user->fresh();
        $this->assertCount(1, $roles = $this->user->roles);
        $this->assertEquals($this->adminRole->id, $roles->first()->id);
    }

    /** @test */
    public function it_can_get_if_user_has_a_role()
    {
        $this->assertFalse($this->user->hasRole($this->userRole->name));

        $this->user->giveRole($this->userRole->name);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasRole($this->userRole->name));
        $this->assertFalse($this->user->hasRole($this->adminRole->name));
    }

    /** @test */
    public function it_can_get_if_user_has_any_role_from_a_list_of_role_names()
    {
        $this->assertFalse($this->user->hasRole($this->userRole->name, $this->adminRole->name));

        $this->user->giveRole($this->userRole->name);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasRole($this->userRole->name));
        $this->assertFalse($this->user->hasRole($this->adminRole->name));
        $this->assertTrue($this->user->hasRole($this->userRole->name, $this->adminRole->name));
    }

    /** @test */
    public function it_can_check_if_user_has_permissions_through_his_roles()
    {
        $readPermission = Permission::create(['name' => 'read']);

        $this->assertFalse($this->user->hasPermissionThroughRole($readPermission->name));

        $this->userRole->givePermissionTo($readPermission->name);
        $this->user->giveRole($this->userRole->name);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPermissionThroughRole($readPermission->name));
    }

    /** @test */
    public function it_can_check_if_user_has_permission()
    {
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $this->assertFalse($this->user->hasPermission($readPermission->name));
        $this->user->givePermissionTo($readPermission->name);
        $this->user = $this->user->fresh();
        $this->assertTrue($this->user->hasPermission($readPermission->name));

        $this->assertFalse($this->user->hasPermission($writePermission->name));
        $this->userRole->givePermissionTo($writePermission->name);
        $this->user->giveRole($this->userRole->name);
        $this->user = $this->user->fresh();
        $this->assertTrue($this->user->hasPermission($writePermission->name));
    }

    protected function getModel()
    {
        return $this->user;
    }

    protected function getCheckPermissionMethodNameInHasPermissionsTrait()
    {
       return 'hasPermissionThroughPermissionTrait';
    }
}
