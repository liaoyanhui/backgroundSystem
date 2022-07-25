<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-17 09:38:21
 * @LastEditTime: 2022-06-30 10:38:43
 * @FilePath: /baiying/application/admin/model/system/businessconfig/ByBusinessCompany.php
 */

namespace app\admin\model\system\businessconfig;

use think\Model;
use traits\model\SoftDelete;

class ByBusinessCompany extends Model
{
  use SoftDelete;

  // 表名
  protected $name = 'business_company';

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
