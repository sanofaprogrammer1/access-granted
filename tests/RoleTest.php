<?php

use Illuminate\Support\Collection;
use Zaichaopan\Permission\Models\Role;
use Zaichaopan\Permission\Models\Permission;

class RoleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'valid role']);
        $this->readPermission = Permission::create(['name' => 'read']);
        $this->writePermission = Permission::create(['name' => 'write']);
        $this->deletePermission = Permission::create(['name' => 'delete']);
    }

    /** @test */
    public function it_belongs_to_many_permissions()
    {
        $this->assertInstanceOf(Collection::class, $this->role->permissions);
    }

    /** @test */
    public function it_cannot_be_given_a_invalid_permission()
    {
        $this->assertCount(0, $this->role->permissions);
        $this->assertFalse($this->role->givePermissionTo('invalid'));
        $this->assertCount(0, $this->role->permissions);
    }

    /** @test */
    public function it_can_be_give_a_valid_permission()
    {
        $this->assertCount(0, $this->role->permissions);
        $this->assertTrue($this->role->givePermissionTo($this->readPermission->name));

        $this->role = $this->role->fresh();
        $this->assertCount(1, $permissions = $this->role->permissions);
        $this->assertEquals($this->readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_be_given_valid_permissions()
    {
        $this->assertCount(0, $this->role->permissions);
        $this->assertTrue($this->role->givePermissionTo($this->readPermission->name, $this->writePermission->name));

        $this->role = $this->role->fresh();
        $this->assertCount(2, $permissions = $this->role->permissions->pluck('id')->toArray());
        $this->assertContains($this->readPermission->id, $permissions);
        $this->assertContains($this->writePermission->id, $permissions);
    }

    /** @test */
    public function it_cannot_be_withdrew_a_invalid_permission()
    {
        $this->role->givePermissionTo($this->readPermission->name);
        $this->assertFalse($this->role->withdrawPermissionTo('invalid'));
        $this->role = $this->role->fresh();
        $this->assertCount(1, $permissions = $this->role->permissions);
        $this->assertEquals($this->readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_be_withdrew_a_valid_permission()
    {
        $this->role->givePermissionTo($this->readPermission->name);
        $this->assertFalse($this->role->withdrawPermissionTo('invalid'));
        $this->role = $this->role->fresh();
        $this->assertCount(1, $permissions = $this->role->permissions);
        $this->assertEquals($this->readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_be_withdrew_valid_permissions()
    {
        $this->role->givePermissionTo($this->readPermission->name, $this->writePermission->name);
        $this->role->withdrawPermissionTo($this->readPermission->name, $this->writePermission->name);
        $this->role = $this->role->fresh();
        $this->assertCount(0, $this->role->permissions);
    }

    /** @test */
    public function it_can_be_withdrew_all_permissions()
    {
        $this->role->givePermissionTo($this->readPermission->name, $this->writePermission->name);
        $this->role->withdrawAllPermissions();
        $this->role = $this->role->fresh();
        $this->assertCount(0, $this->role->permissions);
    }

    /** @test */
    public function it_can_check_if_a_role_has_a_specific_permission()
    {
        $this->assertFalse($this->role->hasPermission($this->readPermission->name));
        $this->role->givePermissionTo($this->readPermission->name);
        $this->role = $this->role->fresh();
        $this->assertTrue($this->role->hasPermission($this->readPermission->name));
        $this->assertFalse($this->role->hasPermission($this->writePermission->name));
    }
}
