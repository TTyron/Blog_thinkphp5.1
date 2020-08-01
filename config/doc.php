<?php

// 获取所有的模块下的接口文档
$appPath = dirname(__DIR__).'/application/'; // 应用根目录
// 获取应用目录下的模块目录config目录
$modulePath = scandir($appPath);
$controllerData = []; // 控制器配置文档数组
if (!empty($modulePath) && is_array($modulePath)) {
    // . .. 文件 都删除
    $moduleDirectoryPath = [];
    foreach ($modulePath as $index => $item) {
        if (is_dir("{$appPath}{$item}") && !in_array($item, ['.','..'])) {
            // 检查doc配置文件是否存在
            $docConfigPath = "{$appPath}{$item}/config/doc.php";
            if (file_exists($docConfigPath)) { // 文件存在,获取配置文件的内容
                $items = include $docConfigPath;
                if (!empty($items) && !empty($items['controller']) && is_array($items['controller'])) {
                    $controllerData = array_merge($controllerData, $items['controller']);
                }
            }
        }
    }
}



return [
    'title' => "APi接口文档",  //文档title
    'version'=>'1.0.0', //文档版本
    'copyright'=>'ABABAB', //版权信息
    'password' => '', //访问密码，为空不需要密码
    //静态资源路径--默认为云上路径，解决很多人nginx配置问题
    //可将assets目录拷贝到public下面，具体路径课自行配置
    'static_path' => '',

    'controller' => [
        'app\api\controller\Demo' 
        //这个是控制器的命名空间+控制器名称
    ],
    'filter_method' => [
        //过滤 不解析的方法名称
        '_empty'
    ],
    'return_format' => [
        //数据格式
        'status' => "200/300/301/302",
        'message' => "提示信息",
    ],
    'public_header' => [
        //全局公共头部参数
        //如：['name'=>'version', 'require'=>1, 'default'=>'', 'desc'=>'版本号(全局)']
    ],
    'public_param' => [
        //全局公共请求参数，设置了所以的接口会自动增加次参数
        //如：['name'=>'token', 'type'=>'string', 'require'=>1, 'default'=>'', 'other'=>'' ,'desc'=>'验证（全局）')']
    ],
];
