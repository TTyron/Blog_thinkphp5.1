<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use app\admin\model\Admin;

/**
 * @title 管理员
 * @description 接口说明
 * @group 后台
 */
class Admin extends Base
{

    /**
     * @title 管理员个人信息接口
     * @description 接口说明
     * @author TTT
     * @url /admin/admin/select
     * @method POST
     *
     *
     * @param name:username type:varchar require:1 default: other: desc:用户名
     *
     *
     * @return username:用户名
     * @return password:密码
     * @return email:邮箱
     * @return login_count:登录次数
     * @return last_time:最后登录时间
     */
    public function select()
    {
                //1.读取admin管理员表的信息
        $username = input("param.username");
        $list = Admin::where('username',$username)->field('username,password,email,login_count,last_time')->select();

        if (!$list){

            //设置返回信息
            $msg['msg'] = '该用户不存在';
            return $this->errorJson($msg);
        }
        $msg['list'] = $list;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);


    }


    /**
     * @title 修改个人信息接口
     * @description 接口说明
     * @author TTT
     * @url /admin/admin/update
     * @method POST
     *
     *
     * @param name:username type:varchar require:1 default: other: desc:用户名
     * @param name:password type:varchar require:1 default: other: desc:密码
     * @param name:email type:varchar require:1 default: other: desc:邮箱
     *
     *
     * @return username:用户名
     * @return password:密码
     * @return email:邮箱
     */
    public function update()
    {

        //获取提交的数据,自动过滤一下空值
        $data = array_filter($this->request->param());

        //更新用户表
        $res = Admin::where('username',$data['username'])->update($data);


        //判断修改是否成功
         if (!$res){
         $msg['msg'] = '修改失败';
         return $this->errorJson($msg);
        }

        $msg['msg'] = '修改成功';
        return $this->successJson($msg);
    }




}
