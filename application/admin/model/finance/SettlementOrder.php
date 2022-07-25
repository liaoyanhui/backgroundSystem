<?php

namespace app\admin\model\finance;

use think\Model;
use traits\model\SoftDelete;

class SettlementOrder extends Model
{
    use SoftDelete;

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = 'deleted_at';
}
