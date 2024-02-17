<?php

namespace app\common\model;

use app\BaseModel;

class Sms extends BaseModel
{
    /**
     * 发送短信
     * @param $phone
     * @param $data
     * @param $templateId
     * @return bool
     */
    public function sendSms($phone, $data, $templateId): bool
    {
        //todo
        return true;
    }

    /**
     * 发送短信验证码
     * @param $phone
     * @param $type
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendCode($phone, $type)
    {
        $code = random_str(6);
        $record = $this->where('phone', $phone)
            ->where('type', $type)
            ->order('id', 'desc')
            ->find();
        $config = app('app\common\model\Config');
        $setting = $config->getSetting('sms');
        if (!empty($record)) {
            if (strtotime($record['create_time']) > time() - $setting['code_expire']) {
                $this->error = "验证码发送频繁，请稍后再试";
                return false;
            }
        }
        if (!$this->sendSms($phone, ['code' => $code], $setting['code_template_id'])) {
            $this->error = "短信发送失败";
            return false;
        }
        $result = $this->save(['code' => $code, 'phone' => $phone, 'type' => $type]);
        if (empty($result)) {
            $this->error = "服务器繁忙";
            return false;
        }
        return $code;
    }

    public function checkCode($phone, $code, $type)
    {
        $record = $this->where('phone', $phone)
            ->where('type', $type)
            ->where('code', $code)
            ->where('is_used',0)
            ->order('id', 'desc')
            ->find();
        if (!empty($record)) {
            $config = app('app\common\model\Config');
            $setting = $config->getSetting('sms');
            if ($record['create_time'] < time() - $setting['code_expire']) {
                $this->error = "验证码过期，请重新获取";
                return false;
            }
            $record->where('id',$record->id)->update([
                "is_used" => 1,
            ]);
            return true;
        }
        $this->error = "验证码错误";
        return false;
    }
}