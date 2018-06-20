<?php

use Illuminate\Support\Collection;
use Zaichaopan\AccessGranted\Models\Permission;

class PermissionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->permission = Permission::create(['name' => 'write']);
    }

    /** @test */
    public function it_belongs_to_many_roles()
    {
        $this->assertInstanceOf(Collection::class, $this->permission->roles);
    }
}
