<?php

namespace app\admin\controller;


use app\common\controller\Base;
use think\facade\Request;
use app\admin\model\Message;
use think\Db;

/**
 * @title 留言
 * @description 接口说明
 * @group 后台
 */

class Message extends Base{
	/**
     * @title 查询所有留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/select_all
     * @method POST
     */
	public function select_all(){
		$list = Message::field('id,name,contents,comment_num,create_time')->order('id','desc')->select();
		$msg['list'] = $list;
		$msg['msg'] = '查询成功';
		return $this->successJson($msg);
	}

    /**
     * @title 第二查询所有留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/select_only
     * @method POST
     */
    public function select_only(){
        $list = Message::where('flag',1)->field('id,name,contents,comment_num,create_time')->order('id','desc')->select();
        $msg['list'] = $list;
        $msg['msg'] = '查询成功';
        $msg['msg_count'] = count($list);
        return $this->successJson($msg);
    }

    /**
     * @title 查询最新留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/select_new
     * @method POST
     */
    public function select_new(){
        $list = Message::field('id,name,contents,comment_num,create_time')->order('id','desc')->find();
        $msg['list'] = $list;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);
    }

public function testadd(){
	return $this->fetch('message_list');
}

	/**
     * @title 新增留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/add
     * @method POST
     *
     * @param name:name type:varchar require:1 default: other: desc:昵称
     * @param name:contents type:text require:1 default: other: desc:内容
     * @param name:article_id type:int require:1 default: other: desc:文章ID
     * 
     * @return name:昵称
     * @return content:内容
     */

	public function add(){
		$data = Request::param();
		$messageValidate = new \app\admin\validate\Message;
		$validate = $messageValidate->check($data);
		if(!$validate){
			$msg['msg'] = '验证失败';
			$msg['validate'] = $messageValidate->getError();
			return $this->errorJson($msg);
		}

        $article_id = input('article_id');
        $res = \app\admin\model\Article::get($article_id);
        if(!$res){
            $msg['msg'] = '该文章不存在';
            return $this->errorJson($msg);
        }
      //5向表中新增数据
		$res = Message::create($data);

      //6判断新增是否成功
		if (!$res){
			$msg['msg'] = '新增失败';
			return $this->errorJson($msg);
		}

		$msg['msg'] = '新增成功';
		return $this->successJson($msg);
	}

    /**
     * @title 第二新增留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/only_add
     * @method POST
     *
     * @param name:name type:varchar require:1 default: other: desc:昵称
     * @param name:contents type:text require:1 default: other: desc:内容
     * 
     * @return name:昵称
     * @return content:内容
     */

    public function only_add(){
        $data = Request::param();
        $messageValidate = new \app\admin\validate\Message;
        $validate = $messageValidate->scene('except_id')->check($data);
        if(!$validate){
            $msg['msg'] = '验证失败';
            $msg['validate'] = $messageValidate->getError();
            return $this->errorJson($msg);
        }
      //5向表中新增数据
        $data['flag'] = 1;
        $res = Message::create($data);

      //6判断新增是否成功
        if (!$res){
            $msg['msg'] = '新增失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '新增成功';
        return $this->successJson($msg);
    }

	/**
     * @title 修改留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/update
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:name type:varchar require:1 default: other: desc:昵称
     * @param name:content type:text require:1 default: other: desc:内容
     * @param name:comment_num type:varchar require:1 default: other: desc:评论条数
     * @param name:image type:int require:1 default: other: desc:头像
     * 
     * @return id:id
     * @return name:昵称
     * @return content:内容
     * @return comment_num:评论条数
     * @return image:头像
     */

	public function update()
    {
        //1.获取所有提交过来的数据，包括文件
        $data = $this ->request -> param();
        //验证
        $messageValidate = new \app\admin\validate\Message;
		$validate = $messageValidate->check($data);
		if(!$validate){
			$msg['msg'] = '验证失败';
			$msg['validate'] = $messageValidate->getError();
			return $this->errorJson($msg);
		}

        //2.对于文件单独操作打包成一个文件对象
        $file = $this -> request -> file('image');

        //3.文件验证与上传
        $info = $file->move('./uploads/');
        if (is_null($info)){
            $msg['msg'] = '上传照片失败';
            return $this->errorJson($msg);
        }

        $data['image'] = 'public/uploads/'.$info -> getSavename();
        //4.执行更新操作
        // $res = Message::where('id',$data['id'])->update([
        //     'image'=> $info -> getFilename(),
        //     'title' => $data['title'],
        //     'content' => $data['content'],
        //     'cate' => $data['cate'],
        //     'rec' => $data['rec']
        // ]);
        $res = Message::update($data);

        //5.检测更新
        if (!$res){
            $msg['msg'] = '更新失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '更新成功';
        return $this->successJson($msg);
    }


    /**
     * @title 删除留言数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/message/delete
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     */

    public function delete($id)
    {
        //删除指定id数据
        $res = Message::destroy($id);
        if (!$res){
            $msg['msg'] = '删除失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '删除成功';
        return $this->successJson($msg);
    }
}