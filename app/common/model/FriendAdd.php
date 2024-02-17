<?php

namespace app\common\model;
use app\BaseModel;

class FriendAdd extends BaseModel{


//    绑定

    /**
     * 添加用户的好友关系
     * @return \think\model\relation\BelongsTo
     */
public function ownerRelation(){
    return $this->belongsTo('app\common\model\Friend','user_id','id');
}

    /**
     * 被添加用户的好友关系
     * @return \think\model\relation\BelongsTo
     */
public function userRelation(){
    return $this->belongsTo('app\common\model\Friend','friend_id','id');
}
    /**
     * 添加所有者
     * @return \think\model\relation\BelongsTo
     */
public function owner(){
    return $this->belongsTo('app\common\model\User','user_id','id');
}

    /**
     * 被添加用户
     * @return \think\model\relation\BelongsTo
     */
public function user(){
    return $this->belongsTo('app\common\model\User','friend_id','id');
}
}