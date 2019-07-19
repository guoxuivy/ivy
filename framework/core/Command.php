<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework 加特技 duang
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;

abstract class Command 
{
    /** @var  Resource */
    static $stdout = null;

    public $name = '';
    public $description = '';

    public function __construct()
    {
        $this->configure();
    }

    protected function setName($name){
        $this->name = $name;
        return $this;
    }

    protected function setDescription($description){
        $this->description = $description;
        return $this;
    }

    public static function openOutputStream()
    {
        if(!self::$stdout){
            if (!self::hasStdoutSupport()) {
                return fopen('php://output', 'w');
            }
            self::$stdout = @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
        }
        return self::$stdout;
    }

    public static function isRunningOS400()
    {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];
        return false !== stripos(implode(';', $checks), 'OS400');
    }

    /**
     * 当前环境是否支持写入控制台输出到stdout.
     *
     * @return bool
     */
    public static function hasStdoutSupport()
    {
        return false === self::isRunningOS400();
    }

    /**
     * 将消息写入到输出。
     * @param string $message 消息
     * @param bool   $newline 是否另起一行
     */
    public static function write($message, $newline=false)
    {
        $stream = self::openOutputStream();
        if (false === @fwrite($stream, $message . ($newline ? PHP_EOL : ''))) {
            throw new \CException('Unable to write output.');
        }
        fflush($stream);
    }

    /**
     * 将消息写入到输出。
     * @param string $message 消息
     * @param bool   $newline 是否另起一行
     */
    public static function writeln($message)
    {
        self::write($message,true);
    }

    // 注入命令说明
    abstract public function configure();
    // 命令执行入口
    abstract public function execute($input);
}