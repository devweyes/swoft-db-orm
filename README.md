## Swoft ORM 模型关联

>### <a href="#1">1.简介</a>
>
>### <a href="#2">2.定义关联</a>
>>#### <a href="#2_1">2.1.一对一</a>
>>#### <a href="#2_2">2.2.一对多</a>
>>#### <a href="#2_3">2.3.一对多(反向)</a>
>>#### <a href="#2_4">2.4.多对多</a>
>
>### <a href="#3">3.查询关联</a>
>>#### <a href="#3_1">3.1.存在关联</a>
>>#### <a href="#3_2">3.2.筛选关联</a>
>
>### <a href="#4">4.预加载</a>
>>#### <a href="#4_1">4.1.普通预加载</a>
>>#### <a href="#4_2">4.2.延迟预加载</a>
>
>### <a href="#5">5.插入 & 更新关联模型</a>
>>#### <a href="#5_1">5.1.save 方法</a>
>>#### <a href="#5_2">5.3.create 方法</a>
>>#### <a href="#5_1">5.3.更新 Belongs To 关联</a>
>>#### <a href="#5_2">5.4.多对多关联</a>


### <a name="1">1.简介</a>

数据库表通常相互关联。例如，一篇博客文章可能有很多评论，或者一个订单对应一个下单用户。 ORM 让这些关联的管理和使用变得简单，并支持多种类型的关联

### <a name="2">2.定义关联</a>

> 一般只需定义两个注解，及Getter Setter。
> @RelationPassive() 为切面注解，如需预加载，必不可缺。

#### <a name="2_1">2.1 一对一</a>
<table>
    <tr>
        <th>字段</th>
        <th>是否必填</th>
        <th>描述</th>
    </tr>
    <tr>
        <td>entity</td>
        <td>是</td>
        <td>目标实体</td>
    </tr>
    <tr>
       <td>foreign</td>
       <td>是</td>
       <td>目标关联字段(xxx_id获取 xxx表示本实体转蛇形)</td>
    </tr>
    <tr>
        <td>owner</td>
        <td>否</td>
        <td>本字段(keyName主键获取)</td>
    </tr>
<table>

```php
<?php declare(strict_types=1);

namespace App\Model\Entity;

use Swoft\Orm\Annotation\Mapping\HasOne;
use Swoft\Orm\Annotation\Mapping\RelationPassive;
use Swoft\Db\Eloquent\Model;
/**
 *
 * Class Api
 *
 * @since 2.0
 *
 * @Entity(table="users")
 */
class User extends Model
{
    /**
     * @HasOne(
     *     entity=Role::class,
     *     foreign="id",
     *     owner="user_id"
     * )
     * @var mixed
     */
    private $roles;

    /**
     * @param $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * 获取一个角色
     * @RelationPassive()
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }
 }
```

#### <a name="2_2">2.2 一对多</a>

<table>
    <tr>
        <th>字段</th>
        <th>是否必填</th>
        <th>描述</th>
    </tr>
    <tr>
        <td>entity</td>
        <td>是</td>
        <td>目标实体</td>
    </tr>
    <tr>
       <td>foreign</td>
       <td>是</td>
       <td>目标关联字段(xxx_id获取 xxx表示本实体转蛇形)</td>
    </tr>
    <tr>
        <td>owner</td>
        <td>否</td>
        <td>本字段(keyName主键获取)</td>
    </tr>
<table>

```php
<?php declare(strict_types=1);

namespace App\Model\Entity;

use Swoft\Orm\Annotation\Mapping\HasMany;
use Swoft\Orm\Annotation\Mapping\RelationPassive;
use Swoft\Db\Eloquent\Model;
/**
 *
 * Class Api
 *
 * @since 2.0
 *
 * @Entity(table="users")
 */
class User extends Model
{
    /**
     * @HasMany(
     *     entity=Role::class,
     *     foreign="id",
     *     owner="user_id"
     * )
     * @var mixed
     */
    private $roles;

    /**
     * @param $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * 获取专属的多个角色
     * @RelationPassive()
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }
 }
```

#### <a name="2_3">2.3 一对多（反向）</a>

<table>
    <tr>
        <th>字段</th>
        <th>是否必填</th>
        <th>描述</th>
    </tr>
    <tr>
        <td>entity</td>
        <td>是</td>
        <td>目标实体</td>
    </tr>
    <tr>
       <td>foreign</td>
       <td>是</td>
       <td>本表字段(xxx_id获取 xxx表示关系名，id表示目标主键)</td>
    </tr>
    <tr>
        <td>owner</td>
        <td>否</td>
        <td>目标表字段(keyName主键获取)</td>
    </tr>
<table>

```php
<?php declare(strict_types=1);

namespace App\Model\Entity;

use Swoft\Orm\Annotation\Mapping\BelongsTo;
use Swoft\Orm\Annotation\Mapping\RelationPassive;
use Swoft\Db\Eloquent\Model;
/**
 *
 * Class Api
 *
 * @since 2.0
 *
 * @Entity(table="roles")
 */
class Role extends Model
{
    /**
     * @BelongsTo(
     *     entity=User::class,
     *     foreign="user_id",
     *     owner="id"
     * )
     * @var mixed
     */
    private $users;

    /**
     * @param $roles
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * 获取角色的账号
     * @RelationPassive()
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }
 }
```

#### <a name="2_4">2.4 多对多</a>
<table>
    <tr>
        <th>字段</th>
        <th>是否必填</th>
        <th>描述</th>
    </tr>
    <tr>
        <td>entity</td>
        <td>是</td>
        <td>目标实体</td>
    </tr>
    <tr>
        <td>pointEntity</td>
        <td>是</td>
        <td>中间实体</td>
    </tr>
    <tr>
        <td>foreignPivot</td>
           <td>是</td>
           <td>中间实体关联本字段的字段(xxx_id获取)</td>
        </tr>
        <tr>
            <td>ownerPivot</td>
            <td>否</td>
            <td>中间实体关联目标字段的字段(xxx_id获取)</td>
        </tr>
    <tr>
       <td>foreign</td>
       <td>否</td>
       <td>目标关联字段(id获取)</td>
    </tr>
    <tr>
        <td>owner</td>
        <td>否</td>
        <td>本字段(id获取)</td>
    </tr>
<table>

```php
<?php declare(strict_types=1);

namespace App\Model\Entity;

use Swoft\Orm\Annotation\Mapping\BelongsToMany;
use Swoft\Orm\Annotation\Mapping\RelationPassive;
/**
 *
 * Class Api
 *
 * @since 2.0
 *
 * @Entity(table="users")
 */
class User extends Model
{
    /**
     * @BelongsToMany(
     *     entity=Role::class,
     *     pointEntity=UserRole::class,
     *     foreignPivot="api_id",
     *     ownerPivot="cat_id",
     *     foreign="id",
     *     owner="id"
     * )
     * @var mixed
     */
    private $roles;

    /**
     * @param $cats
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * 获取多个角色
     * @RelationPassive()
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
```
### <a name="3">3.查询关联</a>

#### <a name="3_1">3.1 存在关联</a>

当通过模型获取数据时，你可能希望限制在一个已存在的关系上。比如说，你想要获取至少包含一条评论的博客文章。你就应该这样做，使用针对关联关系的 has and orHas 方法：

```php
// 获取至少有一条评论的文章
$posts = App\Post::has('comments')->get();
```
```php
// 获取至少有3
$posts = App\Post::has('comments', '>=', 3)->get();
```
```php
// 获取至少有一条评论的文章，并加载评论的投票信息
$posts = App\Post::has('comments.votes')->get();
```

#### a name="3_2">3.2 筛选关联</a>

如果你想要做更多特性，你还可以使用 whereHas 和  orWhereHas 方法，在方法中，你可以指定 「where」 条件在你的 has 语句之中。这些方法允许你在关联查询之中添加自定义的条件约束，比如检查评论的内容：

```php
// 获取所有至少有一条评论的文章且评论内容以 foo 开头
$posts = App\Post::whereHas('comments', function ($query) {
    $query->where('content', 'like', 'foo%');
})->get();
```

### <a name="4">4.预加载</a>

#### <a name="4_1">4.1 普通预加载</a>
```php
//获取书作者
$books = App\Book::with('author')->get();
foreach ($books as $book) {
        echo $book->getAuthor()->getName;
}
```

```php
//或书的作者及作者联系人
$books = App\Book::with('author.contacts')->get();
foreach ($books as $book) {
        echo $book->getAuthor()->getContacts()->getName;
}
```

```php
//获取书及作者 并且作者为tom,只需要作者的id name信息，且按照作者id排序
$users = App\Book::with(['author' => function ($query) {
        $query->where('name', '=', '%tom%')
            ->select('id','name')
            ->orderBy('id');
}])->get();
```

#### <a name="4_2">4.2 延迟预加载</a>

有可能你还希望在模型加载完成后在进行预加载。举例来说，如果你想要动态的加载关联数据，那么 load 方法对你来说会非常有用：

```php
$books = App\Book::all();

if ($someCondition) {
        $books->load('author', 'publisher');
}
```
```php
$books->load(['author' => function ($query) {
        $query->orderBy('published_date', 'asc');
}]);
```

