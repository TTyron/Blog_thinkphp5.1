<?php

namespace app\common\model;

use think\Db;
use think\Model;

class TreeModel extends Model {
    function __construct() {
        parent::__construct();
        $root = Db::query("SELECT * FROM  " . $this->getTable());
        if(!$root){
            DB::table($this->getTable())->insert(array('son_id' => 0, "parent_id" => 0, 'depth' => 0, 'left' => 1, 'right' => 2, 'root_id' => 0));
        }
    }
    /**
     * 添加树节点
     * @param $parent_id  父id
     * @param $son_id 子id
     */

    //获取树根
    function getRoot($id) {
        $root = Db::query("SELECT * FROM  " . $this->getTable() . " where son_id=$id ORDER BY depth desc LIMIT 1");
        if ($root && $root[0]) {
            return $root[0];
        } else {
            return FALSE;
        }
    }

    /** parent_id父
     * son_id子
     * 
     * */
//获取上面一层
    function getAParent($id, $length = 1) {
        $parent_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where son_id=" . $id . " AND depth=" . $length);
        if ($parent_ids && $parent_ids[0]) {
            return $parent_ids[0];
        } else {
            return FALSE;
        }
    }

//获取下级（不带自己）
    function getALLSon($id) {
        $son_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where parent_id=$id AND depth>0  order by depth asc");

        return $son_ids;
    }

//获取所有下级（带自己）
    function getALLSonAndSelft($id) {
        $son_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where parent_id=$id order by depth asc");
        return $son_ids;
    }

//获取所有上级(不带自己)
    function getAllParent($id) {
        $parent_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where son_id=$id AND depth>0 order by depth asc");
        return $parent_ids;
    }

//获取所有上级(带自己)
    function getAllParentAndSelft($id) {
        $parent_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where son_id=$id order by depth asc");
        return $parent_ids;
    }

//获取下面一层（不带自己）
    function getSon($id, $length=1) {
        $son_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where parent_id=$id AND depth=$length ");
        return $son_ids;
    }

//是否包含（防止循环）
    function isInSameTree($id1, $id2) {
        $son_ids = Db::query("SELECT * FROM  " . $this->getTable() . " where parent_id=$id1 AND son_id=$id2 and depth>0 UNION SELECT * FROM  " . $this->getTable() . " where parent_id=$id2 AND son_id=$id1 and depth>0;");

        return $son_ids;
    }

    //删除整子棵树(带自己)
    function delAllSon($id) {
        $this->delFromTree($id);
        $sql = "DELETE FROM  " . $this->getTable() . " WHERE son_id IN ( SELECT a.son_id FROM (SELECT t.son_id FROM  " . $this->getTable() . " AS t WHERE t.parent_id = $id) as a ); ";
        return Db::execute($sql);
    }

//把树分离
    private function delFromTree($id) {
        $sons = $this->getALLSonAndSelft($id);
        $parents = $this->getAllParent($id); //不带自己

        $root = $this->getRoot($id);
        $son = $this->getAParent($id, 0);

        $parent_start = $son['right'] + 1;
        $parent_del = $son['right'] - $son['left'] + 1;
        $son_del = $son['left'] - 1;
        //顺序不可以换
        //第一步要分离的树
        $sql = "update " . $this->getTable() . " set `left`=(`left`-$son_del) WHERE `left`>=" . $son['left'] . " and `left`<=" . $son['right'] . " and  root_id=" . $root['parent_id'];
         
        Db::execute($sql);
        $sql = "update " . $this->getTable() . " set `right`=(`right`-$son_del) WHERE `right`>=" . $son['left'] . " and `right`<=" . $son['right'] . " and  root_id=" . $root['parent_id'];
        Db::execute($sql);
        //第二步父树
        $sql = "update " . $this->getTable() . " set `left`=(`left`-$parent_del) WHERE `left`>=" . $parent_start . " and  root_id=" . $root['parent_id'];
        Db::execute($sql);
        $sql = "update " . $this->getTable() . " set `right`=(`right`-$parent_del) WHERE `right`>=" . $parent_start . " and  root_id=" . $root['parent_id'];
        Db::execute($sql);




        $son_ids = [];
        foreach ($sons as $son) {
            $son_ids[] = $son['son_id'];
        }
        $parent_ids = [];
        foreach ($parents as $parent) {
            $parent_ids[] = $parent['parent_id'];
        }
        $son_ids_str = implode(",", $son_ids);
        $parent_ids_str = implode(",", $parent_ids);

        $sql = "update " . $this->getTable() . "  set `root_id`=$id  WHERE son_id in ($son_ids_str)";
        Db::execute($sql);


        $sql = "DELETE FROM " . $this->getTable() . " WHERE son_id in ($son_ids_str) AND parent_id in ($parent_ids_str)";
        return Db::execute($sql);
    }

    public function delNode($son_id) {
        $this->startTrans();
        try {
            $parent = $this->getAParent($son_id);
            $depth1_sons = $this->getSon($son_id);
            
            foreach ($depth1_sons as $son) {
                $this->moveTo($son['son_id'], $parent['parent_id']);
            }
            $this->delFromTree($son_id);
            $sql = "DELETE FROM " . $this->getTable() . " WHERE   parent_id=$son_id";
            Db::execute($sql);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务

            $this->rollback();
            exception($e);
        }
        return TRUE;
    }

//把已分离的树添加到树
//注意：必须是delFromTree后的树
    private function addToTree($son_id, $parent_id, $before_id = NULL) {

        $parent = $this->getAParent($parent_id, 0);
        $son = $this->getAParent($son_id, 0);

        $root = $this->getRoot($parent_id);
        $before=false;
        if ($before_id) {
            $before = $this->getAParent($before_id, 0);
        }



        if ($before) {
            $parent_start = $before['left'];
            $son_add = $before['left'] - 1;
            $parent_add = $son['right'] - $son['left'] + 1;
        } else {
            $parent_start = $parent['right'];
            $son_add = $parent['right'] - 1;
            $parent_add = $son['right'] - $son['left'] + 1;
        }

        $sql = "select * from  " . $this->getTable() . " WHERE   root_id=" . $son_id;
        $re = Db::query($sql);


        //顺序不可以换
        //parent树
        //左边
        $sql = "update " . $this->getTable() . " set `left`=(`left`+$parent_add) WHERE `left`>=$parent_start and root_id=" . $root['parent_id'];
        Db::execute($sql);
        //右边
        $sql = "update " . $this->getTable() . " set `right`=(`right`+$parent_add) WHERE `right`>=$parent_start and  root_id=" . $root['parent_id'];
        Db::execute($sql);




        $parents = $this->getAllParentAndSelft($parent_id);
        $sons = $this->getALLSonAndSelft($son_id);
        $nodes = [];
        foreach ($sons as $son) {

            foreach ($parents as $parent) {
                $node = [];
                $node['son_id'] = $son['son_id'];
                $node['parent_id'] = $parent['parent_id'];
                $node['depth'] = $son['depth'] + $parent['depth'] + 1;
                $node['left'] = $son['left'] + $son_add;
                $node['right'] = $son['right'] + $son_add;
                $node['root_id'] = $root['parent_id'];
                $nodes[] = $node;
            }
        }
        $this->insertAll($nodes);

        //son树
        //左边
        $sql = "update " . $this->getTable() . " set `left`=(`left`+$son_add) WHERE root_id=" . $son_id;
        Db::execute($sql);
        //右边
        $sql = "update " . $this->getTable() . " set `right`=(`right`+$son_add) WHERE  root_id=" . $son_id;
        Db::execute($sql);

        $son_ids = [];
        foreach ($sons as $son) {
            $son_ids[] = $son['son_id'];
        }
        $son_ids_str = implode(",", $son_ids);

        $sql = "update " . $this->getTable() . "  set `root_id`=" . $root['parent_id'] . "  WHERE son_id in ($son_ids_str)";

        Db::execute($sql);
        return TRUE;
    }

//将$son_id下的树移动到$parent_id;
    function moveTo($son_id, $parent_id) {
        Db::table($this->getTable().'_move_to')->insert(array('son_id'=>$son_id,"parent_id"=>$parent_id));
        $re=$this->isInSameTree($son_id, $parent_id);
        if($re&&$parent_id){
            return false;
        }
        if($son_id==$parent_id){
                return false;
        }

        DB::startTrans();
        try {
            $parent_id = intval($parent_id);
            $son_id = intval($son_id);
            if ($this->getAParent($son_id, 0) === FALSE) {
                DB::table($this->getTable())->insert(array('son_id' => $son_id, "parent_id" => $son_id, 'depth' => 0, 'left' => 1, 'right' => 2, 'root_id' => $son_id));
            }
            if ($this->getAParent($son_id) !== FALSE) {
                $this->delFromTree($son_id); //从树上分离 
            }
            if ($this->getAParent($parent_id, 0) !== FALSE) {
                $this->addToTree($son_id, $parent_id);
                DB::commit();

                
                return true;
            }
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollback();
            exception($e);
        }

        return FALSE;
    }

    //例子
    public function test() {
        $model = model("TreeModel");
        $model->table('');
        for ($i = 1; $i < 70000; $i++) {

            $parent_id = rand(0, $i - 1);
            $model->moveTo($i, $parent_id);
        }
    }

}

?>
