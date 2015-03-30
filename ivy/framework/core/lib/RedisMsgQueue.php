<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy * @since 1.0 
 * 基于Redis的消息队列封装
 */
namespace Ivy\core\lib;
class RedisMsgQueue implements IMessageQueue
{

    protected $redis_server;

    protected $server;

    protected $port;

    /**
     * @var 消息队列标志
     */
    protected $key;

    /**
     * 构造队列，创建redis链接
     * @param $server_config
     * @param $key
     * @param bool $p_connect
     */
    public function __construct($server_config = array('IP' => '127.0.0.1', 'PORT' => '6379'), $key = 'redis_message_queue', $p_connect = false)
    {
        if (empty($key))
            throw new \Exception('message queue key can not be empty');

        $this->server = $server_config['IP'];
        $this->port = $server_config['PORT'];
        $this->key = $key;

        $this->check_environment();
        if ($p_connect) {
            $this->pconnect();
        } else {
            $this->connect();
        }
    }

    /**
     * 析构函数，关闭redis链接，使用长连接时，最好主动调用关闭
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 短连接
     */
    private function connect()
    {
        $this->redis_server = new \Redis();
        $this->redis_server->connect($this->server, $this->port);
    }

    /**
     * 长连接
     */
    public function pconnect()
    {
        $this->redis_server = new \Redis();
        $this->redis_server->pconnect($this->server, $this->port);
    }

    /**
     * 关闭链接
     */
    public function close()
    {
        $this->redis_server->close();
    }

    /**
     * 向队列插入一条信息  右入左出
     * @param $message
     * @return mixed
     */
    public function put($message)
    {
        return $this->redis_server->rPush($this->key, $message);
    }

    /**
     * 向队列中插入一串信息
     * @param $message
     * @return mixed
     */
    public function puts(){
        $params = func_get_args();
        $message_array = array_merge(array($this->key), $params);
        return call_user_func_array(array($this->redis_server, 'rPush'), $message_array);
    }

    /**
     * 从队列顶部获取一条记录
     * @return mixed
     */
    public function get()
    {
        return $this->redis_server->lPop($this->key);
    }

    /**
     * 选择数据库，可以用于区分不同队列
     * @param $database
     */
    public function select($database)
    {
        $this->redis_server->select($database);
    }

    /**
     * 获得队列状态，即目前队列中的消息数量
     * @return mixed
     */
    public function size()
    {
        return $this->redis_server->lSize($this->key);
    }

    /**
     * 获取某一位置的值，不会删除该位置的值
     * @param $pos
     * @return mixed
     */
    public function view($pos)
    {
        return $this->redis_server->lGet($this->key, $pos);
    }

    /**
     * 检查Redis扩展
     * @throws Exception
     */
    protected function check_environment()
    {
        if (!\extension_loaded('redis')) {
            throw new \Exception('Redis extension not loaded');
        }
    }
}