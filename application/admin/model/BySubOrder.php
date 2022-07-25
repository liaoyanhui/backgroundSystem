<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-30 10:40:06
 * @LastEditTime: 2022-07-05 10:44:07
 * @FilePath: /baiying/application/admin/model/BySubOrder.php
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class BySubOrder extends Model
{
  use SoftDelete;
  protected $name = 'sub_order';
  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
}
