# Entrust (Laravel 5 Package)

cloty-entrust 是一个基于laravel5的简单验证角色权限的插件


## 内容

- [安装](#安装)
- [配置](#配置)
    - [表结构说明](#表结构说明)
    - [模型](#模型)
        - [EntrustRole](#EntrustPermission)
        - [EntrustPermission](#EntrustPermission)
        - [User](#user)
- [使用](#使用)
    - [概念](#概念)
        - [验证 Roles & Permissions](#验证-roles--permissions)
        - [ability](#ability)
    - [Blade 模板](#Blade 模板)
    - [Middleware](#middleware)

## 安装

1) 安装entrust的方式很简单，直接添加到laravel5的composer.json安装即可。或者通过命令 `composer update` 安装:

```json
"cloty/cloty-entrust": "dev-master"
```

2) 在 `config/app.php`中的 `providers` 引入服务提供者:

```php
Cloty\Entrust\Providers\EntrustServiceProvider::class,
```

3)同样的在 `config/app.php` 中的 `aliases ` 引入:

```php
'Entrust'   => Cloty\Entrust\Facades\EntrustFacade::class,
```

4) 运行下面的命令，发布包配置文件 `config/cloty-entrust.php` 和 `migrations`:

```shell
php artisan vendor:publish
```

5) 中间件开发中（TODO）:

```php

```

## 配置

可以通过编辑文件  `config/entrust.php` 变更对应的表名或者模型

###

配置完后可以使用artisan迁移命令来运行它:

```bash
php artisan migrate
```
### 表结构说明

迁移之后，将出现四个新表:
- `entrust_roles` &mdash; 角色表
- `entrust_permissions` &mdash; 权限表
- `entrust_role_users` &mdash; [many-to-many](https://laravel.com/docs/5.4/eloquent-relationships#many-to-many-polymorphic-relations) 角色与用户之间的关系
- `entrust_permission_roles` &mdash; [many-to-many](https://laravel.com/docs/5.4/eloquent-relationships#many-to-many-polymorphic-relations) 角色和权限之间的关系

### Models

#### EntrustRole

#### Permission

#### User

需要在你的 `User`模型引入 `EntrustUserTrait` trait。比如:

```php
<?php

use Cloty\Entrust\Traits\EntrustUserTrait;

class User extends Eloquent
{
    use EntrustUserTrait;

    ...
}
```

这将启用与角色的关系，并在用户模型中添加以下方法 `entrustRoles()`, `hasRole($name)`, `canDo($permission)`, and `ability($roles, $permissions, $options)`。

## 使用

### 概念

开始使用entrust：

```php
<?php

...

use Entrust;

class TestController extends Controller
{
    ...
}

```
新增角色记录

```php

$admins = [
    'name' => 'admin',
    'display_name' => 'Administrator', //选填
    'description' => 'given project', //选填
];

$admin = Entrust::createRole($admins);

```
接下来，让我们将它们分配给用户。
由于“HasRole”的特点，用户绑定角色将变得简单:

```php

TODO:支持通过Entrust直接绑定role

$user = User::where('username', 'cloty')->first();

// 方法
$user->attachRole($admin); // 参数可以是EntrustRole对象、数组或id

// 或者laravel框架原生的关系绑定
$user->roles()->attach($admin->id); // 只能是角色ID
```

现在我们只需要为这些角色添加权限:

```php

$permissions = [
    'name' => 'post',
    'display_name' => 'Posts',//选填
    'description' => 'posts',//选填
    'p_id' => 0, //选填，默认为0
];

$permission = Entrust::createPermission($permissions);

$admin->attachPermission($permission); // 参数可以是EntrustPermission对象、数组或id
// 相当于 $admin->perms()->sync(array($permission->id));

```

#### 验证 Roles & Permissions

现在我们可以简单地检查角色和权限:

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->can('edit-user');   // false
$user->can('post'); // true
```

`hasRole()` 和 `canDo()` 验证角色和权限时都支持使用数组:

```php
$user->hasRole(['owner', 'admin']);       // true
$user->can(['edit-user', 'create-post']); // true
```

默认情况下，如果为用户提供了任何角色或权限，则该方法将返回true。
作为第二个参数传递“true”，指示该方法需要* *所有* * 角色或权限:

```php
$user->hasRole(['owner', 'admin']);             // true
$user->hasRole(['owner', 'admin'], true);       // false
$user->can(['edit-user', 'post']);       // true
$user->can(['edit-user', 'post'], true); // false
```

还可以通过 `Entrust` 这个类验证角色或权限 `canDo()` 和 `hasRole()`，通过Entrust验证时，验证的是当前登录的用户:

```php
Entrust::hasRole('role-name');
Entrust::canDo('permission-name');

// 等同于

Auth::user()->hasRole('role-name');
Auth::user()->canDo('permission-name');
```

您还可以使用占位符(通配符)来检查任何匹配的权限:

```php
// match any admin permission
$user->canDo("admin.*"); // true

// match any permission about users
$user->canDo("*_users"); // true
```


#### User ability
