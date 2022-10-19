<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Model\AsConfig;
use support\Request;
use support\Response;

class UploadController extends Base
{
    /**
     * 通用上传
     *
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request): Response
    {
        $file = $request->file('file');
        $uploadPath = $request->input('path', 'uploads');

        if (!$file) return $this->errorParam();

        try {
            $basePath = config('plugin.onlyoung4u.as-api.app.upload_file.base_path', 'storage');

            $path = as_file_date_path($file->getUploadExtension(), as_path_combine($basePath, $uploadPath));

            $file->move(public_path($path));

            $name = $file->getUploadName();
            $url = as_get_file_url($path);

            return $this->success(compact('name', 'path', 'url'));
        } catch (\Throwable) {
            return $this->error('上传失败');
        }
    }

    /**
     * 分片上传
     *
     * @param Request $request
     * @return Response
     */
    public function uploadSlice(Request $request): Response
    {
        $file = $request->file('file');

        $id = $request->input('id');
        $name = $request->input('name');
        $index = $request->input('index');
        $total = $request->input('total');
        $uploadPath = $request->input('path', 'uploads');

        if (!$file || empty($id)) return $this->errorParam();

        try {
            // 文件分片名称
            $fileName = $id . '_' . $index;
            // 文件上传路径处理
            $basePath = config('plugin.onlyoung4u.as-api.app.upload_file.base_path', 'storage');
            $uploadPath = as_path_combine($basePath, $uploadPath);
            // 文件分片存储目录
            $path = as_file_date_path('', $uploadPath, $id);
            // 移动文件分片
            $file->move(public_path(as_path_combine($path, $fileName)));

            // 合并文件
            if ($index + 1 == $total) {
                // 获取合并后的文件全路径
                $mergeFilePath = as_file_date_path(as_get_file_extension($name), $uploadPath);

                // 创建文件
                $mergeFile = fopen(public_path($mergeFilePath), 'wb');

                $i = 0;

                // 合并
                while ($i < $total) {
                    $blob = file_get_contents(public_path(as_path_combine($path, $id . '_' . $i)));
                    fwrite($mergeFile, $blob);
                    $i++;
                }

                @fclose($mergeFile);

                $i = 0;

                // 删除文件分片和目录
                while ($i < $total) {
                    @unlink(public_path(as_path_combine($path, $id . '_' . $i)));
                    $i++;
                }
                @rmdir(public_path($path));

                return $this->success([
                    'name' => $name,
                    'path' => $mergeFilePath,
                    'url' => as_get_file_url($mergeFilePath),
                ]);
            }

            return $this->success();
        } catch (\Throwable) {
            return $this->error('上传失败');
        }
    }
}