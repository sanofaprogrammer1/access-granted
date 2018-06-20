<?php

use Zaichaopan\AccessGranted\Models\Role;

class RoleTest extends TestCase
{
    use HasPermissionsTraitTests;

    protected function getModel()
    {
        return Role::create(['name' => 'valid role']);
    }
}
