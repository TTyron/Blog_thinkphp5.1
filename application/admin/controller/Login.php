<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use think\Session;

/**
 * @title 登录
 * @description 接口说明
 * @group 后台
 */
class Login extends Base
{

    /**
     * @title 登录检查接口
     * @description 接口说明
     * @author TTT
     * @url /admin/login/login
     * @method POST
     *
     * @param name:username type:varchar require:1 default: other: desc:用户名
     * @param name:password type:varchar require:1 default: other: desc:密码
     *
     * @return username:用户名
     */
    public function login()
    {

        
        //获取一下表单提交的数据,并保存在变量中
        $data = $this->request -> param();
        $username = $data['username'];
        $password = $data['password'];

        //在admin表中进行查询:以用户为条件
        $res = \app\admin\model\Admin::where('username',$username)->where('password',$password)->find();

        //如果没有查询到该用户
        if (!$res){

            //设置返回信息
            $msg['status'] = 0;
            $msg['message'] = '用户名或密码不正确';
            return $msg;
        }
        $msg['status'] = 1;
        Session::set('userName');
        $msg['session_username'] = Session::get('userName');
        $msg['message'] = '登录成功';
        return $msg;


    }


    // // 退出登录
    // public function logout()
    // {

    //     //删除当前用户session值
    //     Session::delete('user_id');
    //     Session::delete('user_info');

    //     //执行成功,并返回登录界面
    //     $this -> success('注销成功,正在返回...','login/index');
    // }


}
