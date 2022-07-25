<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-10 16:13:44
 * @LastEditTime: 2022-06-24 15:19:13
 * @FilePath: /baiying/application/admin/model/SettlementRadio.php
 */

namespace app\admin\model;

use app\common\model\MoneyLog;
use app\common\model\ScoreLog;
use think\Model;
use traits\model\SoftDelete;

class SettlementRadio extends Model
{
  // 表名
  protected $name = 'settlement_ratio';

  use SoftDelete;
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
}
