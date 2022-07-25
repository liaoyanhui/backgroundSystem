<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-23 17:33:12
 * @LastEditTime: 2022-07-14 17:20:08
 * @FilePath: /baiying/application/admin/model/project/Project.php
 */

namespace app\admin\model\project;

use app\admin\Constants;
use think\Model;
use traits\model\SoftDelete;


class Project extends Model
{
  use SoftDelete;
  // 表名
  protected $name = 'project';

  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';

  // 追加属性
  protected $append = [];
}
