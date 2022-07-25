<?php

namespace app\admin\controller\finance;

use app\admin\Constants;
use app\admin\model\BySubOrder;
use app\admin\model\finance\SettlementOrder;
use app\admin\model\finance\settlementPayback;
use app\admin\model\finance\settlementPaybackOrder;
use app\admin\model\SettlementRadio;
use app\common\controller\Backend;
use think\Db;

/**
 * 结算单管理
 *
 * @icon fa fa-circle-o
 */
class Settlement extends Backend
{

    /**
     * Settlement模型对象
     * @var \app\admin\model\finance\Settlement
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\Settlement;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     * 
     */
    public function index()
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        // $related_id = $userInfo['related_id'];

        // 是否显示进项
        $Incomehidden = false;
        if ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
            $Incomehidden = true;
        }

        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->view->assign('Incomehidden', $Incomehidden);
            return $this->view->fetch();
        }
    }


    /**
     * 销项 
     *
     */
    public function Output()
    {
        $this->relationSearch = true;
        // 获取当前登录角色
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        // dump($related_id);
        if (!$related_type) {
            $this->error('没有权限!');
        }

        // 根据角色 判断当前结算关系
        $settlement_relationship = null;
        if ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
            $settlement_relationship = Constants::RELATIONSHIP_B_S;
        } elseif ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
            $settlement_relationship = Constants::RELATIONSHIP_C_B;
        } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
            $settlement_relationship = Constants::RELATIONSHIP_P_C;
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        // to_id 销项  from_id 进项 
        $list = $this->model
            ->where('to_id', $related_id)
            ->where('settlement_relationship', $settlement_relationship)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->paginate($limit);

        $total = $this->model->where('to_id', $related_id)->count();

        $result = ['total' => $total, 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 进项 
     *
     */
    public function Income()
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        if (!$related_type) {
            $this->error('没有权限!');
        }

        $settlement_relationship = null;
        if ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
            $settlement_relationship = Constants::RELATIONSHIP_B_S;
        } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
            $settlement_relationship = Constants::RELATIONSHIP_C_B;
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();


        // to_id 销项  from_id 进项
        $list = $this->model
            ->where('from_id', $related_id)
            ->where('settlement_relationship', $settlement_relationship)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->paginate($limit);
        $total = $this->model->where('from_id', $related_id)->count();

        $result = ['total' => $total, 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 销项详情
     *
     */
    public function settlement_detail_out($ids = null)
    {
        $data = $this->model->get($ids);
        $count = SettlementOrder::where('settlement_id', $ids)->count();
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->view->assign('data', $data);
            $this->view->assign('count', $count);
            return $this->view->fetch();
        }
    }

    /**
     * 进项详情
     *
     */
    public function settlement_detail_in($ids = null)
    {
        $data = $this->model->get($ids);
        $count = SettlementOrder::where('settlement_id', $ids)->count();
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->view->assign('data', $data);
            $this->view->assign('count', $count);
            return $this->view->fetch();
        }
    }

    /**
     * 销项子订单
     *
     */
    public function outSubOrder($ids = null)
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        $re = null;

        // 销项 供应商 和 签约单位 没有实缴金额
        if (!$related_type) {
            $this->error('没有权限!');
        } elseif ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
            $re = null;
        } elseif ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
            $re = Constants::RELATIONSHIP_B_S;
        } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
            $re = Constants::RELATIONSHIP_C_B;
        }

        $subOrderIds = SettlementOrder::where('settlement_id', $ids)->column('sub_order_id');

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        if ($re) {
            $subsql = BySubOrder::alias('b')->field('b.id,b.order_id,b.settlement_relationship,b.settlement_ratio')->where('b.settlement_relationship', $re)->group('b.id')->buildSql();
            $list = BySubOrder::alias('bso')
                ->field('bso.*,w.settlement_ratio as settlement_ratio_2')
                ->join([$subsql => 'w'], 'w.order_id = bso.order_id', 'LEFT')
                ->where('bso.id', 'in', $subOrderIds)->order($sort, 'bso.id')->select();
        } else {
            $list = BySubOrder::alias('bso')
                ->where('bso.id', 'in', $subOrderIds)->order($sort, 'bso.id')->select();
        }

        $result = ['rows' => $list];
        return json($result);
    }

    /**
     * 进项子订单
     *
     */
    public function inSubOrder($ids = null)
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        $re = null;

        // 进项 
        if (!$related_type) {
            $this->error('没有权限!');
        } elseif ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
            $re = null;
        } elseif ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
            $re = Constants::RELATIONSHIP_C_B;
        } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
            $re = Constants::RELATIONSHIP_P_C;
        }

        $subOrderIds = SettlementOrder::where('settlement_id', $ids)->column('sub_order_id');

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        if ($re) {
            $subsql = BySubOrder::alias('b')->field('b.id,b.order_id,b.settlement_relationship,b.settlement_ratio')->where('b.settlement_relationship', $re)->group('b.id')->buildSql();
            $list = BySubOrder::alias('bso')
                ->field('bso.*,w.settlement_ratio as settlement_ratio_2')
                ->join([$subsql => 'w'], 'w.order_id = bso.order_id', 'LEFT')
                ->where('bso.id', 'in', $subOrderIds)->order($sort, 'bso.id')->select();
        } else {
            $list = BySubOrder::alias('bso')
                ->where('bso.id', 'in', $subOrderIds)->order($sort, 'bso.id')->select();
        }

        $result = ['rows' => $list];
        return json($result);
    }

    /**
     * 录入回款弹窗
     *
     */
    public function add_settlement_record($settlement_type = null, $ids = null)
    {
        $this->relationSearch = true;
        // 获取当前登录角色
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];


        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {

            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        $params1 = $this->request->post('subOrder/a');

        if (empty($params) || empty($params1)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $currentsettlement = $this->model::get($ids);
        if ($currentsettlement['finished_amount'] + $params['amount'] > $currentsettlement['amount']) {
            $this->error('回款金额不能大于未回款金额');
        }

        // 子订单记录规则
        $orderData = [];
        foreach ($params1 as $k => $v) {
            if ($v['amount']) {
                array_push($orderData, $v);
            }
        }
        foreach ($orderData as $k => $v) {
            if ($v['amount'] > $v['last_amount']) {
                $this->error('订单' . $v['sub_order_no'] . '本次金额超过剩余回款金额!');
            }
        }

        $result = false;
        $result1 = false;
        Db::startTrans();

        try {
            /**
             * @description: 
             * 1、新增结算记录 修改结算单已开总额 同时拿到结算记录id
             * 2、根据结算记录id 新增子订单金额记录 修改子订单累计开票金额 结算比例 结算类型 
             * 3、结算单状态修改 如果是第一次 修改为 2部分开票 如果金额满足 则修改为3已完成
             * 4、结算单完成 对应子订单状态更新为3 待付款
             * @return {*}
             */
            $SettlementPayback = new settlementPayback();
            $result = $SettlementPayback->allowField(true)->save(
                [
                    'settlement_id' => $ids,
                    'pay_bank' => $params['pay_bank'],
                    'pay_info' => $params['pay_info'],
                    'amount' => round($params['amount'], 2),
                    'certificate' => $params['certificate'],
                    'payback_at' => $params['payback_at'],
                    'remark' => $params['remark'],
                ]
            );

            // 修改结算单金额
            // $currentsettlement = $this->model::get($ids);
            if ($currentsettlement['finished_amount'] + $params['amount'] > $currentsettlement['amount']) {
                $this->error('回款金额不能大于未回款金额');
            }
            $currentsettlement->finished_amount = round($currentsettlement['finished_amount'] + $params['amount'], 2);
            $currentsettlement->save();

            // 拿到回款记录id
            $SettlementPaybackId = $SettlementPayback->id;
            $data = [];
            if ($SettlementPaybackId) {
                foreach ($orderData as $k => $v) {
                    array_push($data, [
                        'settlement_payback_id' => $SettlementPaybackId,
                        'sub_order_id' => $v['sub_order_id'],
                        'amount' => round($v['amount'], 2),
                        'remark' => $v['remark']
                    ]);
                }
            }
            $SettlementPaybackOrder = new settlementPaybackOrder();
            $result1 = $SettlementPaybackOrder->allowField(true)->saveAll($data);

            // 更新子订单 数据
            if ($result1) {
                $subOrderData = [];
                foreach ($orderData as $k => $v) {
                    $subOrder = BySubOrder::get($v['sub_order_id']);
                    array_push($subOrderData, [
                        'id' => $v['sub_order_id'],
                        'settlement_finished_amount' => round($subOrder['settlement_finished_amount'] + $v['amount'], 2),
                        // 'settlement_last_amount' => $v['amount'],

                    ]);
                }
                $BySubOrder = new BySubOrder();
                $BySubOrder->allowField(true)->saveAll($subOrderData);
            }

            // 更新结算单状态 每次更新 都改为2部分回款 check金额 如果等于总金额 则改为3已完成
            $currentsettlement2 = $this->model::get($ids);
            if (round($currentsettlement2->amount, 2) < round($currentsettlement2->finished_amount, 2)) {
                $this->error('回款金额不能大于未回款金额');
            } elseif (round($currentsettlement2->amount, 2) == round($currentsettlement2->finished_amount, 2)) {
                $currentsettlement2->status = 3;
                $subOrderIds = SettlementOrder::where('settlement_id', $ids)->column('sub_order_id');
                BySubOrder::where('id', 'in', $subOrderIds)->update(['status' => 5]);
            } else {
                $currentsettlement2->status = 2;
            }
            $currentsettlement2->save();
            // $this->settlement_detail_out($ids);
            // $this->redirect('finance/settlement/settlement_detail_out', ['ids' => $ids]);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();

            $this->error($th->getMessage());
        }
        // if (false === $result || false === $result1) {
        //   $this->error(__('No rows were updated'));
        // }
        // return $this->redirect('finance/settlement/settlement_detail_out', ['ids' => $ids]);
        // return $this->redirect('http://www.baidu.com');
        // return $this->redirect('/finance/settlement/settlement_detail_out/ids/' . $ids);
        return $this->success();
    }

    /**
     * 回款记录
     *
     */
    public function SettlementRecord($settlementId = null)
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        if (!$related_type) {
            $this->error('没有权限!');
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        // $list = settlementPayback::where('settlement_id', $settlementId)->order($sort, $order)->paginate($limit);

        $list = settlementPayback::alias('ir')->field('ir.*,bi.status')->join('by_settlement bi', 'ir.settlement_id = bi.id', 'LEFT')
            ->where('ir.settlement_id', $settlementId)->order($sort, $order)->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 录入回款的子订单
     *
     */
    public function SettlementRecordOrder($ids = null)
    {
        $userInfo = $this->auth->getUserInfo();
        $related_type = $userInfo['related_type'];
        $related_id = $userInfo['related_id'];
        if (!$related_type) {
            $this->error('没有权限!');
        }

        $subOrderIds = SettlementOrder::where('settlement_id', $ids)->column('sub_order_id');

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $subsql = settlementPaybackOrder::alias('iro')->field('iro.id, sum(iro.amount) as amount,iro.sub_order_id')
            ->group('iro.sub_order_id')->buildSql();
        // 剩余开票金额 订单金额 * 结算比例 - (结算记录与子订单表(by_settlement_record_orde) 中 同一个sub_order_id的amount的和)
        $list = BySubOrder::alias('bs')
            ->field('bs.id,bs.sub_order_no, bs.order_amount,bs.settlement_ratio, w.amount')
            ->join([$subsql => 'w'], 'w.sub_order_id = bs.id', 'LEFT')
            ->where('bs.id', 'in', $subOrderIds)->order($sort, $order)->select();

        $result = ['rows' => $list];
        return json($result);
    }

    /**
     * 回款记录查看
     *
     */
    public function settlement_record($ids = null)
    {
        $row = settlementPayback::get($ids);
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->view->assign('row', $row);
            $this->view->assign('cdnurl', $this->view->config['upload']['cdnurl']);
            return $this->view->fetch();
        }
    }

    /**
     * 回款记录删除
     * 
     * @param {*} $ids
     * @return {*}
     */
    public function settlement_record_del($ids = null)
    {

        $SettlementRecord = settlementPayback::get($ids);

        // $count = 0;
        Db::startTrans();
        try {
            // 1 更新子订单 累计开票金额 删除 子订单表的记录
            $settlementPaybackOrder = settlementPaybackOrder::where('settlement_payback_id', $ids)->select();
            foreach ($settlementPaybackOrder as $k => $v) {
                $subOrder = BySubOrder::get($v['sub_order_id']);
                $amount = $subOrder->settlement_finished_amount;
                $subOrder->settlement_finished_amount =  $amount - $v['amount'];
                $subOrder->save();
            }

            foreach ($settlementPaybackOrder as $item) {
                // $count +=
                $item->delete();
            }

            // 2 更新发票单已开总额 删除发票记录 同时判断是否更新发票单状态 
            //(发票单记录为空 则将发票单状态改为1待开票 )
            $settlement = $this->model::get($SettlementRecord['settlement_id']);
            $settlement->finished_amount =  $settlement->finished_amount - $SettlementRecord['amount'];

            settlementPayback::destroy($ids);
            $SettlementRecordIds = settlementPayback::where('settlement_id', $SettlementRecord['settlement_id'])->column('id');
            if (empty($SettlementRecordIds)) {
                $settlement->status = 1;
            }
            $settlement->save();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();

            $this->error($th->getMessage());
        }

        $this->success();
    }

    // 回退sub_order表 删除settlement表 删除settlement_order表
    /**
     * 回款单删除
     * 
     */
    public function output_del($ids = null)
    {
        $settlement = $this->model::get($ids);

        Db::startTrans();
        try {
            // 回退sub_order表
            $subOrderIds = SettlementOrder::where('settlement_id', $settlement['id'])->column('sub_order_id');
            BySubOrder::where('id', 'in', $subOrderIds)->update(['status' => 3]);

            // 删除settlement表
            $settlement->delete();

            // 删除settlement_order表
            $settlementOrderList = SettlementOrder::where('settlement_id', $settlement['id'])->select();
            foreach ($settlementOrderList as $item) {
                $item->delete();
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();

            $this->error($th->getMessage());
        }
        $this->success();
    }
}
