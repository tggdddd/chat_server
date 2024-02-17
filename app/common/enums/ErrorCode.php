<?php

namespace app\common\enums;
enum ErrorCode: int
{
    case SUCCESS = 0; // 成功;
    case ERROR = 1; // 系统错误;
    case PARAM_ERROR = 2; // 参数错误;
    case  NOT_LOGIN = 401; // 未登录;
    case ACCOUNT_FREEZE = 402; //账号冻结
}