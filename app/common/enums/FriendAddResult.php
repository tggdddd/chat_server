<?php

namespace app\common\enums;

enum FriendAddResult:int
{
    case PENDING = 0; // 等待验证
    case SUCCESS = 1; // 添加成功
    case FAILED = 2; // 添加失败
    case BLOCKED = 3; // 已被拉黑
}