<?php

namespace app\admin\controller;

use app\common\controller\Base;
use think\Request;
use app\admin\model\Article;
use think\Db;

/**
 * @title 文章
 * @description 接口说明
 * @group 后台 
 */

class Article extends Base
{


    /**
     * @title 指定id查询文章数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/Article/select_one_id
     * @method POST
     *
     *
     * @param name:id type:int require:1 default: other: desc:ID
     */
    public function select_one_id()

    {

        $id = $this->request->param('id');
        $data = Article::get($id);
        $message = $data->message()->order('id','desc')->select();
        if(!$data){
            $msg['msg'] = '这条数据不存在';
            return $this->errorJson($msg);
        }
        $msg['list'] = $data;
        $msg['list']['message'] = $message;
        $msg['list']['message_count'] = count($message);
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);

    }

    /**
     * @title 点击量排行接口
     * @description 接口说明
     * @author TTT
     * @url /admin/Article/select_by_rec
     * @method POST
     *
     */
    public function select_by_rec()

    {
        $data = Article::order('rec','desc')->select();
        if(!$data){
            $msg['msg'] = '查询失败';
            return $this->errorJson($msg);
        }
        $msg['list'] = $data;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);

    }

    /**
     * @title 查询所有文章数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/select_all
     * @method POST
     */
    public function select_all()

    {
        //1.获取到所有的数据记录

        $listData = Article::select();
        if(!$listData){
            $msg['msg'] = '查询失败';
            return $this->errorJson($msg);
        }
        $msg['list'] = $listData;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);

    }
    // public function create(){
    //     return view('article_add');
    // }

    /**
     * @title 新增文章数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/add
     * @method POST
     *
     * @param name:title type:varchar require:1 default: other: desc:标题
     * @param name:content type:text require:1 default: other: desc:内容
     * @param name:image type:varchar require:1 default: other: desc:图片
     * @param name:cate type:int require:1 default: other: desc:类别
     * 
     * @return title:标题
     * @return content:内容
     * @return image:图片
     * @return cate:类别
     */
    public function add(){
      //1.获取一下提交的数据,包括上传文件
      $data = $this->request->post();

      //2获取一下上传的文件对象
      $file = $this->request->file('image');

      //3判断是否获取到了文件
        if (empty($file)) {
            $msg['msg'] = '获取不到照片';
            return $this->errorJson($msg);
        }
         $info = $file->move('./uploads');


      if (is_null($info)){
          $msg['msg'] = '上传照片失败';
          return $this->errorJson($msg);
      }

      //5向表中新增数据
      // $data['image'] = 'public/uploads/'.$info -> getSavename();

      $res = Article::create($data);

      //6判断新增是否成功
      if (!$res){
          $msg['msg'] = '新增失败';
          return $this->errorJson($msg);
      }

      $msg['msg'] = '新增成功';
      return $this->successJson($msg);
    }

          
    /**
     * @title 修改文章数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/update
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:title type:varchar require:1 default: other: desc:标题
     * @param name:content type:text require:1 default: other: desc:内容
     * @param name:image type:varchar require:1 default: other: desc:图片
     * @param name:cate type:int require:1 default: other: desc:类别
     * 
     * @return id:id
     * @return title:标题
     * @return content:内容
     * @return image:图片
     * @return cate:类别
     */
    public function update()
    {
        //1.获取所有提交过来的数据，包括文件
        $data = $this ->request -> param();

        //2.对于文件单独操作打包成一个文件对象
        $file = $this -> request -> file('image');

        
      //3判断是否获取到了文件
        if (empty($file)) {
            $msg['msg'] = '获取不到照片';
            return $this->errorJson($msg);
        }
         $info = $file->move('./uploads');

        //4.执行更新操作
        $res = Article::where('id',$data['id'])->update([
            'image'=> 'public/uploads/'.$info -> getSavename(),
            'title' => $data['title'],
            'content' => $data['content'],
            'cate' => $data['cate']
        ]);

        //5.检测更新
        if (!$res){
            $msg['msg'] = '更新失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '更新成功';
        return $this->successJson($msg);



    }
    /**
     * @title 修改文章点击量接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/update_rec
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:rec type:int require:1 default: other: desc:点击量
     */
    public function update_rec()
    {
        $id = input('id');
        $rec = input('rec');
        $res = Article::where('id',$id)->update(['rec'=>$rec]);
        if (!$res){
            $msg['msg'] = '修改失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '修改成功';
        return $this->successJson($msg);
    }
    
    /**
     * @title 删除文章数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/article/delete
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     */
    public function delete($id)
    {
        //删除指定id数据
        $res = Article::destroy($id);
        if (!$res){
            $msg['msg'] = '删除失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '删除成功';
        return $this->successJson($msg);
    }


}
