<?php

namespace LaraSlim\Kernel\Providers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;

class DatabaseServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        
        $config = $container->has('config') ? $container->get('config') : [];
        
        $dbConfig = $config['database']['connections'][$config['database']['default']] ?? [];

      
        if (empty($dbConfig)) {
            $connection = env('DB_CONNECTION', 'sqlite');

            if ($connection === 'sqlite') {
                $dbConfig = [
                    'driver' => 'sqlite',
                    'database' => env('DB_DATABASE', __DIR__ . '/../../../storage/database.sqlite'),
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ];
                
                $this->createSqliteDatabase($dbConfig['database']);
            } else {
                $dbConfig = [
                    'driver' => env('DB_CONNECTION', 'mysql'),
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_DATABASE', 'laraslim'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ];
            }
        }

        $capsule = new Capsule();
        $capsule->addConnection($dbConfig);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        $container->set(Capsule::class, function () use ($capsule) {
            return $capsule;
        });

        $container->set('db', function () use ($capsule) {
            return $capsule;
        });
    }

    private function createSqliteDatabase(string $path): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        
        if (!file_exists($path)) {
            touch($path);
            chmod($path, 0664);
        }
    }
}