<?php

namespace app\admin\model;


use think\Model;

class Message extends Model{
	// protected $autoWriteTimestamp = true;
	public function getCreateTimeAttr($value){
		$time = time()-$value;
		if ($time<=60) {
			return '一分钟前';
		}elseif ($time>60 && $time<=60*60) {
			return '一个小时前';
		}elseif ($time>60*60 && $time<60*60*24) {
			return '一天前';
		}else{
			return date("Y-m-d h:i",$value);
		}
	}
	
}