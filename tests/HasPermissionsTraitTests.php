<?php

use Illuminate\Support\Collection;
use Zaichaopan\AccessGranted\Models\Permission;

trait HasPermissionsTraitTests
{
    abstract protected function getModel();

    // Some model may alias hasPermission in HasPermissions trait
    protected function getCheckPermissionMethodNameInHasPermissionsTrait()
    {
       return 'hasPermission';
    }

    /** @test */
    public function it_belongs_to_many_permissions()
    {
        $this->assertInstanceOf(Collection::class, $this->getModel()->permissions);
    }

    /** @test */
    public function it_cannot_be_given_a_invalid_permission()
    {
        $model = $this->getModel();
        $this->assertCount(0, $model->permissions);
        $this->assertFalse($model->givePermissionTo('invalid'));
        $this->assertCount(0, $model->permissions);
    }

    /** @test */
    public function it_can_be_given_a_valid_permission()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);

        $this->assertCount(0, $model->permissions);
        $this->assertTrue($model->givePermissionTo($readPermission->name));

        $model = $model->fresh();
        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_be_given_valid_permissions()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $this->assertCount(0, $model->permissions);
        $this->assertTrue($model->givePermissionTo($readPermission->name, $writePermission->name));

        $model = $model->fresh();
        $this->assertCount(2, $permissions = $model->permissions->pluck('id')->toArray());
        $this->assertContains($readPermission->id, $permissions);
        $this->assertContains($writePermission->id, $permissions);
    }

    /** @test */
    public function it_cannot_be_withdrew_a_invalid_permission()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $model->givePermissionTo($readPermission->name);

        $this->assertFalse($model->withdrawPermissionTo('invalid'));

        $model = $model->fresh();
        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions->first()->id);
    }

    /** @test */
    public function it_can_be_withdrew_a_valid_permission()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $model->givePermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);

        $model->withdrawPermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertCount(0, $permissions = $model->permissions);
    }

    /** @test */
    public function it_can_be_withdrew_valid_permissions()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $model->givePermissionTo($readPermission->name, $writePermission->name);
        $model->withdrawPermissionTo($readPermission->name, $writePermission->name);
        $model = $model->fresh();

        $this->assertCount(0, $model->permissions);
    }

    /** @test */
    public function it_can_be_withdrew_all_permissions()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);
        $model->givePermissionTo($readPermission->name, $writePermission->name);
        $model->withdrawAllPermissions();
        $model = $model->fresh();

        $this->assertCount(0, $model->permissions);
    }

    /** @test */
    public function a_valid_permission_can_not_be_given_twice()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $model->givePermissionTo($readPermission->name);
        $this->assertCount(1, $model->permissions);

        $model->givePermissionTo($readPermission->name, $writePermission->name);
        $model= $model->fresh();

        $this->assertCount(2, $permissions = $model->permissions->pluck('id')->toArray());
        $this->assertContains($readPermission->id, $permissions);
        $this->assertContains($writePermission->id, $permissions);
    }

    /** @test */
    public function it_cannot_update_invalid_permission()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);

        $model->givePermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions->first()->id);

        $model->updatePermission('invalid');
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions = $model->permissions->first()->id);
    }

    /** @test */
    public function it_can_update_a_valid_permission()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $model->givePermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions->first()->id);

        $model->updatePermission($writePermission->name);
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($writePermission->id, $permissions = $model->permissions->first()->id);
    }

    /** @test */
    public function it_can_update_valid_permissions()
    {
        $model = $this->getModel();
        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);
        $deletePermission = Permission::create(['name' => 'delete']);

        $model->givePermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertCount(1, $permissions = $model->permissions);
        $this->assertEquals($readPermission->id, $permissions->first()->id);

        $model->updatePermission($writePermission->name, $deletePermission->name);
        $model = $model->fresh();

        $this->assertCount(2, $permissions = $model->permissions->pluck('id')->toArray());
        $this->assertContains($writePermission->id, $permissions);
        $this->assertContains($deletePermission->id, $permissions);
    }

    /** @test */
    public function it_can_check_if_a_model_has_a_specific_permission_through_permissions()
    {
        $model = $this->getModel();
        $method = $this-> getCheckPermissionMethodNameInHasPermissionsTrait();

        $readPermission = Permission::create(['name' => 'read']);
        $writePermission = Permission::create(['name' => 'write']);

        $this->assertFalse($model->{$method}($readPermission->name));

        $model->givePermissionTo($readPermission->name);
        $model = $model->fresh();

        $this->assertTrue($model->{$method}($readPermission->name));
        $this->assertFalse($model->{$method}($writePermission->name));
    }
}
