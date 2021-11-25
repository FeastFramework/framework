<?php

namespace Psr\Log;

/**
 * Describes log levels. Added public visibility. See https://www.php-fig.org/psr/psr-3/
 */
class LogLevel
{
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';
}
