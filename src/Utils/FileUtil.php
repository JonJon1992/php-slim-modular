<?php

namespace Core\Utils;

class FileUtil
{
    /**
     * @param string $fileName
     * @param string $prefix
     * @return string
     */
    public static function generateNameRandom(string $fileName, string $prefix = ''): string
    {
        $timeStamp = strtotime(date('Y-m-d H:i:s'));
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $prefix = $prefix ? $prefix . '_' : '';
        return $prefix . random_int(0, 1000000) . $timeStamp . '-' . $ext;
    }

}