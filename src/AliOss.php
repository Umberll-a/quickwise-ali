<?php
namespace Quickwise\Ali;

require_once __DIR__.'/../vendor/autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Aliyun RDS Service
 *
 * Class AliRds
 * @package Quickwise\Ali
 */
class AliOss
{
    protected $endPoint;
    protected $accessKeyId;
    protected $accessSecret;

    protected $ossClient = null;

    protected $bucket;

    public function __construct($accessKeyId = '', $accessSecret = '', $endPoint = ''){

        $this->endPoint     = $endPoint;
        $this->accessKeyId  = $accessKeyId;
        $this->accessSecret = $accessSecret;

        $this->getOssClient();
    }

    public function getEndPoint(){
        return $this->endPoint;
    }

    public function getAccessKeyId(){
        return $this->accessKeyId;
    }

    public function getAccessSecret(){
        return $this->accessSecret;
    }

    /**
     * 获取 OSS 客户端对象
     *
     * @return null|OssClient
     */
    public function getOssClient(){
        if(is_null($this->ossClient)){
            try{
                $this->ossClient = new OssClient(
                    $this->accessKeyId,
                    $this->accessSecret,
                    $this->endPoint
                );
            }catch(OssException $e){
                print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
                die();
            }
        }

        return $this->ossClient;
    }

    /**
     * 获取所有 Bucket 列表
     */
    public function getBucketListInfo(){
        try {
            return $this->ossClient->listBuckets();
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     *  判断 Bucket 是否存在
     *
     * @param string $bucket 存储空间名称
     */
    public function doesBucketExist($bucket){
        try {
            return $this->ossClient->doesBucketExist($bucket);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     * 获取 Bucket 访问权限
     *
     * @param $bucket
     */
    public function getBucketAcl($bucket){
        try {
            return $this->ossClient->getBucketAcl($bucket);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     * 获取 Bucket 地域信息
     *
     * @param $bucket
     */
    public function getBucketLocation($bucket){
        try {
            return $this->ossClient->getBucketLocation($bucket);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     * 获取 Bucket 元信息
     *
     * @param $bucket
     */
    public function getBucketMeta($bucket){
        try {
            return $this->ossClient->getBucketMeta($bucket);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     * 获取 Bucket 中所有文件
     *
     * @param       $bucket
     * @param array $options
     */
    public function getObjectListInfo($bucket,$options = NULL){
        try {
            return $this->ossClient->listObjects($bucket, $options);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    /**
     * 文件上传的封装
     * 自动根据文件大小判断使用普通上传还是分片上传
     * 上传成功返回文件地址
     *
     * @param      $bucket
     * @param      $object
     * @param      $filePath
     * @param null $options
     */
    public function multiuploadFile($bucket, $object, $filePath, $options = NULL){
        try{
            return $this->ossClient->multiuploadFile($bucket, $object, $filePath, $options);
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }
}