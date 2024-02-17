<?php
namespace app\admin;
use think\App;

class Config extends \app\BaseController{
    protected $access = [];
    protected \app\common\model\Config $model;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = app('app\\common\\model\\Config');

    }

    public function add(){
     $params = $this->request->param();
     $this->validate($params,
         ['field'=>'require','value'=>'require'],
         ['field.require'=>'配置项不能为空','value.require'=>'配置值不能为空']);
     $record = $this->model->where('field', $params['field'])->find();
     if(empty($record)){
         $this->model->save($params)?$this->success():$this->error();
     }else{
         $record->update($params);
     }
     $this->success();
 }
 public function delete($field){
     empty($this->model->delete(['field'=>$field]))?$this->error():$this->success();
 }
 public function list(){
     $result = $this->model->getDefault();
     $select = $this->model->select();
     foreach ($select as $record){
        $result[$record->getAttr('field')] = $record->getAttr('value');
     }
     $this->success("",$result);
 }
}