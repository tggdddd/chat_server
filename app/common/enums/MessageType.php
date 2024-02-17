<?php

namespace app\common\enums;

enum MessageType: int
{
    case TEXT = 1;
    case VOICE = 2;
    case VIDEO = 3;
    case EXPRESSION = 4;
    case FILE = 5;
    case CARD = 6;
    case SHARE_LINK = 7;
}