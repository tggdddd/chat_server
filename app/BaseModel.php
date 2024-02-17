<?php

namespace app;

use think\facade\Session;
use think\Model;
use think\model\concern\SoftDelete;

class BaseModel extends Model
{

    use SoftDelete;

    protected $deleteTime = 'delete_time'; // 软删除字段名
    /*
     * 登录用户id
     *
     */
    public $uid;
    /*
     * 方法执行失败信息
     */
    public $error;
    public $m = false;

    public function __construct(array $data = [])
    {
        $this->uid = session("uid");
        parent::__construct($data);
    }

    public static function onAfterUpdate($model)
    {
        if ($model->m = true) {
            return;
        }
        $model->m = true;
        $model->save(['update_by' => $model->uid]);
    }

    public static function onAfterInsert($model)
    {
        $model->m = true;
        $model->save(['create_by' => $model->uid]);
    }
}