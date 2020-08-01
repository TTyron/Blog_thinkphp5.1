<?php

namespace app\admin\controller;
use app\common\controller\Base;

use think\Request;
use think\Db;

use app\admin\model\About;
/**
 * @title 关于我
 * @description 接口说明
 * @group 后台
 */
class About extends Base
{
    /**
     * @title 查询关于我数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/About/select
     * @method POST
     */
    public function select()
    { //1.获取到所有的数据记录

        $listData = About::select();
        $msg['list'] = $listData;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);
    }

    // public function testadd(){
    //     return $this->fetch('about_add');
    // }

    /**
     * @title 新增关于我数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/About/add
     * @method POST
     *
     * @param name:content type:text require:1 default: other: desc:内容
     * @param name:image type:varchar require:1 default: other: desc:照片
     * @param name:occupation type:varchar require:1 default: other: desc:职业
     * @param name:wechat type:varchar require:1 default: other: desc:微信号
     * @param name:email type:varchar require:1 default: other: desc:邮箱
     * @param name:autograph type:varchar require:1 default: other: desc:个性签名
     * 
     * @return content:内容
     * @return image:照片
     * @return occupation:职业
     * @return wechat:微信号
     * @return email:邮箱
     * @return autograph:个性签名
     */
    public function add()
    {

        //1.获取一下提交的数据,包括上传文件
        $data = $this->request->post();

        //2获取一下上传的文件对象
        $file = $this->request->post('image');
        // $file = input('image');

        //3判断是否获取到了文件
        if (empty($file)) {
            $msg['msg'] = '获取不到照片';
            return $this->errorJson($msg);
        }
         $info = $file->move('./uploads');

        //4上传文件
        // $map = [
        //     'ext'=>'jpg,png,jpeg',
        //     'size'=> 3000000
        // ];
        // $info = $file->move(__ROOT__ . 'public' . DS . 'uploads/');
        // if (is_null($info)){
        //     $msg['msg'] = '上传照片失败';
        //     return $this->errorJson($msg);
        // }

        //5向表中新增数据
        $data['image'] = 'public/uploads/'.$info -> getSaveName();

        $res = About::create($data);

        //6判断新增是否成功
        if (!$res){
            $msg['msg'] = '新增失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '新增成功';
        return $this->successJson($msg);

    }
    
    /**
     * @title 修改关于我数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/about/update
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:content type:text require:1 default: other: desc:内容
     * @param name:image type:varchar require:1 default: other: desc:照片
     * @param name:occupation type:varchar require:1 default: other: desc:职业
     * @param name:wechat type:varchar require:1 default: other: desc:微信号
     * @param name:email type:varchar require:1 default: other: desc:邮箱
     * @param name:autograph type:varchar require:1 default: other: desc:个性签名
     * 
     * @return id:id
     * @return content:内容
     * @return image:照片
     * @return occupation:职业
     * @return wechat:微信号
     * @return email:邮箱
     * @return autograph:个性签名
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
        $res = About::where('id',$data['id'])->update([
            'image'=> 'public/uploads/'.$info -> getSavename(),
            'content' => $data['content'],
            'occupation' => $data['occupation'],
            'wechat' => $data['wechat'],
            'email' => $data['email'],
            'autograph' => $data['autograph']
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
     * @title 删除关于我数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/about/delete
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     */
    public function delete($id)
    {
        //删除指定id数据
        $res = About::destroy($id);
        if (!$res){
            $msg['msg'] = '删除失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '删除成功';
        return $this->successJson($msg);
    }
}
