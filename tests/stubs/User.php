<?php

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\{HasPermissionsTrait, HasRolePermissionsTrait, HasRolesTrait};

class User extends Model
{
    use HasPermissionsTrait, HasRolesTrait, HasRolePermissionsTrait;

    protected $connection = 'testbench';

    protected $table = 'users';
}
