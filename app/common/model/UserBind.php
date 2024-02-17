<?php

namespace app\common\model;

use app\BaseModel;

class UserBind extends BaseModel
{

//    模型绑定
    public function user()
    {
        return $this->belongsTo('app\common\model\User', "id", "user_id");
    }
}