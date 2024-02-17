<?php

namespace app\common\model;

use app\BaseModel;

class Message extends BaseModel
{
    public function getPrivateMsgByIds($ids, $user_id)
    {
        return $this->where('to_id', $user_id)
            ->where('id', 'in', $ids)
            ->order('id', 'asc')
            ->select();
    }
    public function getUnReadPrivateMsg($from,$to){
        return $this->where('to_user_id', $to)
            ->where('from_user_id', $from)
            ->where('is_read',0)
            ->order('id', 'asc')
            ->select();
    }

    public function getGroupChatMsg($group_id, $user_id)
    {
        $count = app('app\common\model\GroupChatUser')
            ->where('group_chat_id', $group_id)
            ->where('user_id', $user_id)
            ->count();
        if (empty($count)) {
            return [];
        }
        return $this->where('is_group_chat', 1)
            ->where('group_chat_id', $group_id)
            ->order('id', 'asc')
            ->select();
    }
}