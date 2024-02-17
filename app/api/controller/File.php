<?php
namespace app\api\controller;
class File extends \app\BaseController
{
    /**
     * 上传文件
     * @return void
     */
    public function upload()
    {
        $files = $this->request->file();
        foreach ($_FILES as $file){
            if($file['error'] != 0){
                $this->error();
            }
        }
        $model = app('app\\common\\model\\File');
        foreach ($files as $file) {
            $r = $model->saveUploadFile($file);
            if ($r === false) {
                $this->error($model->error);
            }
            $result[] = $r;
        }
        $this->success("上传成功", $result);
    }



//    /**
//     * 上传文件
//     * @return void
//     */
//    public function upload()
//    {
//        if (empty(count($_FILES))) {
//            $this->error("没有上传的文件");
//        }
//        $model = app('app\\common\\model\\File');
//        foreach ($_FILES as $key => $file) {
//            if ($file['error'] != 0) {
//                $this->error("上传失败，{$key}传输错误" . $file['error']);
//            }
//        }
//        foreach ($_FILES as $file) {
//            $r = $model->saveUploadFile($file);
//            if ($r === false) {
//                $this->error($model->error);
//            }
//            $result[] = $r;
//        }
//        $this->success("上传成功", $result);
//    }

}


