<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-07-05 10:33:15
 * @LastEditTime: 2022-07-05 10:46:57
 * @FilePath: /baiying/application/admin/model/finance/InvoiceOrder.php
 */

namespace app\admin\model\finance;

use think\Model;
use traits\model\SoftDelete;

class InvoiceOrder extends Model
{
  use SoftDelete;
  // 表名
  // protected $name = 'invoice_order';

  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
}
