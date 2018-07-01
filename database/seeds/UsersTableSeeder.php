<?php

use Illuminate\Database\Seeder;
use App\Models\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(50)->make();
        User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(1);
        $user->activated = true;
        $user->name = '弃少';
        $user->email = '727040917@qq.com';
        $user->password = bcrypt(123456);
        $user->is_admin = true; //第一个用户设置为管理员
        $user->save();
    }
}
