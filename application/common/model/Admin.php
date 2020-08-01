<?php
/**
 * Created by PhpStorm.
 * author: 陈庆锋
 * Date: 2018/9/4
 * Time: 10:29
 * desc: 这是一个管理员模型
 */
namespace app\common\model;
use think\Db;
use think\Model;

class Admin extends Model
{
    /**
     * 函数的含义说明:获取管理员的角色
     *
     * @access  public
     * @author  作者
     * @param string $account_id  账号id
     * @return string  $roles
     * @date  2018/9/4
     * @time  10:37
     */
    public function getRole($account_id){
        $roles = Db::connect('qianxun_admin_auth_config')
            ->name('shop_admin_role')
            ->alias('sar')
            ->join('shop_role sr','sar.shop_role_id=sr.id')
            ->where('sar.account_id',$account_id)
            ->find();
        if($roles){       //有角色
            return $roles;
        }else{           //无角色，返回空字符串
            return $roles;
        }
    }

    /**
     * 函数的含义说明:获取管理员的基本信息
     *
     * @access  public
     * @author  作者
     * @param string token  token
     * @return array  $account
     * @date  2018/9/4
     * @time  11:15
     */
    public function getInfo($token){
        $account = Db::name('token')
            ->alias('t')
            ->join('account a', 't.account_id=a.id')
            ->field('a.*')
            ->where('t.token',$token)
            ->find();
        if($account){
            return $account;
        }else{
            return array();
        }
    }
}