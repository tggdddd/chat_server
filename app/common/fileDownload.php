<?php

namespace app\common;

use app\Request;
use Closure;
use Grafika\Grafika;
use think\Response;

class fileDownload
{
    private $path = "";
    private $name = "";
    private $ext = "";

    public function handle(Request $request, Closure $next)
    {
        $filePath = root_path() . "upload/" . $request->pathinfo();
        if (file_exists($filePath)) {
            $filePath = realpath($filePath);
            if ($this->isImage($filePath)) {
                $width = $request->param('w');
                $height = $request->param('h');
                $quality = $request->param('q');
                $resize = $request->param('r');
                $format = $request->param('f');
                if (!empty($width) || !empty($height) || !empty($quality) || !empty($resize) || !empty($format)) {
                    $editor = Grafika::createEditor();
                    $editor->open($image, $filePath);
                    $width = empty($width) ? $image->getWidth() : $width;
                    $height = empty($height) ? $image->getHeight() : $height;
                    // 0-100
                    $quality = empty($quality) ? 75 : $quality;
                    // exact, exactHeight, exactWidth, fill, fit
                    $resize = empty($resize) ? 'fill' : $resize;
                    //  gif, png, jpeg, null
                    $format = empty($format) ? null : $format;
                    $filePath = $this->path . "/" . $this->name . "_{$width}_{$height}_{$quality}_{$resize}_{$format}" . $this->ext;
                    if (file_exists($filePath)) {
                        return download($filePath, $this->name . $this->ext);
                    }
                    $editor->resize($image, $width, $height, $resize);
                    $editor->save($image, $filePath, $format, $quality);
                    return download($filePath, $this->name . $this->ext);
                }
            }
            return download($filePath, $this->name . $this->ext);
        }
        return $next($request);
    }

    private function isImage($filePath): bool
    {
        $index = strrpos($filePath, DIRECTORY_SEPARATOR);
        $this->path = substr($filePath, 0, $index);
        $filePath = substr($filePath, $index + 1);
        $this->name = $filePath;
        $index = strrpos($filePath, ".");
        if ($index !== false) {
            $this->name = substr($filePath, 0, $index);
            $this->ext = substr($filePath, $index);
            if (in_array($this->ext, ['.jpg', '.jpeg', '.png', '.gif'])) {
                return true;
            }
        }
        return false;
    }
}