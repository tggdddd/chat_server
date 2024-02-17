<?php
namespace app\common\enums;
enum FriendAddType:int{
    case PHONE=1;
    case CARD=2;
    case USERNAME=3;
    case RADIO=4;
    case NEAR=5;
}