<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/22
 * Time: 16:38
 */

namespace app\admin\model;


use think\Model;

class Article extends Model
{
    //创建获取器方法,用来实现时间的转换
    // public function getLastTimeAttr($val)
    // {
    //     return date('Y/m/d', $val);
    // }
    public function getCateAttr($value){
    	$status = [0=>'心得博客',1=>'博客日记',2=>'程序人生',3=>'网址建设'];
        return $status[$value];
    }

    public function message(){
    	return $this->hasMany('Message','article_id');
    }
}