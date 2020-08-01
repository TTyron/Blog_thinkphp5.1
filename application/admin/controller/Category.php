<?php

namespace app\admin\controller;

use app\common\controller\Base;

use think\Request;

use app\admin\model\Category;
/**
 * @title 分类
 * @description 接口说明
 * @group 后台
 */
class Category extends Base
{
    /**
     * @title 查询分类数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/Category/select
     * @method POST
     */
    public function select()
    {

        //获取所有分类信息
        $cate_list=Category::order('id','asc')->select();

        //3.获取分类数量
        $count = Category::count();

        //输出json数据
        $msg['list'] = $cate_list;
        $msg['count'] = $count;
        $msg['msg'] = '查询成功';
        return $this->successJson($msg);
    }

    
    /**
     * @title 新增分类数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/Category/add
     * @method POST
     *
     * @param name:cate_name type:varchar require:1 default: other: desc:分类名称
     * 
     * @return cate_name:分类名称
     */
    public function add()
    {
        //添加
        $data = $this->request->param();
        $res = Category::create($data);

        //6判断新增是否成功
        if (!$res){
            $msg['msg'] = '新增失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '新增成功';
        return $this->successJson($msg);
    }


    /**
     * @title 修改分类数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/Category/update
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:cate_name type:varchar require:1 default: other: desc:分类名称
     * 
     * @return id:id
     * @return cate_name:分类名称
     */
    public function update()
    {
        //1.获取一下提交的数据
        $data = $this->request -> param();

        //2.更新操作
        $res = Category::where('id',$data['id'])->update([
            'cate_name' => $data['cate_name']
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
     * @title 删除分类数据接口
     * @description 接口说明
     * @author TTT
     * @url /admin/category/delete
     * @method POST
     *
     * @param name:id type:int require:1 default: other: desc:id
     */
    public function delete($id)
    {
        
        //删除指定id数据
        $res = Category::destroy($id);
        if (!$res){
            $msg['msg'] = '删除失败';
            return $this->errorJson($msg);
        }

        $msg['msg'] = '删除成功';
        return $this->successJson($msg);

    }


}
