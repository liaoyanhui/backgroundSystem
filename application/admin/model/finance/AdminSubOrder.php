<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-30 09:54:57
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-14 17:10:06
 * @FilePath: /baiying/application/admin/model/finance/AdminSubOrder.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\model\finance;

use app\admin\Constants;
use think\Model;


class AdminSubOrder extends Model
{





    // 表名
    protected $name = 'order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');

        return isset(Constants::$orderStatus[$value]) ? Constants::$orderStatus[$value] : '';
    }
}
