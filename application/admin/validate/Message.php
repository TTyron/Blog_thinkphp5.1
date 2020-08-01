<?php
namespace app\admin\validate;

use think\Validate;

class Message extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:10', 
        'contents'   => 'require',
        'article_id' => 'require',
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过10个字符',
        'contents.require' => '留言必须',
        'article_id.require' => '文章id不能为空'
    ];

    // except_article_id
    public function sceneExcept_id(){
        return $this->only(['name','contents']);
    }

}