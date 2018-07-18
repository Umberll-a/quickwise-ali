<?php
namespace Quickwise\Ali\Demos;

require_once __DIR__ . "/../config.php";

use Aliyun;
use Quickwise\Ali\AliRds;
use Quickwise\Ali\AliOss;

class AliRdsDemo
{
    /**
     * 创建逻辑单库备份
     * 返回 RequestId，备份需要几分钟的时间
     */
    public static function createBackup(){

        $aliRds   = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);
        $response = $aliRds->createBackup(Aliyun::$dbInstanceId,Aliyun::$dnNames);
        print_r($response);
        exit();
    }

    /**
     * 下载最新逻辑单库备份文件到本地
     *
     * @param $localPath
     */
    public static function downloadBackupToLocal(){

        $localPath = '/logical_sql.tar';

        $startTime = date('Y-m-d\TH:i\Z',strtotime('-7 day'));
        $endTime   = date('Y-m-d\TH:i\Z');

        $aliRds   = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);
        $response = $aliRds->downloadBackup(Aliyun::$dbInstanceId, $startTime, $endTime, $localPath, Aliyun::$dnNames);
        print_r($response);
        exit();
    }

    /**
     * 创建备份，并上传至OSS
     *
     * @return bool
     */
    public static function createBackupAndUploadToOss(){

        $startTime = date('Y-m-d\TH:i\Z',strtotime('-7 day'));
        $endTime   = date('Y-m-d\TH:i\Z');

        $localPath = '/logical_sql.tar';

        $aliRds   = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);
        $response = $aliRds->createBackup(Aliyun::$dbInstanceId, Aliyun::$dnNames);
        if($response){
            $result = $aliRds->downloadBackup(Aliyun::$dbInstanceId, $startTime, $endTime, $localPath, Aliyun::$dnNames);
            if(!$result){
                echo "Download Backup File Failed" . PHP_EOL;
                exit();
            }
        }else{
            echo "Create Backup Failed" . PHP_EOL;
            exit();
        }

        echo "Begin Start Upload To OSS: " . date("Y-m-d H:i:s") . PHP_EOL;

        $bucket    = ALiyun::$bucket;
        $root      = Aliyun::$root;
        $object    = "{$root}{$result['name']}/.tar";

        $aliOss   = new AliOss(Aliyun::$accessKeyId, Aliyun::$accessSecret, Aliyun::$endPoint);
        $response = $aliOss->multiuploadFile($bucket,$object,$localPath);
        if (empty($response)) {
            @unlink($localPath);
            echo "Upload To OSS Failed! Delete Temp File" . PHP_EOL;
            return false;
        }

        echo "Upload To OSS Success" . PHP_EOL;
        echo "The File PATH Is: " . $response['oss-request-url'] . PHP_EOL;
        @unlink($localPath);
        echo "Delete Temp File Success" . PHP_EOL;
        exit();
    }

    /**
     * 查看RDS地域列表信息
     */
    public static function showRdsRegionList(){

        $aliRds = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);

        $request = $aliRds->getActionRequest('DescribeRegions');
        $response = $aliRds->getActionResponse($request);
        print_r($response);
        exit();
    }

    /**
     * 查看所有 RDS 实例列表
     */
    public static function showInstanceList(){

        $aliRds = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);

        $request = $aliRds->getActionRequest('DescribeDBInstances');
        print_r($aliRds->getActionResponse($request));
        exit();
    }

    /**
     * 查看当前实例属性
     */
    public static function showInstanceAttribute(){

        $aliRds = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);

        $request = $aliRds->getActionRequest('DescribeDBInstanceAttribute');
        $request->setDBInstanceId(Aliyun::$dbInstanceId);
        print_r($aliRds->getActionResponse($request));
        exit();
    }

    /**
     * 查看实例下所有数据库列表
     */
    public static function showDatabaseList(){

        $aliRds = new AliRds(Aliyun::$regionId, Aliyun::$accessKeyId, Aliyun::$accessSecret);

        // 查看数据库列表
        $request = $aliRds->getActionRequest('DescribeDatabases');
        $request->setDBInstanceId(Aliyun::$dbInstanceId);
        $request->setDBName(Aliyun::$dnNames);
        $request->setDBStatus('Running');
        $response = $aliRds->getActionResponse($request);
        print_r($response);
        exit();
    }

}