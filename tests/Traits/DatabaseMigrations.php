<?php

namespace Tests\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Database Migrations Trait
 * 
 * Automatically runs migrations for testing.
 */
trait DatabaseMigrations
{
    /**
     * Run migrations before each test
     */
    protected function runMigrations(): void
    {
        $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        
        if (!is_dir($migrationsPath)) {
            return;
        }

        $files = glob($migrationsPath . '/*.php');
        
        if (empty($files)) {
            return;
        }

        // Sort files to ensure correct order
        sort($files);

        foreach ($files as $file) {
            require_once $file;
            
            $className = $this->getMigrationClassName($file);
            
            if (class_exists($className)) {
                $migration = new $className();
                
                if (method_exists($migration, 'up')) {
                    $migration->up();
                }
            }
        }
    }

    /**
     * Rollback migrations after each test
     */
    protected function rollbackMigrations(): void
    {
        $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        
        if (!is_dir($migrationsPath)) {
            return;
        }

        $files = glob($migrationsPath . '/*.php');
        
        if (empty($files)) {
            return;
        }

        // Reverse order for rollback
        rsort($files);

        foreach ($files as $file) {
            require_once $file;
            
            $className = $this->getMigrationClassName($file);
            
            if (class_exists($className)) {
                $migration = new $className();
                
                if (method_exists($migration, 'down')) {
                    $migration->down();
                }
            }
        }
    }

    /**
     * Get migration class name from file
     */
    private function getMigrationClassName(string $file): string
    {
        $filename = basename($file, '.php');
        
        // Remove timestamp prefix (e.g., 2024_01_01_000000_create_users_table)
        $parts = explode('_', $filename);
        
        // Remove first 4 parts (timestamp: year, month, day, sequence)
        $nameparts = array_slice($parts, 4);
        
        // Convert to CamelCase
        $className = '';
        foreach ($nameparts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }

    /**
     * Refresh the database (rollback and re-migrate)
     */
    protected function refreshDatabase(): void
    {
        $this->rollbackMigrations();
        $this->runMigrations();
    }

    /**
     * Setup database for testing (call in setUp)
     */
    protected function setUpDatabase(): void
    {
        $this->runMigrations();
    }

    /**
     * Teardown database after testing (call in tearDown)
     */
    protected function tearDownDatabase(): void
    {
        // For in-memory SQLite, just drop all tables
        if ($this->isInMemoryDatabase()) {
            $this->dropAllTables();
        } else {
            $this->rollbackMigrations();
        }
    }

    /**
     * Check if using in-memory database
     */
    private function isInMemoryDatabase(): bool
    {
        return env('DB_DATABASE') === ':memory:';
    }

    /**
     * Drop all tables (for in-memory database cleanup)
     */
    private function dropAllTables(): void
    {
        try {
            $connection = Capsule::connection();
            $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            
            foreach ($tables as $table) {
                $connection->statement("DROP TABLE IF EXISTS {$table->name}");
            }
        } catch (\Exception $e) {
            // Ignore errors during cleanup
        }
    }
}
