<?php

namespace app\common\model;

use app\BaseModel;

class User extends BaseModel
{
    /**
     * 获取用户信息
     * @param $uid
     * @return User|array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfo($uid=null){
        if(!empty($uid)){
            return $this->withoutField(["password", "confuse"])->find($uid);
        }
        return $this->hidden(["password", "confuse"]);
    }
    /**
     * 账号密码登录
     * @param $username
     * @param $password
     * @return User|false
     */
    public function loginByUserName($username, $password): false|User
    {
        $user = $this->where('username|phone', $username)->find();
        if (empty($user)) {
            $this->error = "账号不存在";
            return false;
        }
        if ($user->password != password_encrypt($password, $user->confuse)) {
            $this->error = "密码错误";
            return false;
        }
        return $user;
    }

    public function loginByPhone($phone): false|User
    {
        $user = $this->where('phone', $phone)->find();
        if (empty($user)) {
            $this->error = "手机号不存在";
            return false;
        }
        return $user;
    }

    public function loginByBind($openid, $openType): false|User
    {
        $bind = app('app\\common\\model\\UserBind')
            ->where("openid", $openid)
            ->where("type", $openType)
            ->find();
        if (empty($bind)) {
            $this->error = "当前未绑定账号";
            return false;
        }
        return $bind->user();
    }

    public function register($data = []):false|User
    {
//        默认值
        if (empty($data['phone'])) {
            $this->error = "手机号不存在";
            return false;
        }
        $user = $this->where('phone',$data['phone'])->find();
        if(!empty($user)){
            $this->error = "当前手机号已注册,请使用该手机号登录";
            return false;
        }
        empty($data['username']) && $data['username'] = $data['phone'];
        empty($data['nickname']) && $data['nickname'] = 'user_' . strtoupper(random_str(8));
        empty($data['password']) && $data['password'] = '123456';
//        配置
        $data['confuse'] = random_str(6);
        $data['password'] = password_encrypt($data['password'], $data['confuse']);
        $data['current_point'] = 0;
        $data['total_point'] = 0;
        $data['current_money'] = 0;
        $data['total_money'] = 0;
        $data['status'] = 0;
        $data['birthday'] = empty($data['birthday']) ? null : $data['birthday'];
        $result = $this->save($data);
        if(!$result){
            return false;
        }
        return $this;
    }
//    属性获取器
public function getAvatarAttr($value){
        return file_url($value,"/avatar/default.png");
}

//    模型绑定
    public function userBind()
    {
        return $this->hasMany('app\common\model\UserBind', "user_id", "id");
    }
    public function friend(){
        return $this->hasMany('app\common\model\Friend', "user_id", "id");
    }

    public function beFriend(){
        return $this->hasMany('app\common\model\Friend', "friend_id", "id");
    }
    public function friendAdd(){
        return $this->hasMany('app\common\model\FriendAdd', "user_id", "id");
    }
}