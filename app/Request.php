<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    public $customResponse = [];
    public function setCustomResponse($value)
    {
        $this->customResponse = $value;
    }

    public function getCustomResponse(){
        return $this->customResponse;
    }
}
