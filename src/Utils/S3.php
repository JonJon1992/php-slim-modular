<?php

namespace Core\Utils;

use Aws\Result;
use Core\Exceptions\AppException;
use Exception;
use S3\Lib\Bucket;

class S3
{

    private Bucket $bucket;

    private function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;

    }

    /**
     * @param Bucket $bucket
     * @return S3
     */
    public static function create(Bucket $bucket): S3
    {
        return new S3($bucket);
    }

    /**
     * @param $file_path
     * @param $file_path_local
     * @return Result|void
     * @throws AppException
     */
    public function saveS3($file_path, $file_path_local)
    {
        try {
            $info = [];
            if (function_exists('mime_content_type')) {
                $info['ContentType'] = mime_content_type($file_path_local);
            }
            return $this->bucket->putFile($file_path, $file_path_local, $info);
        } catch (Exception $e) {
            abort($e->getMessage(), 500);
        }
    }

    /**
     * @param $file_path
     * @return string
     */
    public function generateUrl($file_path): string
    {
        return $this->bucket->fileUrl($file_path);

    }
}