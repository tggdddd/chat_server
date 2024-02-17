<?php

namespace app\common;

class LoggerRequest
{
    public function handle()
    {
        $request = \request();
        $resp = $request->getCustomResponse();
        $model = app('app\common\model\Log');
        $model->save([
            'params' => json_encode($request->param()),
            'module' => $request->root(),
            'controller' => $request->controller(),
            'action' => $request->action(),
            'code' => $resp['code'],
            'msg' => $resp['msg'],
            'return' => json_encode($resp['data']),
            'method' => $request->method(),
            'return_type' => empty($resp) ? 1 : 0,
            'ip' => $request->ip(),
            'client' => $request->header('User-agent'),
            'create_time'=>date('y-m-d H:i:s',$request->time())
        ]);
    }
}