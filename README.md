# Aliyun RDS/OSS Service

主要针对三个点：
```
1、RDS 逻辑备份
2、RDS 备份文件下载
3、RDS 备份文件上传至 OSS
```
## 目录结构
```$xslt
src 目录下存放源代码
    src/demos 目录存放案例
    src/config.php 针对案例的配置文件 
    src/AliRds.php Ali RDS 服务类
    src/AliOss.php Ali OSS 服务类
aliyun 目录下存放各类阿里云服务的 SDK 
```

## 使用
首先 composer install 安装 aliyuncs/oss-sdk-php 包

如果要运行 demos 下的案例，先要配置 src/config.php
```$xslt
dbInstanceId => 数据库实例的ID
accessKeyId  => 阿里云访问ID
accessSecret => 阿里云访问秘钥
endPoint     => oss 所在地域（如http://oss-cn-hangzhou.aliyuncs.com）
regionId     => rds 所在地域ID（如cn-hangzhou）
dnNames      => rds 数据库列表（如"db1,db2"）;
bucket       => oss 存储空间名称
root         => oss 存储文件路径(或者叫前缀);
```

