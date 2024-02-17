<?php
// 应用公共文件
/**
 * 生成一个token
 * @param $uid
 * @return string
 */
function gen_token($uid): string
{
    $uuid = uniqid();
    cache("token:" . $uuid, $uid);
    return $uuid;
}

/**
 * 根据token获取uid
 * @param $token
 * @return false|mixed|object|\think\App
 */
function validate_token($token): false|string
{
    if (empty($token)) {
        return false;
    }
    $uid = cache("token:" . $token);
    return empty($uid) ? false : $uid;
}

/**
 *  删除token
 * @param  $token
 * @return void
 */
function del_token($token): void
{
    cache("token:" . $token, null);
}

/**
 * 密码加密
 * @param $password string 密码
 * @param $confuse string 加盐
 * @return string
 */
function password_encrypt($password, $confuse): string
{
    return md5(md5($password . $confuse) . $confuse);
}

/**
 * 随机生成数字字母字符串
 * @param $len
 * @return string
 */
function random_str($len): string
{
    $str = "";
    while (strlen($str) < $len) {
        $str .= base_convert(rand(), 10, 36);
    }
    return strtoupper(substr($str, 0, $len));
}

/**
 * 获取请求url
 * 为空则返回默认值
 * @param $url string 本地或远程链接
 * @param $default string 默认值
 * @param $isRemote bool 默认值是否为远程链接
 * @return string
 */
function file_url($url, $default = "", $isRemote = false): string
{
    if (empty($url)) {
        return $isRemote ? $default : url($default)->suffix(false)->domain(true)->build();
    }
    if (str_starts_with($url, "http")) {
        return $url;
    }
    if (file_exists(root_path() . 'upload/' . $url)) {
        return url($url)->suffix(false)->domain(true)->build();
    }
    return $isRemote ? $default : url($default)->suffix(false)->domain(true)->build();
}