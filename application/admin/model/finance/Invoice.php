<?php

namespace app\admin\model\finance;

use think\Model;
use traits\model\SoftDelete;

class Invoice extends Model
{
  use SoftDelete;

  // 表名
  protected $name = 'invoice';

  // 自动写入时间戳字段
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  // 定义时间戳字段名
  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';


  // 追加属性
  protected $append = [
    'status_text'
  ];



  public function getStatusList()
  {
    return ['5) unsigne' => __('5) unsigne')];
  }


  public function getStatusTextAttr($value, $data)
  {
    $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
    $list = $this->getStatusList();
    return isset($list[$value]) ? $list[$value] : '';
  }
}
