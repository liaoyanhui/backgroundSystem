<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class SupplierCompany extends Model
{
  protected $name = 'supplier_company';

  use SoftDelete;
  protected $autoWriteTimestamp = 'datetime';
  protected $dateFormat = 'Y-m-d H:i:s';

  protected $createTime = false;
  protected $updateTime = false;
  protected $deleteTime = 'deleted_at';
}
