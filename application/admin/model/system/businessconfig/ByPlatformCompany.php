<?php

namespace app\admin\model\system\businessconfig;

use think\Model;
use traits\model\SoftDelete;

class ByPlatformCompany extends Model
{


  use SoftDelete;
  // 表名
  protected $name = 'platform_company';

  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';


  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  // protected $deleteTime = false;
  protected $deleteTime = 'deleted_at';


  // 追加属性
  protected $append = [];
}
