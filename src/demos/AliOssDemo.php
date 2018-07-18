<?php
namespace Quickwise\Ali\Demos;

require_once __DIR__ . "/../config.php";

use Aliyun;
use Quickwise\Ali\AliOss;

class AliOssDemo
{

    /**
     * 查看所有存储空间
     */
    public static function showBucketList(){

        $aliOss = new AliOss(Aliyun::$accessKeyId, Aliyun::$accessSecret, Aliyun::$endPoint);

        $result = $aliOss->getBucketListInfo();
        print($result);
        exit();
    }

    /**
     * 查看指定存储空间下指定前缀的所有对象
     */
    public static function showObjectList(){

        $aliOss = new AliOss(Aliyun::$accessKeyId, Aliyun::$accessSecret, Aliyun::$endPoint);

        $bucket  = Aliyun::$bucket;
        $options = [
            'prefix' => Aliyun::$root
        ];

        $result = $aliOss->getObjectListInfo($bucket,$options);
        print_r($result);
        exit();
    }

}