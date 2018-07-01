<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';

    //在用户模型中，指明一个用户拥有多条微博，一对多。
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * The attributes that are mass assignable.
     *允许被更新的字段
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *对用户密码或其它敏感信息在用户实例通过数组或 JSON 显示时进行隐藏，则可使用 hidden 属性：
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //关联粉丝模型 我们可以通过 followers 来获取粉丝关系列表，如：$user->followers();
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    //关联自己关注的列表 $user->followings();
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    /**添加关注
     * @param $user_ids
     */
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    /**取关
     * @param $user_ids
     */
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    /**判断你有没有关注这个用户
     * @param $user_id 查询的用户
     * @return mixed
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }

    /**获取用户自己以及关注的人的所有微博
     * @return $this
     */
    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
       
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    /**生成全球通用的头像
     * @param string $size
     * @return string
     */
    public function gravatar($size = '100')
    {
        /*
        为 gravatar 方法传递的参数 size 指定了默认值 100；
        通过 $this->attributes['email'] 获取到用户的邮箱；
        使用 trim 方法剔除邮箱的前后空白内容；
        用 strtolower 方法将邮箱转换为小写；
        将小写的邮箱使用 md5 方法进行转码；
        将转码后的邮箱与链接、尺寸拼接成完整的 URL 并返回；
        */

        /*$hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";*/
        return "https://avatars3.githubusercontent.com/u/20850040?s=460&v=4";
    }

    /*
     * boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
       现在，我们需要更新模型工厂，将生成的假用户和第一位用户都设为已激活状态。
    */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    //发送密码重置邮件
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }



}
