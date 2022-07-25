<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-08 16:58:46
 * @LastEditTime: 2022-06-27 09:56:30
 * @FilePath: /baiying/application/common/model/Contact.php
 */

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Contact extends Model
{
  use SoftDelete;
  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
  protected $dateFormat = 'Y-m-d H:i:s';
  protected $autoWriteTimestamp = 'datetime';
}
