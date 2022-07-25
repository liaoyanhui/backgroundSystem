<?php

namespace app\common\utils\oss;

use app\common\exception\UploadException;
use OSS\Core\OssException;
use OSS\OssClient;
use think\Config;

class AliOssService
{
  public static function ossUpload($object, $file)
  {
    try {
      $isCName = !(strpos(Config::get('ali_oss.endpoint'), 'aliyuncs.com') > 0);
      $accessKeyId = Config::get('ali_oss.access_id');
      $accessKeySecret = Config::get('ali_oss.access_secret');
      $endpoint = Config::get('ali_oss.endpoint');
      $bucket = Config::get('ali_oss.bucket');
      $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, $isCName);
      $result = $ossClient->uploadFile($bucket, $object, $file);
    } catch (OssException $e) {
      throw new UploadException($e->getDetails());
    }

    return $result['info']['url'];
  }
}
