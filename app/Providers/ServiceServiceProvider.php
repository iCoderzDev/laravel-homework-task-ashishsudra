<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserServiceInterface;
use App\Services\UserService;

class ServiceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }
}
