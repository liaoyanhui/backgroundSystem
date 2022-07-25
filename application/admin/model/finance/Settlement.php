<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-07-05 15:05:02
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-13 17:07:14
 * @FilePath: /baiying/application/admin/model/finance/Settlement.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\model\finance;

use think\Model;
use traits\model\SoftDelete;

class Settlement extends Model
{


    use SoftDelete;


    // 表名
    protected $name = 'settlement';

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
