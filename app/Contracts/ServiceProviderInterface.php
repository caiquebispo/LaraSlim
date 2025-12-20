<?php

namespace LaraSlim\Contracts;

use Psr\Container\ContainerInterface;

/**
 * Service Provider Interface
 * 
 * All service providers should implement this interface.
 */
interface ServiceProviderInterface
{
    /**
     * Register any application services.
     * 
     * @param ContainerInterface $container The DI container
     * @return void
     */
    public function register(ContainerInterface $container): void;

    /**
     * Bootstrap any application services.
     * Called after all providers have been registered.
     * 
     * @param ContainerInterface $container The DI container
     * @return void
     */
    public function boot(ContainerInterface $container): void;
}
