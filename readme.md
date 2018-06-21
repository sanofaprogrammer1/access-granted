# Access Granted

This package allows you to add roles to users, add permissions to users and add permissions to role in your laravel app. It can be used in laravel 5.5 or higher.

<!-- TOC -->

- [Access Granted](#access-granted)
    - [Installation](#installation)
    - [Usage](#usage)
        - [Migration](#migration)
        - [Assign Role to user](#assign-role-to-user)
        - [Assign Permission to Role](#assign-permission-to-role)
        - [Add Permissions to User](#add-permissions-to-user)
        - [Has Permission through Role](#has-permission-through-role)
        - [Middleware](#middleware)
        - [Blade Directive](#blade-directive)

<!-- /TOC -->

## Installation

```bash
composer require zaichaopan/access-granted
```

## Usage

### Migration

After install the package, run the migration command to migrate __roles__, __permissions__, __role_user__, __permission_user__, and __permission_role__ tables.

```bash
php artisan migrate
```

The schema of the these tables

```php
// roles
Schema::create('roles', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->unique('name');
    $table->timestamps();
});
```

```php
// permissions
Schema::create('permissions', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->unique('name');
    $table->timestamps();
});
```

```php
// permission_user
Schema::create('permission_user', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('user_id');
    $table->unsignedInteger('permission_id');
    $table->unique(['user_id', 'permission_id']);
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('permission_id')->references('id')->on('permissions') ->onDelete('cascade');
    $table->timestamps();
});
```

```php
// permission_role
Schema::create('permission_role', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('permission_id');
    $table->unsignedInteger('role_id');
    $table->unique(['permission_id', 'role_id']);
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->timestamps();
})
```

```php
// role_user
 Schema::create('role_user', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('role_id');
    $table->unsignedInteger('user_id');
    $table->unique(['role_id', 'user_id']);
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->timestamps();
});
```

### Assign Role to user

To assign role to user, add __HasRoles__ trait the User model

```php
// User.php
class User extends Model
{
    use HasRoles;
}
```

This trait provides the follow methods

* __giveRole__

```php
public function giveRole(string ...$roleName): bool
```

It can be used to give a roles or multiple roles to a user. For example:

```php
// you have roles in your  admin and manger
$user->giveRole('admin');

// or both
$user->giveRole('admin', 'manager');
```

__Note:__

If the user is given a role that he or she already has, the role won't be add again. And if the user is given a role that does not exist in the roles table, the invalid role won't be added.

* updateRole

```php
public function updateRole(string ...$roleNames) : self
```

It can be used to update user role. After calling this method on a user, he or she will only have the new roles. All the old roles will be removed.

```php
public function updateRole(string ...$roleNames) : self
```

__Note__:

If the updated role cannot be found, the it will be ignore and the user still keeps his o her old roles.

* __removeRole__

```php
public function removeRole(string ...$roleNames) : self
```

It can be used to remove a role or multiple roles from user. For example:

```php
// $user have admin and manager roles

// to move manger role
$user->removeRole('manager');

// to move both
$user->removeRole('manager', 'admin');
```

__Note:__

If the removed role cannot be found or the user doesn't have it, it will be ignored.

* __removeAllRoles__

```php
public function removeAllRoles() : self
```

It is used to remove all roles from the user. For example:

```php
$user->removeAllRoles();
```

* __hasRole__

```php
public function hasRole(string ...$roles) : bool
```

It can be used to determine if a user has a role or any role from a list of roles. For example:

```php
$user->hasRole('admin');

// it will return true if the user is admin or manager
$user->hasRole('admin', 'manager');
```

### Assign Permission to Role

This package provides __HasPermissions__ trait which is used by __Role__ model by default. So you can give permissions to a role, you can use the following methods provided by the trait.

* __givePermissionTo__

```php
public function givePermissionTo(string ...$permissionNames): bool
```

It can be used to give a permission or multiple permissions to a role, for example:

```php
// your have a role admin and you have permissions which names are: write post and delete post
$adminRole->givePermissionTo('write post');

// or both
$adminRole->givePermissionTo('delete post');
```

If the given permission is valid or is already give, it will be ignored.

* __updatePermissionTo__

```php
public function updatePermissionTo(string ...$permissionNames): bool
```

It can be used to update the permissions of a role. After calling this method on a role, only the new permissions remain and all the old permissions it has will be removed. If an invalid permission is provided, it will be ignore. If all the provided permissions are invalid, the method returns false and role will still keep its old permissions. For example:

```php
$role->updatePermissionTo('read post');

// or both
$role->updatePermissionTo('read post', 'delete post');
```

* __withdrawPermissionTo__

```php
public function withdrawPermissionTo(string ...$permissionNames): bool
```

It can be used to withdraw a permission or multiple permissions from a role. If a withdrew permission is invalid, it will be ignored. If all the withdrew roles are invalid, the method returns false and the role will still keep its old permissions

```php
$role->withdrawPermissionTo('read post');

// or both
$role->withdrawPermissionTo('read post', 'delete post');
```

* __withdrawAllPermissions__

```php
public function withdrawAllPermissions(): self
```

It can be used to withdraw all the permission from the role.

```php
$role->withdrawPermissions();
```

* __hasPermission__

```php
public function hasPermission(string $permission): bool
```

It can be used to determine if a role has a specific permission. For example:

```php
$role->hasPermission('read post');
```

### Add Permissions to User

To add permissions to user, you just need to add __HasPermissions__ trait to your user model.

```php
class User extends Model
{
    use HasRoles,
        HasPermissions;

    // ...
}
```

Now you can use all the methods discussed above in __add permissions to role__ section to give permissions.

### Has Permission through Role

When we uses both __HasRoles__ and __HasPermissions__ trait in the User model, to determine if a user has a specific permission, we have to check if he or she has the permission through permissions table or if his or her role has this permission.

To make things easier, this package provides another trait __HasPermissionThroughRole__. To use it, just add it to your User model

```php
class User extends Model
{

    use HasRoles, HasPermissions, HasPermissionThroughRole;
}
```

This trait provides a method __hasPermissionThroughRole__ which can be used to determine if a user has a role that contains this permission. For example:

```php
$user->hasPermissionThroughRole('write post');
```

__Note__:

If we call __hasPermission__ method, it can only check if the user has the permission through the permissions table. To avoid confusion, we can alias this method when using __hasPermissions__ trait and override the __hasPermission__ method to include check user's role permissions.

```php
class User extends Model
{
    use HasRoles,
        HasRolePermissions,
        HasPermissions { hasPermission as hasPermissionThroughPermissionTrait; }

    protected $connection = 'testbench';

    protected $table = 'users';

    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionThroughPermissionTrait($permission) || $this->hasPermissionThroughRole($permission);
    }
}

```

### Middleware

This package provides two middleware: __RoleMiddleware__ and __PermissionMiddleware.__ They can be used to protect any route that needs a specific role and permission to access.

To use them, register them in your __Kernel.php__

```php
protected $routeMiddleware = [
    'role' => \Zaichaopan\AccessGranted\Middleware\RoleMiddleware::class,
    'permission' => \Zaichaopan\AccessGranted\Middleware\PermissionMiddleware::class,
];
```

To use them:

```php
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')
    }
}
```

```php
class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:edit post')
    }
}
```

### Blade Directive

This package provides blade directive which can be used in your blade to protect content that only use with a given role can access.

```html
@role('admin')
<!-- content here  -->
@endrole
```

To only allow user with given permission to access some content. Using

```html
@can('edit post')
<!-- content here -->
@endcan
```
