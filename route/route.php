<?php
use think\Request;
use think\Response;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('admin','admin/index');

// Route::get('hello/:name', 'index/hello');

// return [

// ];
// Route::rule('hello/:name', function (Request $request, $name) {
//     $method = $request->method();
//     return '[' . $method . '] Hello,' . $name;
// });

// Route::get('hello/:name', function (Response $response, $name) {
//     return $response
//         ->data('Hello,' . $name)
//         ->code(404)
//         ->contentType('text/plain');
// });
