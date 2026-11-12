<?php

namespace App\Providers;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\ListingPhotoRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RatingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ChatRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\ListingPhotoRepository;
use App\Repositories\Eloquent\ListingRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OrganizationRepository;
use App\Repositories\Eloquent\RatingRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VegetableTypeRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ListingRepositoryInterface::class, ListingRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VegetableTypeRepositoryInterface::class, VegetableTypeRepository::class);
        $this->app->bind(ListingPhotoRepositoryInterface::class, ListingPhotoRepository::class);
        $this->app->bind(RatingRepositoryInterface::class, RatingRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }
}