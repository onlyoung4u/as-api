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
            $basePath = config('plugin.onlyoung4u.as-api.app.upload_base_path', 'storage');

            $path = as_file_date_path($file->getUploadExtension(), as_path_combine($basePath, $uploadPath));

            $file->move(public_path($path));

            $name = $file->getUploadName();
            $url = AsConfig::getFileUrl($path);

            return $this->success(compact('name', 'path', 'url'));
        } catch (\Throwable) {
            return $this->error('上传失败');
        }
    }
}