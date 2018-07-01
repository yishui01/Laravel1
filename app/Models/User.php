<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;

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

    /**获取用户自己以及关注的人的所有微博
     * @return $this
     */
    public function feed()
    {
        return $this->statuses()
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
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
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
