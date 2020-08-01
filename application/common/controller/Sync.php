<?php

namespace app\common\controller;

use think\Db;
use think\worker\Server;
use think\Request;
use Workerman\Lib\Timer;

class Sync extends Server
{
    protected $port = 2349;
    protected $option = [
        'count' => 3,
        'name' => 'Sync',
    ];

    public function onMessage($connection, $data)
    {
        $json['msg'] = "sync_server start success";
        $connection->send($this->successJson($json));
    }

    function successJson($data = [])
    {
        $default = array(
            "status" => true,
            "msg" => "成功",
        );

        $data = array_merge($default, $data);
        return json_encode($data);
    }

    function errorJson($data = [])
    {
        $default = array(
            "status" => false,
            "msg" => "失败",
        );
        $data = array_merge($default, $data);
        return json_encode($data);
    }

    public function task($worker)
    {
        $is_true = true;
        //定时器使用
        //Timer::add(time_interval,func:callable,[args:array | mixed=array()],[persistent:bool = true])

        //time_interval(间隔时间，默认单位秒数)

        //func:callable（回调函数）
        //注意：回调函数属性必须是public
        //使用方法
        //调用当前类下的方法：
        //例如：调用当前类下的test方法 array($this,'test')
        //调用其它类下的方法：
        //例如：调用Login类下的test方法 $login = new Login();  array($login,'test')

        //args 参数
        //传递函数所需参数
        //例如：调用当前类下的test方法，且test方法需要$a和$b两个参数 array($a,$b)

        //persistent 是否持续
        //persistent 为 false时，该定时器只调用一次。persistent 为 true时，该定时器持续调用

        //完整示例：
        //增加一个定时任务：每60秒执行测试任务
        //test方法 function test(){ echo '123'; }
        //public task(){ Timer::add(60, array($this,'test'), array(), true) }
        if ($worker->id === 0) {
            // 每5秒执行测试任务
//            Timer::add(5, array($this, 'test'), array(), $is_true);
            //自动取消订单
            Timer::add(600, array($this, 'cancel_order_whole_situation'), array(), $is_true);//10分钟执行一次
            //订单自动完成（7天内，没有售后，添加完成时间，不可售后）
            Timer::add(5, array($this, 'order_confirm_receipt_after'), array(), $is_true);//12个小时执行一次

        }
//        if ($worker->id === 1) {
//            砍价活动过期操作
//            Timer::add(900, array($this, 'bargain'), array(), $is_true);
//            自动评价
//            Timer::add(43200, array($this, 'evaluate_auto'), array(), $is_true);//12个小时执行一次
//        }
//        if ($worker->id === 2) {
            //自动确认收货
//            Timer::add(43200, array($this, 'automatic_confirmation_of_receipt'), array(), $is_true);//12个小时执行一次
            //拼团活动过期操作
//            Timer::add(900, array($this, 'wholesale'), array(), $is_true);
//        }

    }


    public function onWorkerStart($worker)
    {
        $this->task($worker);
    }

    //测试
    function test()
    {
        if(config('app.sync')){
            echo '123....';
        }
    }

    //自动取消订单
    function cancel_order_whole_situation(){

        //自动取消订单时间
        $automatic_cancellation = config('electronic_mall.automatic_cancellation');

        //当前时间
        $nowtime = time();

        $zb_order = config('database.zb_order');
        $zb_wholesale = config('database.zb_wholesale');

        //查询订单列表未支付且未取消订单
        $order = Db::connect($zb_order)
            ->name('order')
            ->where('payStatus', 'not_pay')
            ->where('isDelete', '0')
            ->field('id,payStatus,orderNo,product_type,createTime')
            ->select();

        $res = [];
        //交易关闭
        foreach ($order as $k => $v) {
            $time_stack = $nowtime - $v['createTime'];
            Db::startTrans();
            try{
                if ($time_stack > $automatic_cancellation) {
                    $res[] = Db::connect($zb_order)->name('order')->where('id', $v['id'])->update(['orderStatus' => 'cancel']);

                    //拼团商品操作
                    if($v['product_type'] == 2){

                        //更新拼团记录为失败状态
                        $update_status = Db::connect($zb_wholesale)
                            ->name('wholesale_record')
                            ->where('orderNo', $v['orderNo'])
                            ->update([
                                'status' => 2,
                                'updateTime' => time()
                            ]);

                        if(!$update_status){
                            Db::rollback();
                        }
                    }
                    Db::commit();
                }
            }catch (\Exception $e){
                Db::rollback();
                logs('cancel_order_exception',$e->getMessage());
            }
        }

        if(config('app.sync')){
            if(count($res) > 0 && in_array(1,$res)){
                echo 'cancel_order_true';
            }else{
                echo 'cancel_order_false';
            }
        }

    }

    //自动完成订单
    function order_confirm_receipt_after(){

        //订单确认收货后(售后有效期)
        $confirm_receipt = config('electronic_mall.confirm_receipt');
        //转为时间戳
        $confirm_receipt = $confirm_receipt * 24 * 60 * 60;

        //当前时间
        $nowtime = time();

        $zb_order = config('database.zb_order');

        //查询订单列表已确认收货数据
        $order = Db::connect($zb_order)
            ->name('order')
            ->where('isReceipt', '1')
            ->where('isDelete', '0')
            ->where('orderStatus', 'success')
            ->whereOr('orderStatus','evaluate')
            ->where('accomplishTime', null)
            ->field('id,receiptTime')
            ->select();

        $res = [];

        foreach ($order as $k => $v) {
            $time_stack = $nowtime - $v['receiptTime'];
            if ($time_stack > $confirm_receipt) {
                $res[] = Db::connect($zb_order)->name('order')->where('id', $v['id'])->update(['accomplishTime' => time()]);
            }
        }

        if(config('app.sync')){
            if(count($res) > 0 && in_array(1,$res)){
                echo 'complete_order_true';
            }else{
                echo 'complete_order_false';
            }
        }

    }

    //自动确认收货
    function automatic_confirmation_of_receipt(){

        //自动确认收货时间
        $automatic_confirmation = config('electronic_mall.automatic_confirmation');
        //转为时间戳
        $automatic_confirmation = $automatic_confirmation * 24 * 60 * 60;

        //当前时间
        $nowtime = time();

        $zb_order = config('database.zb_order');

        //查询订单列表待收货数据
        $order = Db::connect($zb_order)
            ->name('order')
            ->where('isDelete', '0')
            ->where('orderStatus', 'receiving')
            ->where('isShip', 1)
            ->field('id,shipTime')
            ->select();

        $res = [];

        foreach ($order as $k => $v) {
            $time_stack = $nowtime - $v['shipTime'];
            if ($time_stack > $automatic_confirmation) {
                $res[] = Db::connect($zb_order)->name('order')->where('id', $v['id'])->update(['orderStatus' => 'evaluate']);
            }
        }

        if(config('app.sync')){
            if(count($res) > 0 && in_array(1,$res)){
                echo 'automatic_confirm_order_true';
            }else{
                echo 'automatic_confirm_order_false';
            }
        }


//        $test = get_curl('order','order/automatic_confirmation_of_receipt');
//
//        if($test['status'] == true){
//            echo 'true';
//        } else {
//            echo 'false';
//        }
    }

    //自动评价
    function evaluate_auto()
    {
        //自动评价时间
        $good_review = config('electronic_mall.good_review');

        //转为时间戳
        $confirm_receipt = $good_review * 24 * 60 * 60;

        //当前时间
        $now_time = time();

        $zb_goods = config('database.zb_goods');
        $zb_order = config('database.zb_order');

        //查询未评价订单的创建时间
        $evaluate = Db::connect($zb_order)
            ->name('order_item')
            ->where('is_evaluate', '0')
            ->field('id,goodID,skuID,orderID,createTime')
            ->select();

        $res = [];

        //如果存在未评价订单
        if(count($evaluate) > 0){
            foreach ($evaluate as $k => $v) {
                //开启事务
                Db::startTrans();
                try{
                    //当前时间与未追评订单的时间差
                    $day = $now_time - $v['createTime'];

                    //时间差 大于 自动评价时间 则自动添加追评
                    if ($day > $confirm_receipt) {

                        $sku_name = '';

                        if(!empty($v['skuID'])){
                            //查询属性名称
                            $sku_name = Db::connect($zb_goods)
                                ->name('good_sku')
                                ->where('id', $v['skuID'])
                                ->value('name');
                        }

                        //查询订单下的用户ID和商家ID
                        $user_info = Db::connect($zb_order)
                            ->name('order')
                            ->where('id', $v['orderID'])
                            ->field('userID,merchantID')
                            ->find();

                        if(!isset($user_info['userID'])){
                            $user_info['userID'] = 0;
                        }

                        if(!isset($user_info['merchantID'])){
                            $user_info['merchantID'] = 0;
                        }

                        //添加评论
                        $res[] = Db::connect($zb_goods)
                            ->name('good_evaluate')
                            ->insert([
                                'good_id' => $v['goodID'],
                                'sku' => $sku_name,
                                'content' => '此用户没有填写评价。',
                                'imgs' => '',
                                'user_id' => $user_info['userID'],
                                'merchant_id' => $user_info['merchantID'],
                                'desc_rank' => 5,
                                'express_rank' => 5,
                                'service_rank' => 5,
                                'rank' => 1,
                                'is_system_default' => 1,
                                'is_add' => 0,
                                'order_id' => $v['orderID'],
                                'create_time' => $now_time,
                                'update_time' => $now_time
                            ]);

                        //修改order_item表中is_evaluate的状态
                        Db::connect($zb_order)
                            ->name('order_item')
                            ->where('orderID', $v['orderID'])
                            ->where('goodID', $v['goodID'])
                            ->update(['is_evaluate' => 1]);

                        //查询该订单是否存在还未评价商品
                        $order_item = Db::connect($zb_order)
                            ->name('order_item')
                            ->where('orderID', $v['orderID'])
                            ->where('is_evaluate', 'neq', 1)
                            ->select();

                        //如果该订单不存在未评价商品
                        if (count($order_item) < 1) {
                            //修改订单状态
                            $up = Db::connect($zb_order)
                                ->name('order')
                                ->where('id', $v['orderID'])
                                ->update(
                                    [
                                        'orderStatus' => 'success',
                                        'lastUpdateTime' => time()
                                    ]);
                        }

                    }
                    //提交事务
                    Db::commit();
                }catch (\Exception $e){
                    //事务回滚
                    Db::rollback();
                    logs('auto_evaluate_exception_false',$e->getMessage());
                    echo "auto_evaluate_exception_false\n";
                }
            }
        }

        if(config('app.sync')){
            if(count($res) > 0 && in_array(1,$res)){
                echo "auto_evaluate_true\n";
            }else{
                echo "auto_evaluate_false\n";
            }
        }

    }

    //砍价活动
    function bargain()
    {
        $zb_bargain = config('database.zb_bargain');

        $now_time = time();

        //查询已过期的砍价活动
        $end_ids = Db::connect($zb_bargain)
            ->name('bargain')
            ->where('endTime','<',$now_time)
            ->column('id');

        $update = false;

        if(count($end_ids) > 0){
            $bargain_record_end = Db::connect($zb_bargain)
                ->name('bargain_record')
                ->where('status',0)
                ->where('bargain_id','in',$end_ids)
                ->column('id');

            if(count($bargain_record_end) > 0){
                $update = Db::connect($zb_bargain)
                    ->name('bargain_record')
                    ->where('id','in',$bargain_record_end)
                    ->update([
                        'status' => 2,
                        'updateTime' => $now_time,
                    ]);
            }
        }

        if(config('app.sync')){
            if($update){
                echo "bargain_true\n";
            }else{
                echo "bargain_false\n";
            }
        }

    }

    //拼团活动
    function wholesale()
    {
        $zb_wholesale = config('database.zb_wholesale');
        $zb_order = config('database.zb_order');

        $now_time = time();

        //查询正在进行的已过期拼团记录
        $wholesale_record = Db::connect($zb_wholesale)
            ->name('wholesale_record')
            ->where('endTime', '<',$now_time)
            ->column('id');

        if(count($wholesale_record) > 0){

            foreach ($wholesale_record as $k => $v){
                Db::startTrans();
                try{

                    $orderNo = Db::connect($zb_wholesale)
                        ->name('wholesale_record')
                        ->where('id',$v)
                        ->value('orderNo');

                    $orderID = Db::connect($zb_order)
                        ->name('order')
                        ->where('orderNo',$orderNo)
                        ->value('id');

                    //查询订单详情信息
                    $order_item = Db::connect($zb_order)
                        ->name('order_item')
                        ->where('orderID',$orderID)
                        ->find();

                    $pa = [
                        'token' => 'os2rftxaXgM0vWL1QzOH',
                        'order_item_id' => $order_item['id'],
                        'order_id' => $orderID,
                        'reason' => '拼团失败,进行退款',
                        'afterReason' => '拼团失败',
                        'img_banner' => '',
                        'type' => 'refund',
                        'num' => $order_item['num'],
                        'is_sys' => true,
                    ];

                    //调取申请售后接口
                    $res = get_curl('order','order/user_after_sale',$pa);

                    //更新拼团记录
                    $update = [
                        'status' => 2,
                        'updateTime' => $now_time,
                    ];

                    if($res['status']){
                        $update['isRefund'] = 1;
                    }

                    $update_status = Db::connect($zb_wholesale)
                        ->name('wholesale_record')
                        ->strict(false)
                        ->where('id', $v)
                        ->update($update);

                    Db::commit();
                    if(config('app.sync')){
                        logs('wholesale_update_status',$update_status);
                        if($update_status){
                            echo "wholesale_true\n";
                        }else{
                            echo "wholesale_false\n";
                        }
                    }
                }catch (\Exception $e){
                    Db::rollback();
                    if(config('app.sync')){
                        logs('wholesale_exception_false',$e->getMessage());
                        echo "wholesale_exception_false\n";
                    }
                }
            }
        }
    }

    //自动追评
//    function review_auto()
//    {
//        //追加评价时期
//        $additional_review = config('electronic_mall.additional_review');
//
//        //转为时间戳
//        $confirm_receipt = $additional_review * 24 * 60 * 60;
//
//        //当前时间
//        $now_time = time();
//
//        $zb_goods = config('database.zb_goods');
//        $zb_order = config('database.zb_order');
//
//        //查询未追评的评价订单id和创建时间
//        $evaluate = Db::connect($zb_goods)
//            ->name('good_evaluate')
//            ->where('is_add', '0')
//            ->field('id,good_id,order_id,create_time')
//            ->select();
//
//        logs('good_evaluate', $evaluate);
//        $res = [];
//
//        foreach ($evaluate as $k => $v) {
//
//            try{
//                //当前时间与未追评订单的时间差
//                $day = $now_time - $v['create_time'];
//
//                //时间差大于$confirm_receipt则自动添加追评
//                if ($day > $confirm_receipt) {
//
//                    //添加追评
//                    $res[] = Db::connect($zb_goods)
//                        ->name('additional_review')
//                        ->insert([
//                            'evaluate_id' => $v['id'],
//                            'content' => '该用户没有追评',
//                            'create_time' => $now_time,
//                            'update_time' => $now_time]);
//
//                    //修改good_evaluate表中is_add的状态和update_time
//                    Db::connect($zb_goods)
//                        ->name('good_evaluate')
//                        ->where('order_id', $v['order_id'])
//                        ->where('good_id', $v['good_id'])
//                        ->update(['is_add' => 1, 'update_time'=>$now_time]);
//
//                    //修改order_item表中is_add的状态
//                    Db::connect($zb_order)
//                        ->name('order_item')
//                        ->where('orderID', $v['order_id'])
//                        ->where('goodID', $v['good_id'])
//                        ->update(['is_add' => 1]);
//                }
//                //提交事务
//                Db::commit();
//            }catch (\Exception $e){
//                //提交事务
//                Db::rollback();
//                logs('auto_review_exception_false',$e->getMessage());
//            }
//        }
//        if(count($res) > 0 && in_array(1,$res)){
//            echo 'auto_review_true';
//        }else{
//            echo 'auto_review_false';
//        }
//    }

}