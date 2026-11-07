<?php

namespace App\Providers;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ListingRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VegetableTypeRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ListingRepositoryInterface::class => ListingRepository::class,
        CartRepositoryInterface::class => CartRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        VegetableTypeRepositoryInterface::class => VegetableTypeRepository::class,
    ];
}