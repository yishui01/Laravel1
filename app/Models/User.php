<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';

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



}
