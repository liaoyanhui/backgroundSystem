<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-06 14:45:53
 * @LastEditTime: 2022-06-24 16:17:16
 * @FilePath: /baiying/application/admin/model/system/businessconfig/ByContractingCompany.php
 */

namespace app\admin\model\system\businessconfig;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class ByContractingCompany extends Model
{

  use SoftDelete;

  // 表名
  protected $name = 'contracting_company';

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
