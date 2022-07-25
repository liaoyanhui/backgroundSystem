<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-22 14:56:11
 * @LastEditTime: 2022-06-24 10:57:46
 * @FilePath: /baiying/application/admin/model/system/Category.php
 */

namespace app\admin\model\system;

use think\Model;
use traits\model\SoftDelete;

class Category extends Model
{



  use SoftDelete;
  protected $dateFormat = 'Y-m-d H:i:s';

  // 表名
  protected $name = 'category_new';

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
