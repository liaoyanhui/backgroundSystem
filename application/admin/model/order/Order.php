<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-21 10:06:58
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-14 17:28:29
 * @FilePath: /baiying/application/admin/model/order/Order.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\model\order;

use app\admin\Constants;
use think\Console;
use think\Model;
use traits\model\SoftDelete;


class Order extends Model
{



    use SoftDelete;

    // 表名
    protected $name = 'order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = 'deleted_at';

    // 追加属性
    protected $append = [
        'deliver_way_text'
    ];

    public function getDeliverWayTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deliver_way']) ? $data['deliver_way'] : '');

        return isset(Constants::$deliverWay[$value]) ? Constants::$deliverWay[$value] : '';
    }
}
