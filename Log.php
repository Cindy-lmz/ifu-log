<?php
namespace ifu\helper;

require_once("aliyun-sls-sdk/Log_Autoload.php");

/**
 * 本地化调试输出到文件
 */
class Log
{
    protected $config = [
        'time_format' => 'Y-m-d H:i:s',
        'file_size' => 2097152,
        'path' => LOG_PATH,
        'endpoint' => 'cn-hangzhou.sls.aliyuncs.com',
        'accessKeyId' => '',
        'accessKey' => '',
        'project' => '',
    ];
    protected $client;

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        $endpoint = $this->config['endpoint']; // 选择与上面步骤创建Project所属区域匹配的Endpoint
        $accessKeyId = $this->config['accessKeyId'];        // 使用你的阿里云访问秘钥AccessKeyId
        $accessKey = $this->config['accessKey'];             // 使用你的阿里云访问秘钥AccessKeySecret
        $this->client = new \Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey);
    }

    /**
     * 保存到阿里云日志里
     * @param $source
     * @param $contents
     */
    private function saveToAli($source, $contents,$logType)
    {

        $project = $this->config['project'];                  // 上面步骤创建的项目名称
        $logItem = new \Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($contents);
        $logitems = array();
        array_push($logitems, $logItem);

        $topic = "";
        $req2 = new \Aliyun_Log_Models_PutLogsRequest($project, "access", $topic, $source, $logitems);

       $this->client->putLogs($req2);
//        dump($result);
        if($logType == "error"){
            //如果是错误的话,则也记录在错误日志里
            $req2 = new \Aliyun_Log_Models_PutLogsRequest($project, "error", $topic, $source, $logitems);

            $this->client->putLogs($req2);
        }
//        dump($res2);
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log = [])
    {

        $now = date($this->config['time_format']);
        $destination = $this->config['path'] . date('y_m_d') . '.log';

        !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }

        // 获取基本信息
        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
        }
        $runtime = (number_format(microtime(true), 8, '.', '') - THINK_START_TIME) ?: 0.00000001;
        $reqs = number_format(1 / number_format($runtime, 8), 2);
        $time_str = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_use = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $file_load = ' [文件加载：' . count(get_included_files()) . ']';


        $contents = [];
        $info = '[ log ] ' . $current_uri . $time_str . $memory_str . $file_load . "\r\n";
        $contents['log'] = $current_uri . $time_str . $memory_str . $file_load;

        $logType = "info";
        foreach ($log as $type => $val) {
            if($type == "error"){
                $logType = $type;
            }
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }
                $info .= '[ ' . $type . ' ] ' . $msg . "\r\n";
            }
            if (is_array($val)) {
                foreach ($val as $key => $value) {
                    $contents[strtoupper("[ $type $key ]")] = is_object($value) ? json_encode($value) : $value;
                }
            } else {
                $contents[strtoupper("[ $type ]")] = is_object($val) ? json_encode($val) : $val;
            }

        }
        $server = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
        $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $this->saveToAli($server, $contents,$logType);

        return error_log("[{$now}] {$server} {$remote} {$method} {$uri}\r\n{$info}\r\n", 3, $destination);
    }
}