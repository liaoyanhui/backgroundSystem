<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-07-01 14:56:50
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-20 16:28:41
 * @FilePath: /baiying/application/admin/controller/finance/SupplierSubOrder.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\finance;

use app\admin\Constants;
use app\common\controller\Backend;

use app\admin\model\finance\Invoice;
use app\admin\model\finance\InvoiceOrder;
use app\admin\model\finance\Settlement;
use app\admin\model\finance\SettlementOrder;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\Cache;


/**
 * 子订单管理
 *
 * @icon fa fa-circle-o
 */
class SupplierSubOrder extends Backend
{

    /**
     * SupplierSubOrder模型对象
     * @var \app\admin\model\finance\SupplierSubOrder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\SupplierSubOrder;
        // $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index($ids = null)
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

        // 过滤哪个公司的订单
        $admin = $this->auth->getUserInfo();
        $query = $this->model
            ->where($where)
            ->where(['status' => $ids, 'settlement_relationship' => Constants::RELATIONSHIP_B_S]);

        $query = $query->where('to_id', $admin['related_id']);

        $list = $query->order($sort, $order)->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function invoice($ids = '')
    {
        $params = [];
        $list = $this->model->all($ids);
        $amount = 0;
        foreach ($list as $item) {
            $amount += ($item['order_amount'] * (100 - $item['settlement_ratio']) / 100);
            $params['from_id'] = $item['from_id'];
            $params['from_name'] = $item['from_name'];
            $params['to_id'] = $item['to_id'];
            $params['to_name'] = $item['to_name'];
        }
        $params['invoice_no'] = 'J0' . (20000000000000 + Cache::inc('invoiceNo'));
        $params['amount'] = round($amount, 2);
        $params['invoice_relationship'] = Constants::RELATIONSHIP_B_S;
        $params['status'] = Constants::INVOICE_STATUS_WAIT_INVOICE;
        $result = false;
        $result1 = false;
        $result2 = false;
        Db::startTrans();
        try {
            $invoice = new Invoice();
            $invoiceOrder = new InvoiceOrder();

            $invoiceOrderList = [];

            $result = $invoice->save($params);
            foreach ($list as $item) {
                array_push($invoiceOrderList, ['invoice_id' => $invoice->id, 'sub_order_id' => $item['id']]);
            };
            $result2 = $invoiceOrder->saveAll($invoiceOrderList);
            $result1 = $this->model->where('id', 'in', $ids)->update(['status' => 2]);
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result && $result1 && $result2) {
            $this->success();
        }
        $this->error(__('No rows were inserted'));
    }

    public function settlement($ids = '')
    {
        $params = [];
        $list = $this->model->all($ids);
        $amount = 0;
        foreach ($list as $item) {
            $amount += ($item['order_amount'] * (100 - $item['settlement_ratio']) / 100);
            $params['from_id'] = $item['from_id'];
            $params['from_name'] = $item['from_name'];
            $params['to_id'] = $item['to_id'];
            $params['to_name'] = $item['to_name'];
        }
        $params['settlement_no'] = 'S0' . (20000000000000 + Cache::inc('settlementNo'));
        $params['amount'] = round($amount, 2);
        $params['settlement_relationship'] = Constants::RELATIONSHIP_B_S;
        $params['status'] = Constants::STEELEMENT_STATUS_WAIT_SETTLEMENT;
        $result = false;
        $result1 = false;
        $result2 = false;
        Db::startTrans();
        try {
            // $list = $this->model->onlyTrashed()->select();
            $settlement = new Settlement();
            $settlementOrder = new SettlementOrder();
            $settlementOrderList = [];

            $result = $settlement->save($params);
            foreach ($list as $item) {
                array_push($settlementOrderList, ['settlement_id' => $settlement->id, 'sub_order_id' => $item['id']]);
            };
            $result2 = $settlementOrder->saveAll($settlementOrderList);
            $result1 = $this->model->where('id', 'in', $ids)->update(['status' => 4]);
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result && $result1 && $result2) {
            $this->success();
        }
        $this->error(__('No rows were inserted'));
    }
}
