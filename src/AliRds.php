<?php
namespace Quickwise\Ali;

require_once __DIR__ . '/../aliyun/aliyun-php-sdk-core/Config.php';

use DefaultAcsClient;
use DefaultProfile;
use Rds\Request\V20140815\CreateBackupRequest;
use Rds\Request\V20140815\DescribeBackupsRequest;
use ServerException;
use ClientException;

/**
 * Aliyun RDS Service
 *
 * Class AliRds
 * @package Quickwise\Ali
 */
class AliRds
{
    protected $regionId;
    protected $accessKeyId;
    protected $accessSecret;

    protected $clientProfile = null;
    protected $acsClient     = null;

    protected $request  = null;
    protected $response = null;

    // 错误代码常量
    const ERROR_CODE_ACTION = 110;
    const ERROR_CODE_RESPONSE = 111;
    const ERROR_CODE_NOT_INSTANCE = 112;
    const ERROR_CODE_DBNAME_ISNULL = 113;

    public function __construct($regionId = '',$accessKeyId = '',$accessSecret = '')
    {
        $this->regionId     = $regionId;
        $this->accessKeyId  = $accessKeyId;
        $this->accessSecret = $accessSecret;

        $this->getClientProfile();
    }

    public function getRegionId(){
        return $this->regionId;
    }

    public function getAccessKeyId(){
        return $this->accessKeyId;
    }

    public function getAccessSecret(){
        return $this->accessSecret;
    }

    /**
     * 获取客户端属性对象
     *
     * @return DefaultProfile
     */
    public function getClientProfile(){

        if(is_null($this->clientProfile))
            $this->clientProfile = DefaultProfile::getProfile(
                $this->regionId,
                $this->accessKeyId,
                $this->accessSecret
            );

        return $this->clientProfile;
    }

    /**
     * 获取客户端对象
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient(){

        if(is_null($this->acsClient))
            $this->acsClient = new DefaultAcsClient($this->clientProfile);

        return $this->acsClient;
    }

    /**
     * 获取不同 API 对应的请求对象
     *
     * @param $action
     *
     * @return bool
     */
    public function getActionRequest($action){

        $class = 'Rds\Request\V20140815\\' . $action . 'Request';

        if(!class_exists($class)){
            throw new ClientException("Action: {$action} Is Not Exists",self::ERROR_CODE_ACTION);
        }

        return $this->request = new $class();
    }

    /**
     * 获取对应请求对象得到响应
     *
     * @param null $request
     *
     * @return bool|mixed|\SimpleXMLElement
     */
    public function getActionResponse($request = null){

        if(is_null($request) || is_null($this->request)){
            throw new ClientException('Request Is Not Allow Null',self::ERROR_CODE_RESPONSE);
        }

        try {
            if(is_null($this->acsClient))
                return $this->response = $this->getAcsClient()->getAcsResponse($request);
            return $this->response = $this->acsClient->getAcsResponse($request);
        } catch (ServerException $e) {
            print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
            die();
        } catch (ClientException $e) {
            print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
            die();
        }
    }

    /**
     * 创建备份
     *
     * @param        $dbInstanceId [数据库实例]
     * @param        $dbName       [备份数据库列表，以英文逗号分隔的字符串，仅在逻辑备份时有效]
     * @param string $method       [备份方法，Logical/Physical，默认为 Logical]
     * @param string $backupType   [备份类型，Auto/FullBackup，默认为 Auto]
     *
     * @return bool|mixed|\SimpleXMLElement
     * @throws ClientException
     */
    public function createBackup($dbInstanceId, $dbName = '', $method = 'Logical', $backupType = 'Auto'){

        if (is_null($this->request)) {
            $this->getActionRequest('CreateBackup');
        }

        if (!$this->request instanceof CreateBackupRequest) {
            throw new ClientException("The Action is Not Instance for CreateBackupRequest",self::ERROR_CODE_NOT_INSTANCE);
        }

        if ($method == 'Logical' && $dbName == '') {
            throw new ClientException("The DBName is Null",self::ERROR_CODE_DBNAME_ISNULL);
        }

        $this->request->setDBInstanceId($dbInstanceId);
        $this->request->setBackupMethod($method);
        $this->request->setDBName($dbName);
        $this->request->setBackupType($backupType);

        return $this->getActionResponse($this->request);
    }

    /**
     * @param        $dbInstanceId
     * @param        $startTime
     * @param        $endTime
     * @param string $dbName
     * @param string $method
     *
     * @return array|bool|null
     * @throws ClientException
     */
    public function downloadBackup($dbInstanceId, $startTime, $endTime, $targetPath, $dbName = '', $method = 'Logical'){
        if (is_null($this->request)) {
            $this->getActionRequest('DescribeBackups');
        }

        if (!$this->request instanceof DescribeBackupsRequest) {
            throw new ClientException("The Action is Not Instance for DescribeBackupsRequest",self::ERROR_CODE_NOT_INSTANCE);
        }

        if ($method == 'Logical' && $dbName == '') {
            throw new ClientException("The DBName is Null",self::ERROR_CODE_DBNAME_ISNULL);
        }

        $this->request->setDBInstanceId($dbInstanceId);
        $this->request->setStartTime($startTime);
        $this->request->setEndTime($endTime);

        echo "Start Get Backup List: " . date("Y-m-d H:i:s") . PHP_EOL;

        try {
            $this->response = $this->getActionResponse($this->request);
        } catch (ServerException $e) {
            echo "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . PHP_EOL;
            return false;
        } catch (ClientException $e) {
            echo "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . PHP_EOL;
            return false;
        }

        if(empty($this->response->Items->Backup)){
            return false;
        }

        // 下载到本地的文件信息
        $result = null;

        // 遍历备份列表，寻找符合条件的备份文件
        foreach ($this->response->Items->Backup as $item) {
            if ($item->BackupMethod == $method && $item->BackupDBNames == $dbName) {
                echo "Begin Start Download Backup File To Local: " . date("Y-m-d H:i:s") . PHP_EOL;
                $localPath = $this->downloadFileByFread($item->BackupDownloadURL, $targetPath);
                if ($localPath) {
                    $result = [
                        'local_path' => $localPath,
                        'name'       => $item->BackupStartTime
                    ];
                    break;
                } else {
                    echo "The Backup File Is Not Exists" . PHP_EOL;
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * 下载源文件内容到本地文件，用以上传使用
     *
     * 读取大文件报500
     *
     * @param $sourceFile
     * @param $targetFile
     *
     * @return bool
     */
    private function downloadFile($sourceFile, $targetFile){

        $fileContent = file_get_contents($sourceFile);

        if (file_put_contents($targetFile, $fileContent)) {
            echo "Downloaded to " . $targetFile . ' Success' . PHP_EOL;
            return $targetFile;
        } else {
            echo "--Download Failed " . $targetFile . PHP_EOL;
            return false;
        }

    }

    /**
     * 通过 fread 下载文件
     * (控制内存占用，同时设置 set_time_limit 为0，确保读取不会超时)
     *
     * @param $sourceFile
     * @param $targetFile
     */
    private function downloadFileByFread($sourceFile, $targetFile){

        $readHandle  = fopen($sourceFile, "rb");
        $writeHandle = fopen($targetFile, "a");

        $header = get_headers($sourceFile,1);
        $size   = $header['Content-Length'];

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

        set_time_limit(0);

        $chunkSize = 1024 * 1024;
        while(!feof($readHandle))
        {
            $data = @fread($readHandle, $chunkSize);
            fwrite($writeHandle, $data);
        }

        fclose($readHandle);
        fclose($writeHandle);

        echo "Downloaded to " . $targetFile . ' Success' . PHP_EOL;
        return $targetFile;
    }

}