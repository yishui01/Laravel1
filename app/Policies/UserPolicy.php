<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $currentUser, User $user)
    {
        /**
         * 我们并不需要检查 $currentUser 是不是 NULL。未登录用户框架会自动为其所有权限 返回 false；
         *调用时，默认情况下，我们 不需要 传递当前登录用户至该方法内，
         * 因为框架会自动加载当前登录用户（接着看下去，后面有例子）；
         */
        return $currentUser->id === $user->id;
    }
    public function destroy(User $currentUser, User $user)
    {
        /*删除用户的动作，有两个逻辑需要提前考虑：
        1、只有当前登录用户为管理员才能执行删除操作；
        2、删除的用户对象不是自己（即使是管理员也不能自己删自己）。
        */
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }
}
