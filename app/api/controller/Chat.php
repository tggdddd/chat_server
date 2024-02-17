<?php

namespace app\api\controller;

use app\BaseController;
use app\common\enums\MessageType;

class Chat extends BaseController
{
    /**
     * 发送聊天消息
     * @return void
     */
    public function send()
    {
        $type = $this->request->param('type');
        $to_user = $this->request->param('to_user');
        $is_group = $this->request->param('is_group');
        $content = $this->request->param('content');
        empty($type) && $this->error("参数错误,type");
        empty($to_user) && $this->error("参数错误,user");
        empty($is_group) && $this->error("参数错误,valid");
        empty($content) && $this->error("参数错误,content");
        switch ($type) {
            case MessageType::TEXT->value:
            case MessageType::EXPRESSION->value:
            case MessageType::CARD->value:
            case MessageType::SHARE_LINK->value:
                $result = app('app\common\model\Message')
                    ->save([
                        'from_user_id' => $this->uid,
                        'to_user_id' => $to_user,
                        'msg_type' => $type,
                        'is_group_chat' => $is_group,
                        'content' => $content
                    ]);
                empty($result) && $this->error();
                $this->success();
            case MessageType::FILE->value:
            case MessageType::VIDEO->value:
            case MessageType::VOICE->value:
                $files = $this->request->file();
                foreach ($_FILES as $file) {
                    if ($file['error'] != 0) {
                        $this->error();
                    }
                }
                $model = app('app\\common\\model\\File');
                foreach ($files as $file) {
                    $r = $model->saveUploadFile($file);
                    if ($r === false) {
                        $this->error($model->error);
                    }
                    $result[] = $r;
                }
                $result = app('app\common\model\Message')
                    ->save([
                        'from_user_id' => $this->uid,
                        'to_user_id' => $to_user,
                        'msg_type' => $type,
                        'is_group_chat' => $is_group,
                        'content' => $result
                    ]);
                empty($result) && $this->error();
                $this->success();
        }
        $this->error();
    }

    /**
     * 获取私聊消息
     * @return void
     */
    public function privateMsg()
    {
        $id = $this->request->param('id');
        $list = app('app\common\model\Message')
            ->getUnReadPrivateMsg($id, $this->uid);
        $this->success("", $list);
    }

    /**
     * 获取群聊消息
     * @return void
     */
    public function groupMsg()
    {
        $id = $this->request->param('id');
        $list = app('app\common\model\Message')
            ->getGroupChatMsg($id, $this->uid);
        $this->success("", $list);
    }
}