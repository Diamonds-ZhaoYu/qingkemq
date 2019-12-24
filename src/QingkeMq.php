<?php
// +----------------------------------------------------------------------
// |  用于轻课调用队列服务使用
// |
// +----------------------------------------------------------------------
// | Copyright (c) https://admuch.txbapp.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhaoyu <9641354@qq.com>
// +----------------------------------------------------------------------
// | Date: 2019/12/24 10:38 上午
// +----------------------------------------------------------------------
namespace Qk\QingkeMq;

class QingkeMq
{
    /**
     * @var string 消息ID
     */
    protected $_messageId     = NULL;

    /**
     * @var string 请求ID
     */
    protected $_requestId     = NULL;

    /**
     * @var int   时间戳
     */
    protected $_timestamp     = NULL;

    /**
     * @var string  回调地址
     */
    protected $_callbackUrl   = "";

    /**
     * @var bool 是否重试，默认重试
     */
    protected $_retry         = TRUE;

    /**
     * @var int 重试次数
     */
    protected $_retryNum      = 3;

    /**
     * @var int 最大重试次数
     */
    protected $_maxRetryNum   = 3;

    /**
     * @var string task名称
     */
    protected $_taskName      = NULL;

    /**
     * @var string 队列名称
     */
    protected $_taskQueueName = "qingke_toq_queue";

    /**
     * @var array 发送参数
     */
    protected $_args          = [];


    /**
     * @var string redis链接地址
     */
    protected $_rdsHost       = '127.0.0.1';


    /**
     * @var string redis端口
     */
    protected $_rdsPort       = '6379';

    /**
     * @var string redis密码
     */
    protected $_rdsPwd        = '';

    /**
     * @var int  redis数据库编号
     */
    protected $_rdsDbs        = 12;

    /**
     * 设置数据库
     * @param int $rdsDbs
     */
    public function setRdsDbs(int $rdsDbs): void
    {
        $this->_rdsDbs = $rdsDbs;
    }

    /**
     * 设置redis地址
     * @param string $rdsHost
     */
    public function setRdsHost(string $rdsHost): void
    {
        $this->_rdsHost = $rdsHost;
    }

    /**
     * 设置redis端口
     * @param string $rdsPort
     */
    public function setRdsPort(string $rdsPort): void
    {
        $this->_rdsPort = $rdsPort;
    }

    /**
     * 设置redis密码
     * @param string $rdsPwd
     */
    public function setRdsPwd(string $rdsPwd): void
    {
        $this->_rdsPwd = $rdsPwd;
    }



    /**
     * 队列调用初始化
     * @param string $env  环境变量
     */
    public function __construct($env = '')
    {
        if ($env == 'sandbox') {

        }
    }

    /**
     * @param string $requestId 设置请求ID
     */
    public function setRequestId(string $requestId): void
    {
        $this->_requestId = $requestId;
    }

    /**
     * 设置回调地址
     * @param string $callbackUrl
     * @throws \Exception
     */
    public function setCallbackUrl(string $callbackUrl): void
    {
        if(!preg_match('/http(s)?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$callbackUrl)){
            throw new \Exception("Please input url!", 500);
        }
        $this->_callbackUrl = $callbackUrl;
    }

    /**
     * 设置是否重试  true-重试  false-不重试
     * @param bool $retry
     */
    public function setRetry(bool $retry): void
    {
        $this->_retry = $retry;
    }

    /**
     * 设置重试次数
     * @param int $retryNum 重试次数
     */
    public function setRetryNum(int $retryNum): void
    {
        $this->_retryNum = $retryNum;
    }

    /**
     * 设置最大重试次数
     * @param int $maxRetryNum 最大重试次数
     */
    public function setMaxRetryNum(int $maxRetryNum): void
    {
        $this->_maxRetryNum = $maxRetryNum;
    }

    /**
     * 设置任务名称
     * @param string $taskName 任务名称
     */
    public function setTaskName(string $taskName): void
    {
        $this->_taskName = $taskName;
    }

    /**
     * 设置队列名称
     * @param string $taskQueueName 队列名称
     */
    public function setTaskQueueName(string $taskQueueName): void
    {
        $this->_taskQueueName = $taskQueueName;
    }

    /**
     * 队列参数
     * @param array $args 参数
     */
    public function setArgs(array $args): void
    {
        $this->_args = $args;
    }

    /**
     * 发送队列消息
     * @return bool
     * @throws \Exception
     */
    public function send(): bool
    {
        if (empty($this->_taskName)) {
            throw new \Exception("Please input taskName!", 500);
        }

        if (empty($this->_requestId)) {
            throw new \Exception("Please input requestId!", 500);
        }

        $this->_messageId = UUID4::genUUID();
        $this->_timestamp = time();

        $sendParam = [
            'MessageId'     => $this->_messageId,
            'RequestId'     => $this->_requestId,
            'Timestamp'     => $this->_timestamp,
            'CallbackUrl'   => $this->_callbackUrl,
            'Retry'         => $this->Retry,
            'RetryNum'      => $this->RetryNum,
            'MaxRetryNum'   => $this->MaxRetryNum,
            'TaskName'      => $this->TaskName,
            'TaskQueueName' => $this->TaskQueueName,
            'Args'          => json_encode($this->_args),
        ];

        $args = json_encode($sendParam);

        return RedisMq::Factory($this->_rdsHost, $this->_rdsPort, $this->_rdsPwd, $this->_rdsDbs)->rpush($this->TaskQueueName, $args);
    }
}