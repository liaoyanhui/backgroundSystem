<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-21 16:36:01
 * @LastEditTime: 2022-06-27 11:26:32
 * @FilePath: /baiying/application/admin/model/ByBusinessCompanySalesman.php
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class ByBusinessCompanySalesman extends Model
{
  use SoftDelete;
  protected $name = 'business_company_salesman';
  // protected $table = 'by_business_company_salesman';
  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
  protected $dateFormat = 'Y-m-d H:i:s';
  protected $autoWriteTimestamp = 'datetime';
}
