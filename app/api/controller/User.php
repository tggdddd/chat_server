<?php

namespace app\api\controller;

use app\BaseController;
use think\App;

class User extends BaseController
{
    protected $access = [];
    protected \app\common\model\User $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = app('app\\common\\model\\User');
    }

    /**
     * 获取个人信息
     * @return void
     */
    public function userInfo()
    {
        $userInfo = $this->model->getUserInfo($this->uid);
        empty($userInfo) ? $this->error() : $this->success("获取成功", $userInfo);
    }

    public function updateInfo()
    {
        $params = $this->request->param();
        $data = [];
        if (!empty($params["avatar"])) {
            $data["avatar"] = $params["avatar"];
        }
        if (!empty($params["nickName"])) {
            $data["nickname"] = $params["nickname"];
        }
//        todo 账号名修改 记录修改  限制字数
        if (!empty($params["brief"])) {
            $data["brief"] = $params["brief"];
        }
        if (!empty($params["birthday"])) {
            $data["birthday"] = $params["birthday"];
        }
        if (!empty($params["province"])) {
            $data["province"] = $params["province"];
        }
        if (!empty($params["city"])) {
            $data["city"] = $params["city"];
        }
        if (!empty($params["county"])) {
            $data["county"] = $params["county"];
        }
        if (!empty($params["password"])) {
            if (empty($params["passwordOld"])) {
                $this->error("请输入原密码");
            }
            if($this->user->password!=password_encrypt($params["passwordOld"],$this->user->confuse)){
                $this->error("旧密码输入错误");
            }
            $data["confuse"] = random_str(6);
            $data["password"] = password_encrypt($params["password"],$data["confuse"]);
        }
        $this->user->update($data);
        $this->success("修改成功");
    }

    /**
     * 修改定位位置
     */
    public function location()
    {
        $params = $this->request->param();
        $this->validate($params, [
            "x" => "require",
            "y" => "require"
        ], [
            "x" => "经度必须",
            "y" => "纬度必须"
        ]);
        $this->user->update([
            "x" => $params['x'],
            "y" => $params['y']
        ]);
        $this->success();
    }
}