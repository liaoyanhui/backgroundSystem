<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-30 09:54:57
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-18 14:55:29
 * @FilePath: /baiying/application/admin/controller/finance/AdminSubOrder.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\finance;

use app\admin\Constants;
use app\common\controller\Backend;

/**
 * 订单
 *
 * @icon fa fa-circle-o
 */
class AdminSubOrder extends Backend
{

    /**
     * AdminSubOrder模型对象
     * @var \app\admin\model\finance\AdminSubOrder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\AdminSubOrder;
        // $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->alias('o')
            ->where('o.status', Constants::ORDER_STATUS_FINISHED)
            ->where($where)
            ->join('sub_order so1', 'o.id = so1.order_id AND so1.settlement_relationship = 1')
            ->join('sub_order so2', 'o.id = so2.order_id AND so2.settlement_relationship = 2')
            ->join('sub_order so3', 'o.id = so3.order_id AND so3.settlement_relationship = 3')
            ->field('o.id,o.order_no,o.status,o.platform_company_name,o.category_id,o.category_name,o.purchaser_id,o.purchaser_name, 
            o.contracting_company_id, o.contracting_company_name, o.business_company_id, o.business_company_name, o.supplier_id, o.supplier_name,
            o.project_id,o.project_name, o.amount, so1.settlement_ratio as settlement_ratio1, so1.settlement_type as settlement_type1, 
            so2.settlement_ratio as settlement_ratio2, so2.settlement_type as settlement_type2, so3.settlement_ratio as settlement_ratio3, so3.settlement_type as settlement_type3, 
            so3.invoice_finished_amount as invoice_finished_amount3, so3.invoice_last_at as invoice_last_at3, so3.invoice_amount as invoice_amount3, 
            so3.settlement_finished_amount as settlement_finished_amount3,  so3.settlement_last_at as settlement_last_at3, so3.invoice_type as invoice_type3,
            so2.invoice_finished_amount as invoice_finished_amount2, so2.invoice_last_at as invoice_last_at2, so2.invoice_amount as invoice_amount2, 
            so2.settlement_finished_amount as settlement_finished_amount2,  so2.settlement_last_at as settlement_last_at2, so2.invoice_type as invoice_type2,
            so1.invoice_finished_amount as invoice_finished_amount1, so1.invoice_last_at as invoice_last_at1, so1.invoice_amount as invoice_amount1, 
            so1.settlement_finished_amount as settlement_finished_amount1,  so1.settlement_last_at as settlement_last_at1, so1.invoice_type as invoice_type1')
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
