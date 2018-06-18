<?php

use Zaichaopan\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Zaichaopan\Permission\Models\Permission;

class UserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create([ 'email' => 'john@example.com' ]);
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->userRole = Role::create(['name' => 'user']);
        $this->readPermission = Permission::create(['name' => 'read']);
        $this->writePermission = Permission::create(['name' => 'write']);
        $this->deletePermission = Permission::create(['name' => 'delete']);
    }

    /** @test */
    public function it_belongs_to_many_roles()
    {
        $roles = $this->user->roles;

        $this->assertInstanceOf(Collection::class, $roles);
    }

    /** @test */
    public function it_belongs_to_many_permissions()
    {
        $permissions = $this->user->permissions;

        $this->assertInstanceOf(Collection::class, $permissions);
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
    public function it_can_give_user_a_invalid_permission()
    {
        $this->assertCount(0, $this->user->permissions);

        $this->user->givePermissionTo('invalid permission');
        $this->user = $this->user->fresh();

        $this->assertCount(0, $this->user->permissions);
    }

    /** @test */
    public function it_can_give_user_a_valid_permission()
    {
        $this->assertCount(0, $this->user->permissions);

        $this->user->givePermissionTo($this->readPermission->name);
        $this->user = $this->user->fresh();

        $this->assertCount(1, $permissions = $this->user->permissions);
        $this->assertEquals($this->readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_give_user_valid_permissions()
    {
        $this->assertCount(0, $this->user->permissions);

        $this->user->givePermissionTo($this->readPermission->name, $this->writePermission->name, 'invalid permission');
        $this->user = $this->user->fresh();

        $this->assertCount(2, $permissions = $this->user->permissions->pluck('id')->toArray());
        $this->assertContains($this->readPermission->id, $permissions);
        $this->assertContains($this->writePermission->id, $permissions);
    }

    /** @test */
    public function a_valid_permission_can_not_be_given_twice()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->assertCount(1, $this->user->permissions);

        $this->user->givePermissionTo($this->readPermission->name, $this->writePermission->name);
        $this->user = $this->user->fresh();

        $this->assertCount(2, $permissions = $this->user->permissions->pluck('id')->toArray());
        $this->assertContains($this->readPermission->id, $permissions);
        $this->assertContains($this->writePermission->id, $permissions);
    }

    /** @test */
    public function it_cannot_withdraw_a_invalid_permission()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->assertCount(1, $this->user->permissions);

        $this->user->withdrawPermissionTo('invalid permission');

        $this->user = $this->user->fresh();

        $this->assertCount(1, $permissions = $this->user->permissions);
        $this->assertEquals($this->readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_withdraw_a_valid_permission()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->assertCount(1, $this->user->permissions);

        $this->user->withdrawPermissionTo($this->readPermission->name, $this->writePermission->name, 'invalid');
        $this->user = $this->user->fresh();

        $this->assertCount(0, $this->user->permissions);
    }

    /** @test */
    public function it_can_withdraw_valid_permissions()
    {
        $this->user->givePermissionTo($this->readPermission->name, $this->writePermission->name, 'invalid permission');
        $this->user->withdrawPermissionTo($this->readPermission->name, $this->writePermission->name, 'invalid');
        $this->user = $this->user->fresh();

        $this->assertCount(0, $this->user->permissions);
    }

    /** @test */
    public function it_can_withdraw_all_permissions()
    {
        $this->user->givePermissionTo($this->readPermission->name, $this->writePermission->name, 'invalid permission');
        $this->user->withdrawAllPermissions();
        $this->user = $this->user->fresh();

        $this->assertCount(0, $this->user->permissions);
    }

    /** @test */
    public function it_cannot_update_invalid_permission()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->user->updatePermission('invalid');
        $this->user = $this->user->fresh();

        $this->assertCount(1, $permissions = $this->user->permissions);
        $this->assertEquals($this->readPermission->id, $permissions = $this->user->permissions->first()->id);
    }

    /** @test */
    public function it_can_update_a_valid_permission()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->user->updatePermission($this->writePermission->name);
        $this->user = $this->user->fresh();

        $this->assertCount(1, $permissions = $this->user->permissions);
        $this->assertEquals($this->writePermission->id, $permissions = $this->user->permissions->first()->id);
    }

    /** @test */
    public function it_can_update_valid_permissions()
    {
        $this->user->givePermissionTo($this->readPermission->name);
        $this->user->updatePermission($this->writePermission->name, $this->deletePermission->name);
        $this->user = $this->user->fresh();

        $this->assertCount(2, $permissions = $this->user->permissions->pluck('id')->toArray());
        $this->assertContains($this->writePermission->id, $permissions);
        $this->assertContains($this->deletePermission->id, $permissions);
    }

    /** @test */
    public function it_can_check_if_user_has_permission_through_his_permissions()
    {
        $this->assertFalse($this->user->hasPermission($this->readPermission->name));
        $this->user->givePermissionTo($this->readPermission->name);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPermission($this->readPermission->name));
        $this->assertFalse($this->user->hasPermission($this->writePermission->name));
    }

    /** @test */
    public function it_can_check_if_user_has_permissions_through_his_roles()
    {
        $this->assertFalse($this->user->hasPermissionThroughRole($this->deletePermission->name));

        $this->adminRole->givePermissionTo($this->deletePermission->name);
        $this->user->giveRole($this->adminRole->name);
        $this->user = $this->user->fresh();
        $this->assertTrue($this->user->hasPermissionThroughRole($this->deletePermission->name));
    }
}
