## Swoft ORM 模型关联

>### <a href="#1">1.简介</a>
>
>### <a href="#2">2.定义关联</a>	
>>#### <a href="#2_1">2.1.一对一</a>
>>#### <a href="#2_1">2.1.一对多</a>
>>#### <a href="#2_1">2.1.一对多(反向)</a>
>>#### <a href="#2_1">2.1.多对多</a>
>
>### <a href="#3">3.查询关联</a>	
>>#### <a href="#3_1">3.1.存在关联</a>
>>#### <a href="#3_2">3.2.筛选关联</a>
>
>### <a href="#4">4.预加载</a>	
>>#### <a href="#4_1">4.1.为预加载添加约束</a>
>>#### <a href="#4_2">4.2.延迟预加载</a>
>
>### <a href="#5">5.插入 & 更新关联模型</a>	
>>#### <a href="#5_1">5.1.save 方法</a>
>>#### <a href="#5_2">5.3.create 方法</a>
>>#### <a href="#5_1">5.3.更新 Belongs To 关联</a>
>>#### <a href="#5_2">5.4.多对多关联</a>
>
 
 
 
>
>
### <a name="1">1.简介</a>
数据库表通常相互关联。例如，一篇博客文章可能有很多评论，或者一个订单对应一个下单用户。 ORM 让这些关联的管理和使用变得简单，并支持多种类型的关联

### <a name="2">2.定义关联</a>

> 一般只需定义两个注解，及Getter Setter。
> @RelationPassive() 为切面注解，如需预加载，必不可缺。

**一对一**
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
       <td>目标关联字段(xxx_id获取)</td>
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
     *     owner="role_id"
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

**一对多**

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
       <td>目标关联字段(xxx_id获取)</td>
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
     *     foreign="user_id",
     *     owner="id"
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

**一对多(反向)**

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
       <td>目标关联字段(xxx_id获取)</td>
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
     *     foreign="id",
     *     owner="user_id"
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

**多对多**
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



