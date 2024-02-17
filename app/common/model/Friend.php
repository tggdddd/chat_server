<?php

namespace app\common\model;

use app\BaseModel;

class Friend extends BaseModel
{
//    方法
    /**
     * 获取好友关系
     * @param $my string friendModel  owner_id
     * @param $he string friendModel  friend_id
     * @return array[my=>[id,has,block],he=>[id,has,block]]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userRelative($my, $he)
    {
        $model = new Friend();
        $myF = $model->where("user_id", $my)->where("friend_id", $he)->field("id,is_block")->find();
        $model = new Friend();
        $heF = $model->where("user_id", $he)->where("friend_id", $my)->field("id,is_block")->find();
        return [
            "my" => [
                "id" => empty($myF) ? 0 : $myF->getAttr("id"),
                "has" => !empty($myF),
                "block" => empty($myF) ? 0 : $myF->getAttr("is_block")
            ],
            "he" => [
                "id" => empty($heF) ? 0 : $heF->getAttr("id"),
                "has" => !empty($heF),
                "block" => empty($heF) ? 0 : $heF->getAttr("is_block")
            ],
        ];
    }

//    关联
    public function owner()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo('\app\common\model\User', 'friend_id', 'id');
    }
}