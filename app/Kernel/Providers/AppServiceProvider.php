<?php

namespace LaraSlim\Kernel\Providers;

use Psr\Container\ContainerInterface;

class AppServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        $configPath = __DIR__ . '/../../../config/app.php';
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Arquivo de configuração não encontrado: {$configPath}");
        }
        
        $config = require $configPath;
        
        $container->set('config', $config);
        
        $this->registerServices($container, $config);
    }

    protected function registerServices(ContainerInterface $container, array $config): void
    {
        if (isset($config['database'])) {
            $container->set('database.config', $config['database']);
        }
        
        if (isset($config['app'])) {
            $container->set('app.config', $config['app']);
        }
        
    }
}