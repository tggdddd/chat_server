<?php

namespace app\api\controller;

use app\BaseController;
use app\common\enums\FriendAddOperate;
use app\common\enums\FriendAddResult;
use app\common\enums\FriendAddType;
use think\App;
use think\Db;

class Friend extends BaseController
{
    protected $access = [];
    protected \app\common\model\Friend $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = \app('\app\common\model\Friend');
    }


    /**
     * 发送好友添加消息
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendAddMsg()
    {
        $params = $this->request->param();
        $this->validate($params,
            ['id' => 'require', 'type' => 'require', 'msg' => 'require'],
            ['id' => '用户不存在', 'type' => '操作非法', 'msg' => '消息不能为空']
        );
        if (is_null(FriendAddType::tryFrom($params['type']))) {
            $this->error("未知的添加方式");
        }
        $record = $this->model->where('user_id', $this->user)
            ->where('friend_id', $params['id'])
            ->find();
        if (!empty($record)) {
            if ($record->getAttr('is_black')) {
                $this->error("该用户已在黑名单中，请先解除");
            }
            $this->error("已添加为好友");
        }
        $block = $this->model
            ->where('user_id', $params['id'])
            ->where('friend_id', $this->user)
            ->where('is_block', 1)
            ->count();
        if ($block) {
            $this->error("已被拉黑");
        }
        $record = app('app\common\model\FriendAdd')->save([
            "user_id" => $this->uid,
            "friend_id" => $params['id'],
            "type" => $params['type'],
            "msg" => $params['msg']
        ]);
        if (empty($record)) {
            $this->error("添加失败,请重试");
        }
//        todo 通知事件
        $this->success("已发送请求");
    }

    /**
     * 好友添加处理
     */
    public function addMsgOperate()
    {
        $params = $this->request->param();
        $this->validate($params,
            ['id' => 'require', 'operate' => 'require'],
            ['id' => '参数有误', 'operate' => '操作非法']
        );
        if (is_null(FriendAddOperate::tryFrom($params['operate']))) {
            $this->error("操作非法");
        }
        $record = $this->user->friendAdd()->find($params['id']);
        if (empty($record)) {
            $this->error("服务器异常，请刷新", 2, ['refresh' => true]);
        }
        if ($record->result != FriendAddResult::PENDING->value) {
            $this->error("已执行该操作", 1, ['status' => $record->result]);
        }
        if ($params['operate'] == FriendAddOperate::REJECT->value) {
            $record->save(['result' => FriendAddOperate::REJECT->value]);
            $this->success("已拒绝");
        }
        $relation = \app('app\common\model\Friend')->userRelative($this->uid, $record['friend_id']);
        if ($relation["my"]["block"]) {
            $this->error("已将该用户拉黑，请到黑名单中解除");
        }
        if ($relation["he"]["block"]) {
            $this->error("已被拉入黑名单");
        }
        $count = app('app\common\model\User')->where('id', $record['friend_id'])->count();
        if (empty($count)) {
            $this->error("该用户不存在");
        }
        if ($params['operate'] == FriendAddOperate::APPROVAL->value) {
            $this->model->startTrans();
            if (!$relation["my"]["has"]) {
                $result = $this->model->insert([
                    "user_id" => $this->uid,
                    "friend_id" => $record["friend_id"],
                    "type" => $record["type"],
                    "add_id" => $params['id']
                ]);
                if (empty($result)) {
                    $this->error("服务器异常100");
                }
            }
            if (!$relation["he"]["has"]) {
                $result = $this->model->insert([
                    "user_id" => $record["friend_id"],
                    "friend_id" => $this->uid,
                    "type" => $record["type"],
                    "add_id" => $params['id']
                ]);
                if (empty($result)) {
                    $this->model->rollback();
                    $this->error("服务器异常101");
                }
            }
            $result = $record->save(['result' => FriendAddOperate::APPROVAL->value]);
            if (empty($result)) {
                $this->model->rollback();
                $this->error("服务器异常102");
            }
            $this->model->commit();
            $this->success("添加成功");
        }
        $this->error("未知情况");
    }

    /**
     * 操作黑名单
     * @return void
     */
    public function black()
    {
        $params = $this->request->param();
        $this->validate($params,
            ['id' => 'require', 'op' => 'require'],
            ['id' => '用户不存在', 'op' => '操作非法']
        );
        $id = $this->request->param("id");
        $black = boolval($this->request->param("op"));
        $this->model->where("friend_id", $id)->where("user_id", $this->uid)
            ->update(["is_block" => $black]);
        $this->success($black ? "已拉黑" : "已取消拉黑");
    }

    /**
     * 好友列表
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        $uid = $this->uid;
        $orderBy = $this->request->param('orderBy', 'id');
        $sort = $this->request->param('sort', 'asc');
        $where = [];
        if (!empty($this->request->param("search"))) {
            $where[] = ['phone|nickname|username', 'like', "%" . $this->request->param("search") . "%"];
        }
        $result = app('app\common\model\User')
            ->withoutField("password,confuse,current_point,total_point,current_money,total_money")
            ->where($where)
            ->whereIn("id", $this->model
                ->where('user_id', $this->uid)->column('friend_id'))
            ->order($orderBy, $sort)
            ->select();
        foreach ($result as &$value) {
            $value->setAttr('info', app('app\common\model\Friend')->where('friend_id', $value['id'])
                ->where('user_id', $uid)->find()->toArray());
        }
        $this->success("", $result);
    }
}