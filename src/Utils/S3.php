<?php

namespace Core\Utils;

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
     */
    public function saveS3($file_path, $file_path_local)
    {
        try {
            $info['ContentType'] = mime_content_type($file_path_local);
            return $this->bucket->putFile($file_path, $file_path_local, $info);
        } catch (\Exception $e) {

        }

    }
}