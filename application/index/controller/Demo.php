<?php
namespace app\index\controller;
use app\index\model\Article;
use think\Controller;
// use think\facade\Request;
use think\Request;
use think\Db;
use think\Validate;
use app\index\model\Register;
use app\common\controller\Base;

/**
 * @title 测试demo
 * @description 接口说明
 * @group 接口分组
 */
class Demo extends Base
{
    /**
     * @title 测试demo接口
     * @description 接口说明
     * @author TTT
     * @url /index/demo
     * @method POST
     *
     * @param name:username type:int require:1 default:1 other: desc:账号
     * @param name:password type:int require:1 default:1 other: desc:密码
     * @return data:帐号信息@!
     * @data token:token
     */
    public function index()
    {
        $data = $this->request->param();
        $validate = new Validate([
            'username' => 'require',
            'password' => 'require',
        ],[
            'username' => 'username不能为空！！',
            'password' => 'password不能为空！！',
        ]);
        if(!$validate -> check($data)){
            $msg['msg'] = $validate->getError();
            return $this->errorJson($msg);
        }
        $res = Register::create($data);
        if($res){
            $msg['msg'] = '注册成功';
            return $this->successJson($msg);
        }else{
            $msg['msg'] = '注册失败';
            return $this->errorJson($msg);
        }


    }
    /**
     * @title 模糊搜索接口
     * @description 根据关键词模糊查询内容
     * @author TTT
     * @url /index/Demo/select_by_key
     * @method POST

     * @param name:token type:int require:1 default: other: desc:token
     * @param name:title type:varchar require:1 default: other: desc:关键字
     *
     * @return data: 相应数据@!
     * @data
     */

    public function select_by_key(){
        $title = $this->request->param();
        $validate = new Validate([
            'title' => 'require',
        ],[
            'title.require' => '关键词不能为空！！',
        ]);
        if(!$validate -> check($title)){
            $msg['msg'] = $validate->getError();
            return $this->errorJson($msg);
        }
//        $result = Article::where('title','like','%'.$title.'%')->select();
        $result = Article::where('title','like','%'.$title['title'].'%')->select();
//        var_dump($result);
        if(!$result){
            $msg['msg'] = "查询内容不存在";
            return $this->errorJson($msg);
        }
        $msg['msg'] = "查询成功";
        $msg['list'] = $result;
        return $this->successJson($msg);
    }
}