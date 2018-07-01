<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema; //手动加上去的，原来文件中没有
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //自己修改的，为了解决数据库迁移的时候报错
         Schema::defaultStringLength(191);
        Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
