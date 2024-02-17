<?php

namespace app\common\model;

use app\BaseModel;
use think\file\UploadedFile;

class File extends BaseModel
{
    /**
     * 保存上传的文件
     * @param $file
     * @return false|
     * [id,url,origin_name,path]
     */
    public function saveUploadFile($file): bool|array
    {
        if ($file instanceof UploadedFile) {
            $md5 = $file->md5();
            $uploadPath = root_path() . "upload";
            $filePath = "/" . substr($md5, 0, 2) . "/"
                . substr($md5, 2, 2) . "/"
                . time() . "/";
            if (!file_exists($uploadPath . $filePath)) {
                mkdir($uploadPath . $filePath, 0777, true);
            }
            $filePath .= $file->getOriginalName();
            $save = $this->save([
                'user_id' => $this->uid,
                'type' => $file->getType(),
                'ext' => $file->getExtension(),
                'origin_name' => $file->getOriginalName(),
                'file_name' => $file->getOriginalName(),
                "path" => $filePath
            ]);
            if ($save === false) {
                $this->error = "服务器异常";
                return false;
            }
            if (move_uploaded_file($file->getRealPath(), $uploadPath . $filePath)) {
                return [
                    "url" => url($filePath)->suffix(false)->domain(true)->build(),
                    "path" => $this['path'],
                    "originName" => $this['origin_name'],
                    "id" => $this['id']
                ];
            }
            $this->error = "系统异常";
            return false;
        }
        $this->error = "异常格式";
        return false;
    }


//    /**
//     * 保存上传的文件
//     * @param $file
//     * @return false|string 请求路径
//     */
//    public function saveUploadFile($file): bool|array
//    {
//        $md5 = md5_file($file['tmp_name']);
//        $uploadPath = root_path() . "upload";
//        $filePath = "/" . substr($md5, 0, 2) . "/"
//            . substr($md5, 2, 2) . "/"
//            . time() . "/";
//        if (!file_exists($uploadPath . $filePath)) {
//            mkdir($uploadPath . $filePath, 0777, true);
//        }
//        $filePath .= $file['name'];
//        if (false !== ($pos = strrpos($file['name'], '.'))) {
//            $ext = substr($file['name'], $pos);
//        }
//        $save = $this->save([
//            'user_id' => $this->uid,
//            'type' => $file['type'],
//            'ext' => $ext,
//            'origin_name' => $file['name'],
//            'file_name' => $file['name'],
//            "path" => $filePath
//        ]);
//        if ($save === false) {
//            $this->error = "服务器异常";
//            return false;
//        }
//        if (move_uploaded_file($file['tmp_name'], $uploadPath . $filePath)) {
//            return [
//                "url" => url($filePath)->suffix(false)->domain(true)->build(),
//                "path" => $this['path'],
//                "originName" => $this['origin_name'],
//                "id" => $this['id']
//            ];
//        }
//        $this->error = "系统异常";
//        return false;
//    }
}
