<?php

use Workerman\Connection\ConnectionInterface;
use Workerman\Lib\Timer;

enum DataTypeEnum: int
{
    case CONNECTED = 1;
    case PING = 2;
    case BROADCAST = 3;
}

enum ResponseCodeEnum: int
{
    case  SUCCESS = 0;
    case CLOSE = 1;
    case SERVER_ERROR = 2;
    case TIMEOUT = 3;
    case RECEIVE_PRIVATE_MSG = 4;
    case RECEIVE_GROUP_CHAT_MSG = 5;
}

abstract class IConnection extends ConnectionInterface
{
//    用户id
    public string $uid;
//    最后活跃时间
    public int $last;
//    定时器
    public int $timers;
}
const WEBSOCKET_HEAT_BEAT_TIMEOUT = 55000;
function send($code, $data = [])
{
    return json_encode(['code' => $code, 'data' => $data]);
}

function onWorkerStart($worker)
{
}

function onWorkerReload(\Workerman\Worker $worker)
{
}

function onConnect(IConnection $connection)
{
    $connection->timers = Timer::add(5, function () use ($connection) {
//      私聊消息
        $ids = app('app\common\model\Message')
            ->where('to_user', $connection->uid)
            ->where('is_read', 0)
            ->order('create_time','asc')
            ->group('from_user')
            ->field('from_user,count(id)');
        if (count($ids) > 0) {
            $connection->send(send(ResponseCodeEnum::RECEIVE_PRIVATE_MSG->value, $ids));
        }
//      群聊消息
        $groupList =app('app\common\model\Message')
            ->alias('message')
            ->join('group_chat_user group_chat', 'group_chat.group_chat_id = message.group_chat_id')
            ->field('group_chat.group_chat_id chat_id,count(message.id) count,last_msg_id last_id')
            ->where('is_group_chat', 1)
            ->where('user_id', $connection->uid)
            ->where('last_msg_id','>','message.id')
            ->group('group_chat.group_chat_id')
            ->select();
        if(count($ids)>0){
            $connection->send(send(ResponseCodeEnum::RECEIVE_GROUP_CHAT_MSG->value, $groupList));
        }
    });
}

function onMessage(IConnection $connection, $data)
{
    $data = json_decode($data);
    switch ($data['type']) {
        case DataTypeEnum::CONNECTED->value:
            $connection->last = time();
            $connection->uid = $data['uid'];
            $connection->send(send(ResponseCodeEnum::SUCCESS->value));
            break;
        case DataTypeEnum::PING->value:
//                超时关闭
            if (!empty($connection->last) && ($connection->last + WEBSOCKET_HEAT_BEAT_TIMEOUT < time())) {
                $connection->close();
                break;
            }
            $connection->last = time();
            break;
        case DataTypeEnum::BROADCAST->value:
            break;
        default :
            $connection->close();
    }
}

function onClose(ConnectionInterface $connection)
{
}

function onError(ConnectionInterface $connection, $code, $msg)
{
}
