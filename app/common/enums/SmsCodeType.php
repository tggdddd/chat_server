<?php

namespace app\common\enums;

enum SmsCodeType:int
{
case REGISTER = 1;
case LOGIN = 2;
case FORGET = 3;
}