<?php
namespace ifu\helper;
use think\facade\Env;
use think\facade\App;

require_once("src/aliyun-sls-sdk/Log_Autoload.php");

/**
 * 本地化调试输出到文件
 */
class Log
{
    protected $config = [
        'endpoint'          => 'http://cn-beijing.sls.aliyuncs.com/',
        'access_key_id'     => '',
        'access_key_secret' => '',
        'project'           => '',
        'logstore'          => '',
        'source'            => '',
        'topic'             => 'default',
        'json'              => false,
        'json_options'      => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ];
    protected $client;

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log): bool
    {
        $info = [];

        // 日志信息封装
        $time = time();

        foreach ($log as $type => $val) {
            $message = [];
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }

                $logItem = new \Aliyun_Log_Models_LogItem();
                $logItem->setTime($time);

                $contents = config('log.aliyun_log_json') ?
                ['content' => json_encode(['type' => $type, 'msg' => $msg], config('log.aliyun_log_json_options'))] :
                ['type' => $type, 'msg' => $msg];
                $logItem->setContents($contents);

                $message[] = $logItem;
            }
            $info = $message;
        }
        if ($info) {
            return $this->write($info);
        }

        return true;
    }
    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function apisave(array $log): bool
    {
        $info = [];
        // 日志信息封装
        $time = time();
        $message = [];
        foreach ($log as $msg) {
            if (!is_string($msg)) {
                $msg = var_export($msg, true);
            }
            $logItem = new \Aliyun_Log_Models_LogItem();
            $logItem->setTime($time);
            $runtime = (number_format(microtime(true), 8, '.', '') - THINK_START_TIME) ?: 0.00000001;
            $reqs = number_format(1 / number_format($runtime, 8), 2);
            $contents =[
                'level' => $msg['level'], 
                'apiUrl' => $msg['apiUrl'], 
                'urlData' => $msg['urlData'], 
                'curl_error' => $msg['curl_error'], 
                'differenct_time' =>  number_format($runtime, 6), 
                'response' => $msg['response']
            ];
            $logItem->setContents($contents);
            $message[] = $logItem;
        }
        $info = $message;
        if ($info) {
            return $this->write($info);
        }
        return true;
    }
    /**
     * 日志写入
     * @access protected
     * @param array  $message     日志信息
     * @return bool
     */
    protected function write(array $message): bool
    {
        $client = new \Aliyun_Log_Client(config('log.aliyun_log_endpoint'), config('log.aliyun_log_access_key_id'), config('log.aliyun_log_access_key_secret'));
        $req = new \Aliyun_Log_Models_PutLogsRequest(config('log.aliyun_log_project'), config('log.aliyun_log_logstore'), config('log.aliyun_log_topic'), config('log.aliyun_log_source'), $message);
        $client->putLogs($req);
        // var_dump($req);exit();
        return true;
    }
}


?>