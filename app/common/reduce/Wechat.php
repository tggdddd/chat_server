<?php

namespace app\common\reduce;

use app\common\model\Config;
use think\Service;

class Wechat {
    protected Config $model;
    public function __construct(){
        $this->model = app('app\\common\\model\\Config');
    }

    public function getMiniSetting()
    {
        return $this->model->getSetting("wechat.mini");
    }
    public function getMiniClient(){
        $setting = $this->getMiniSetting();
        $app = new \EasyWeChat\MiniApp\Application([
            'app_id' => $setting['app_id'],
            'secret' => $setting['secret'],
            'token' => $setting['token'],
            'aes_key' => $setting['aes_key']
        ]);
        return $app->getClient();
    }
}