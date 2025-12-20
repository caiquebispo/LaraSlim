<?php

namespace LaraSlim\Kernel\Providers;

use DI\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logging Service Provider
 * 
 * Provides a simple PSR-3 compatible logger.
 * For production use, consider using Monolog for more features.
 */
class LoggingServiceProvider
{
    public function register(Container $container): void
    {
        $container->set(LoggerInterface::class, function () {
            return new SimpleLogger();
        });

        $container->set('logger', function () use ($container) {
            return $container->get(LoggerInterface::class);
        });
    }
}

/**
 * Simple PSR-3 Logger Implementation
 * 
 * A lightweight logger that writes to files or stderr.
 */
class SimpleLogger implements LoggerInterface
{
    private string $channel;
    private string $level;
    private string $logPath;

    private array $levels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    public function __construct()
    {
        $this->channel = env('LOG_CHANNEL', 'file');
        $this->level = env('LOG_LEVEL', 'debug');
        $this->logPath = $this->getLogPath();
    }

    private function getLogPath(): string
    {
        $storagePath = defined('LARASLIM_STORAGE_PATH') 
            ? LARASLIM_STORAGE_PATH 
            : dirname(__DIR__, 3) . '/storage';
        
        $logsPath = $storagePath . '/logs';
        
        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }

        return $logsPath . '/laraslim-' . date('Y-m-d') . '.log';
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        // Check if this level should be logged
        if (!$this->shouldLog($level)) {
            return;
        }

        $formattedMessage = $this->formatMessage($level, $message, $context);

        if ($this->channel === 'stderr') {
            $this->writeToStderr($formattedMessage);
        } elseif ($this->channel === 'null') {
            // Null channel - do nothing
            return;
        } else {
            $this->writeToFile($formattedMessage);
        }
    }

    private function shouldLog(string $level): bool
    {
        $currentLevelPriority = $this->levels[$this->level] ?? 7;
        $messageLevelPriority = $this->levels[$level] ?? 7;

        return $messageLevelPriority <= $currentLevelPriority;
    }

    private function formatMessage(string $level, string|\Stringable $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);
        $message = (string) $message;

        // Interpolate context into message
        $message = $this->interpolate($message, $context);

        // Add context as JSON if present
        $contextJson = !empty($context) ? ' ' . json_encode($context) : '';

        return "[{$timestamp}] {$level}: {$message}{$contextJson}" . PHP_EOL;
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $val;
            }
        }
        return strtr($message, $replace);
    }

    private function writeToFile(string $message): void
    {
        file_put_contents($this->logPath, $message, FILE_APPEND | LOCK_EX);
    }

    private function writeToStderr(string $message): void
    {
        fwrite(STDERR, $message);
    }
}
