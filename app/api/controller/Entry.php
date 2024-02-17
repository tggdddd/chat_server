<?php

namespace app\api\controller;

use app\BaseController;
use app\common\enums\SmsCodeType;
use app\common\enums\UserBind;

class Entry extends BaseController
{
    public function loginByPassword()
    {
        $params = $this->request->param();
        $this->validate($params, [
            "username" => "require",
            "password" => "require|length:6,20"
        ], [
            "username" => "账号不能为空",
            "password.require" => "密码不能为空",
            "password.length" => "密码长度需要为6-20个字符"
        ]);
        $user = app('app\\common\\model\\User');
        $result = $user->loginByUserName($params['username'], $params['password']);
        if (empty($result)) {
            $this->error($user->error);
        }
        $token = gen_token($result->id);
        $this->success("登录成功", ['token' => $token,
            'userInfo' => $result->getUserInfo()]);
    }

    public function loginByPhone()
    {
        $params = $this->request->param();
        $this->validate($params, [
            "phone" => "require|mobile",
            "code" => "require",
        ], [
            "phone.require" => "手机号不能为空",
            "phone.mobile" => "手机号格式不正确",
            "code" => "验证码不能为空",
        ]);
//        验证 验证码
        $sms = app('app\\common\\model\\Sms');
        $sms->checkCode($params['phone'], $params['code'], SmsCodeType::LOGIN->value)
        or $this->error($sms->error);
        // 登录操作
        $user = app('app\\common\\model\\User');
        $result = $user->loginByPhone($params['phone']);
        if (empty($result)) {
//            注册
            $result = $user->register($params);
            if (empty($result)) {
                $this->error($user->error);
            }
            $this->success("登录成功", ['token' => gen_token($result),
                'userInfo' => $user->getUserInfo($result)]);
        }
        $token = gen_token($result->id);
        $this->success("登录成功", ['token' => $token,
            'userInfo' => $result->getUserInfo()]);
    }

    public function loginByWechat()
    {
        $params = $this->request->param();
//        $this->validate($params, [
//            "phone" => "require|mobile",
//            "code" => "require",
//            "password" => "require|length:6,20"
//        ], [
//            "phone.require" => "手机号不能为空",
//            "phone.mobile" => "手机号格式不正确",
//            "code" => "验证码不能为空",
//            "password.require" => "密码不能为空",
//            "password.length" => "密码长度需要为6-20个字符"
//        ]);
//       todo 获取openid
        $openid = 1;
        $user = app('app\\common\\model\\User');
        $result = $user->loginByBind($openid, UserBind::WECHAT->value);
        if (empty($result)) {
            $this->error($user->error);
        }
        $token = gen_token($result->id);
        $this->success("登录成功", ['token' => $token,
            'userInfo' => $result->getUserInfo()]);
    }

    public function register()
    {
        $user = app('app\\common\\model\\User');
        $params = $this->request->param();

        $this->validate($params, [
            "phone" => "require|mobile",
            "code" => "require",
        ], [
            "phone.require" => "手机号不能为空",
            "phone.mobile" => "手机号格式不正确",
            "code" => "验证码不能为空",
        ]);
//        验证 验证码
        $sms = app('app\\common\\model\\Sms');
        $sms->checkCode($params['phone'], $params['code'], SmsCodeType::REGISTER->value)
        or $this->error($sms->error);
        $u = $user->register($params);
        if (empty($u)) {
            $this->error($user->error);
        }
        $this->success("注册成功");
    }

    public function logout()
    {
        del_token($this->token);
        $this->success("退出成功");
    }

    public function sendCode()
    {
        $params = $this->request->param();
        $this->validate($params, [
            "phone" => "require|mobile",
            "type" => "require",
        ], [
            "phone.require" => "手机号不能为空",
            "phone.mobile" => "手机号格式不正确",
            "type" => "验证码类型不能为空"
        ]);
        $sms = app('app\\common\\model\\Sms');
        $result = $sms->sendCode($params['phone'], $params['type']);
        if ($result) {
            $this->success("发送成功");
        }
        $this->error($sms->error);
    }

    public function forget()
    {
        $params = $this->request->param();
        $this->validate($params, [
            "phone" => "require|mobile",
            "code" => "require",
            "password" => "require|length:6,20"
        ], [
            "phone.require" => "手机号不能为空",
            "phone.mobile" => "手机号格式不正确",
            "code" => "验证码不能为空",
            "password.require" => "密码不能为空",
            "password.length" => "密码长度需要为6-20个字符"
        ]);
//        验证 验证码
        $sms = app('app\\common\\model\\Sms');
        if (!$sms->checkCode($params['phone'], $params['code'])) {
            $this->error($sms->error);
        }
        // 登录操作
        $user = app('app\\common\\model\\User');
        $result = $user->loginByPhone($params['phone']);
        if (empty($result)) {
            $this->error($user->error);
        }
        $salt = random_str(6);
        $result->update([
            'confuse' => $salt,
            'password' => md5($params['password'], $salt)
        ]);
        $token = gen_token($result->id);
        $this->success("密码已重置", ['token' => $token,
            'userInfo' => $result->getUserInfo()]);

    }
}