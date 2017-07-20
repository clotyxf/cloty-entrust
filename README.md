# Entrust (Laravel 5 Package)

cloty-entrust 是一个基于laravel5的简单验证角色权限的插件


## 内容

- [安装](#安装)
- [配置](#配置)
    - [表结构说明](#表结构说明)
    - [Models](#Models)
        - [EntrustRole](#EntrustPermission)
        - [EntrustPermission](#EntrustPermission)
        - [User](#user)
- [使用](#使用)
    - [概念](#概念)
        - [验证Roles&Permissions](#验证Roles&Permissions)
        - [ability](#ability)
    - [Blade](#blade)
    - [Middleware](#middleware)

## 安装

1) 安装entrust的方式很简单，直接添加到laravel5的composer.json安装即可。或者通过命令 `composer update` 安装:

```json
"cloty/cloty-entrust": "~1.0"
```

2) 在 `config/app.php`中的 `providers` 引入服务提供者:

```php
Cloty\Entrust\Providers\EntrustServiceProvider::class,
```

3) 同样的在 `config/app.php` 中的 `aliases ` 引入:

```php
'Entrust'   => Cloty\Entrust\Facades\EntrustFacade::class,
```

4) 运行下面的命令，发布包配置文件 `config/cloty-entrust.php` 和 `migrations`:

```shell
php artisan vendor:publish
```

5) 如果你需要引入中间件 [Middleware](#middleware) (Laravel 5.*) 时，须在 `app\Http\Kernel.php` 中添加以下内容:

```php
    'entrust.role' => \Cloty\Entrust\Middleware\EntrustRole::class,
    'entrust.permission' => \Cloty\Entrust\Middleware\EntrustPermission::class,
    'entrust.ability' => \Cloty\Entrust\Middleware\EntrustAbility::class,
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

#### EntrustPermission

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
$user->entrustRoles()->attach($admin->id); // 只能是角色ID
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
添加非p_id = 0的权限时，需指明上级p_id，子权限的命名方式 `name` 必须是用父级命名做前缀，如 `post_create`。
创建子权限时如不写前缀`post_`，Entrust将自动补全。多级子权限亦同。如 `post_create_detail` ...

#### 验证Roles&Permissions

现在我们可以简单地检查角色和权限:

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->canDo('post_add');   // false
$user->canDo('post'); // true
```

`hasRole()` 和 `canDo()` 验证角色和权限时都支持使用数组:

```php
$user->hasRole(['owner', 'admin']);       // true
$user->canDo(['post_add', 'post']); // true
```

默认情况下，如果为用户提供了任何角色或权限，则该方法将返回true。
作为第二个参数传递“true”，指示该方法需要* *所有* * 角色或权限:

```php
$user->hasRole(['owner', 'admin']);             // true
$user->hasRole(['owner', 'admin'], true);       // false
$user->canDo(['post_add', 'post']);       // true
$user->canDo(['post_add', 'post'], true); // false
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


#### User ability & Entrust ability

可以使用 `ability` 功能进行更高级的权限验证。 它包含三个参数（roles，permissions，options）：

- `roles` 需要验证的角色.
- `permissions` 需要验证的权限.

`roles` 或 `permissions` 变量可以是逗号分隔的字符串或数组：

```php
$user->ability(['admin', 'cloty'], ['post', 'post_create', 'post_create_detail']);

// 或

$user->ability('admin,owner', 'post,post_create,post_create_detail');
```
这将检查用户是否具有任何提供的角色和权限。 在这种情况下，它将返回true，因为用户是 `admin` 并具有`post_create`权限。

第三个参数 `options`为选填项，传递参数为数组:

```php
$options =[
    'validate_all' => true | false (Default: false),
    'return_type'  => boolean | array | both (Default: boolean)
    ];
```

- `validate_all` 该参数值为boolean，如果设置为 `true`,则验证 所有 `roles`或`permissions` 都匹配时，则返回`true`.
- `return_type`  指定返回的值格式.

例子:

```php
$options = [
    'validate_all' => true,
    'return_type' => 'both'
    ];

list($validate, $allValidations) = $user->ability(
    ['admin', 'owner'],
    ['post', 'post_create'],
    $options
);

var_dump($validate);
// bool(false)

var_dump($allValidations);
// array(4) {
//     ['admin'] => bool(true)
//     ['owner'] => bool(false)
//     ['post'] => bool(true)
//     ['post_create'] => bool(false)
// }

```
`Entrust`中的`ability()`提供快捷验证当前登录的用户方法：
```php
Entrust::ability('admin,owner', 'post,post_create');

// 相当于

Auth::user()->ability('admin,owner', 'post,post_create');
```

### Blade

Entrust提供三个Blade模板指令。你可以在提供的指令中将参数直接传递到相应的方法：

```php
@role('admin')
    <p>具有`admin` 角色的用户可以看到这一点.渲染时被替换成
    \Entrust::hasRole('admin')</p>
@endrole

@permission('post')
    <p>具有给定权限的用户可以看到这一点.渲染时被替换成
    \Entrust::canDo('post').</p>
@endpermission

@ability('admin,owner', 'post,post_create')
    <p>具有给定abilities的用户可以看到. 渲染时被替换成
    \Entrust::ability('admin,owner', 'post,post_create')</p>
@endability
```

### Middleware

可以使用中间件`entrust.role`或`entrust.permission`过滤路由和路由组:

```php
Route::group(['prefix' => 'admin', 'middleware' => ['entrust.role:admin']], function() {
    Route::get('/', 'AdminController@welcome');
    Route::get('/manage', ['middleware' => ['entrust.permission:manage_admins'], 'uses' => 'AdminController@manageAdmins']);
});
```
可以使用|符号作为* OR *运算符:
```php
'middleware' => ['entrust.role:admin|root']
```
要使用* AND *验证时只需使用多个中间件实例:
```php
'middleware' => ['entrust.role:owner', 'entrust.role:writer']
```
对于更复杂的情况，可以使用 `entrust.ability` 中间件.使用时可传递3个参数: roles, permissions, validate_all
```php
'middleware' => ['entrust.ability:admin|owner,post|post_create,true']
```
