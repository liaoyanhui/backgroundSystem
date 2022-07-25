<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-07-13 10:02:58
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-13 17:08:45
 * @FilePath: /baiying/application/admin/model/finance/settlementPayback.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\model\finance;

use think\Model;
use traits\model\SoftDelete;

class settlementPayback extends Model
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
