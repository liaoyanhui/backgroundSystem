<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-20 11:19:45
 * @LastEditTime: 2022-07-06 16:53:05
 * @FilePath: /baiying/application/admin/model/system/businessconfig/BySupplier.php
 */

namespace app\admin\model\system\businessconfig;

use think\Model;
use traits\model\SoftDelete;

class BySupplier extends Model
{
  use SoftDelete;
  protected $dateFormat = 'Y-m-d H:i:s';
  // 表名
  protected $name = 'supplier';

  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';

  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  // protected $deleteTime = false;
  protected $deleteTime = 'deleted_at';

  // 追加属性
  protected $append = [];
}
