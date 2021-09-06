# ifu-log
# 项目简介: 阿里云日志

# 使用案例

 ##配置文件使用:

ThinkPHP 6 阿里云日志服务日志驱动
```

## 配置
config/log.php
```php
// 默认日志记录通道
'default'      => 'aliyunsls',
// 日志通道列表
'channels'     => [
    'aliyunsls' => [
        // 日志记录方式
        //阿里云日志  2021/03/11 cindy
        'type'        => '\ifu\helper\Log',
        // 日志保存目录
        'path'           => '',
        // 单文件日志写入
        'single'         => false,
        // 指定日志类型
        'level'=>['notice', 'warning', 'error', 'sql', 'critical', 'alert', 'emergency'],
        // 独立日志级别
        'apart_level' => ['error', 'sql', 'critical', 'alert', 'emergency'],
        // 每个文件大小 ( 10兆 )
        // 'file_size'      => 1024 * 1024 * 10,
        // 日志日期格式
        'time_format'    => 'Y-m-d H:i:s',
        // 最大日志文件数量
        // 'max_files'      => 100,
        // 使用JSON格式记录
        'json'           => true,
        // 日志处理
        'processor'      => null,
        // 关闭通道日志写入
        'close'          => false,
        // 日志输出格式化
        'format'         => '[%s][%s] %s',
        // 是否实时写入
        'realtime_write' => false,
        // 阿里云 endpoint
        'aliyun_log_endpoint' => '',
        // 阿里云 AccessKey ID
        'aliyun_log_access_key_id' => '',
        // 阿里云 AccessKey Secret
        'aliyun_log_access_key_secret' =>'',
        // 项目名称
        'aliyun_log_project' => '',
        // logstore 名称
        'aliyun_log_logstore' => '',
        // source 标识
        'aliyun_log_source' => '',
        // topic 标识
        'aliyun_log_topic' => '',
        // 日志处理
        'aliyun_log_processor' => null,
        // 使用JSON格式记录
        'aliyun_log_json' => true,
    ],
]

# 单独调用

引入: use ifu\helper\Log;

$this->log = new Log;
$apidata['level']           =   2;
$apidata['apiUrl']          =   'haigongPYJ';
/**
* $apidata 数组key和value都可以自定义;
**/
$this->log->apisave($apidata,'自定义标识码');
