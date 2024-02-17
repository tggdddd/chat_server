<?php

namespace app\common\model;

use app\BaseModel;

class Config extends BaseModel
{
    public static function onAfterUpdate($model)
    {
        parent::onAfterUpdate($model);
        cache('config:' . $model->getAttr('field'), null);
    }

    public function getSetting(string $field)
    {
        $explode = explode(".", $field);
        $field = array_shift($explode);
        $cache = cache('config:' . $field);
        if (empty($cache)) {
            $cache = $this->where('field', $field)->value('value');
            if (is_null($cache)) {
                $cache = $this->getDefault($field);
            } else if (
                (str_ends_with('}', $cache) && str_ends_with('{', $cache))
                ||
                (str_ends_with(']', $cache) && str_ends_with('[', $cache))) {
                $cache = json_decode($cache);
            }
            cache('config:' . $field, $cache);
        }
        if (count($explode)) {
            return compact($cache . implode("", array_map(fn($v) => "[$v]", $explode)));
        }
        return $cache;
    }

    public function getDefault($field=null)
    {
        $default = [
            'sms' => [
                'code_expire' => 60000,
                'code_template_id' => 0
            ],
            'wechat' => [
                'mini' => [
                    'app_id' => '',
                    'secret' => '',
                    'token' => '',
                    'aes_key'=>''
                ],
            ],
        ];
        if(is_null($field)){
            return $default;
        }
        return $default[$field] ?? '';
    }
}