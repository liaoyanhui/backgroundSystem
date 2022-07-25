<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-21 14:04:47
 * @LastEditTime: 2022-06-27 09:56:04
 * @FilePath: /baiying/application/admin/model/BusinessCompanyDepartment.php
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class BusinessCompanyDepartment extends Model
{
  use SoftDelete;
  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
  protected $dateFormat = 'Y-m-d H:i:s';
  protected $autoWriteTimestamp = 'datetime';
}
