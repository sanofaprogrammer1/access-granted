<?php

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\HasPermissionsTrait;

// we autoload it in the dev using classmap specify the file  tests/stubs/UserStub
// which means composer will look for all the classes in this file. So we don't have to give it a namespace
// we user stub classes to simulate the real Eloquent classes. We cannot use the one in the src models because
// in the stub classes we need to specify the database connection to testbench
class UserStub extends Model
{
    use HasPermissionsTrait;

    protected $connection = 'testbench';
}
