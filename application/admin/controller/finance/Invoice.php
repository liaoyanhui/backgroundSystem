<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-29 09:49:57
 * @LastEditTime: 2022-07-20 16:31:32
 * @FilePath: /baiying/application/admin/controller/finance/Invoice.php
 */

namespace app\admin\controller\finance;

use app\admin\Constants;
use app\common\controller\Backend;
use think\Db;

use app\admin\model\finance\InvoiceOrder;
use app\admin\model\finance\InvoiceRecord;
use app\admin\model\finance\InvoiceRecordOrder;
use app\admin\model\BySubOrder;
use app\admin\model\order\SubOrder;
use app\admin\model\SettlementRadio;

/**
 * 发票单管理
 *
 * @icon fa fa-circle-o
 */
class Invoice extends Backend
{

  /**
   * Invoice模型对象
   * @var \app\admin\model\finance\Invoice
   */
  protected $model = null;
  protected $noNeedRight = ['check', 'invoice_type'];

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\finance\Invoice;
    $this->view->assign("statusList", $this->model->getStatusList());
  }



  /**
   * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
   * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
   * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
   */

  /**
   * 查看
   * 
   *  */
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
   *  销项
   * 
   *  */
  public function Output()
  {
    $this->relationSearch = true;
    // 获取当前登录角色
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (!$related_type) {
      $this->error('没有权限!');
    }

    // 根据角色 判断当前结算关系
    $invoice_relationship = null;
    if ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
      $invoice_relationship = Constants::RELATIONSHIP_B_S;
    } elseif ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
      $invoice_relationship = Constants::RELATIONSHIP_C_B;
    } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
      $invoice_relationship = Constants::RELATIONSHIP_P_C;
    }

    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    // to_id 销项  from_id 进项 
    $list = $this->model
      ->where('to_id', $related_id)
      ->where('invoice_relationship', $invoice_relationship)
      ->order($sort, $order)
      ->limit($offset, $limit)
      ->paginate($limit);

    $total = $this->model->where('to_id', $related_id)->count();

    $result = ['total' => $total, 'rows' => $list->items()];
    return json($result);
  }

  /**
   *  进项
   * 
   *  */
  public function Income()
  {
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (!$related_type) {
      $this->error('没有权限!');
    }

    $invoice_relationship = null;
    if ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
      $invoice_relationship = Constants::RELATIONSHIP_B_S;
    } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
      $invoice_relationship = Constants::RELATIONSHIP_C_B;
    }

    [$where, $sort, $order, $offset, $limit] = $this->buildparams();


    // to_id 销项  from_id 进项
    $list = $this->model
      ->where('from_id', $related_id)
      ->where('invoice_relationship', $invoice_relationship)
      ->order($sort, $order)
      ->limit($offset, $limit)
      ->paginate($limit);
    $total = $this->model->where('from_id', $related_id)->count();

    $result = ['total' => $total, 'rows' => $list->items()];
    return json($result);
  }

  /**
   * 销项 详情页
   * 
   */
  public function invoice_detail_out($ids = null)
  {
    $data = $this->model->get($ids);
    $count = InvoiceOrder::where('invoice_id', $ids)->count();
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('data', $data);
      $this->view->assign('count', $count);
      return $this->view->fetch();
    }
  }

  /**
   * 进项 详情页
   * 
   */
  public function invoice_detail_in($ids = null)
  {
    $data = $this->model->get($ids);
    $count = InvoiceOrder::where('invoice_id', $ids)->count();
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('data', $data);
      $this->view->assign('count', $count);
      return $this->view->fetch();
    }
  }

  /**
   * 销项 子订单列表
   * 
   *  */
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
      // $re = null;
    }

    $subOrderIds = InvoiceOrder::where('invoice_id', $ids)->column('sub_order_id');

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
   * 进项 子订单列表
   * 
   *  */
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

    $subOrderIds = InvoiceOrder::where('invoice_id', $ids)->column('sub_order_id');

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
   * 实缴税费
   * 
   */
  public function invoice_amount($ids = null)
  {
    // $data = $this->model->get($ids);
    // $count = InvoiceOrder::where('invoice_id', $ids)->count();
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      // $this->view->assign('data', $data);
      // $this->view->assign('count', $count);
      return $this->view->fetch();
    }
    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $result = null;
    $result = BySubOrder::where('id', $ids)->update(['invoice_amount' => round($params['invoice_amount'], 2)]);
    if (!$result) {
      $this->error('更新失败！');
    }

    $this->success();
  }

  /**
   * 通过查看发票记录表 查看是否已经增加过发票记录
   * 
   * @internal
   * @return {*}
   */
  public function check($invoice_id = null)
  {
    $record = InvoiceRecord::where('invoice_id', $invoice_id)->find();
    // 是否已经存在
    if ($record) {
      $this->success('ok', null, $record['type']);
    } else {
      $this->error('error', null, '');
    }
  }

  /**
   * 选择发票类型弹窗
   * 
   * @remark 选择发票类型 重新读取结算比例表 回填到发票表(amount) 和 子订单表(invoice_type, settlement_ratio)
   */
  public function invoice_type($invoice_id = null)
  {
    $invoiceTypeList = [];
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];

    // 如果是业务公司 那么发票类型有两种 否则 只有一种专票
    if ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
      $invoiceTypeList = [
        ['id' => 3, 'name' => '专票'],
        ['id' => 1, 'name' => '普票'],
      ];
    } else {
      $invoiceTypeList = [
        ['id' => 3, 'name' => '专票']
      ];
    }
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('invoiceTypeList', $invoiceTypeList);
      return $this->view->fetch();
    }

    $params = $this->request->post("row/a");

    if (empty($params) || !$params['invoice_type']) {
      $this->error(__('Parameter %s can not be empty', ''));
    }

    $params = $this->preExcludeFields($params);

    Db::startTrans();
    try {
      $re = null;
      // 进项 
      if (!$related_type) {
        $this->error('没有权限!');
      } elseif ($related_type == Constants::RELATED_TYPE_SUPPLIER) {
        $re = Constants::RELATIONSHIP_B_S;
      } elseif ($related_type == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
        $re = Constants::RELATIONSHIP_C_B;
      } elseif ($related_type == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
        $re = Constants::RELATIONSHIP_P_C;
      }

      // 更新子订单
      $subOrderIds = InvoiceOrder::where('invoice_id', $invoice_id)->column('sub_order_id');
      $subOrderData = [];
      $amount = 0;
      foreach ($subOrderIds as $items) {
        $subOrder = BySubOrder::get($items);
        $query = SettlementRadio::where('platform_id', $subOrder['platform_company_id'])->where('related_id', $subOrder['from_id'])->where('target_id', $related_id)
          ->where('invoice_type', $params['invoice_type'])->where('settlement_relationship', $re);


        // 如果是平台与签约单位 则需要省id  
        if ($subOrder['settlement_relationship'] == Constants::RELATIONSHIP_P_C) {
          $query = $query->where('province_id', $subOrder['project_province_id']);
        } else {
          $query = $query->where('province_id', NULL);
        }

        $newSettlementRatio = $query->column('settlement_ratio');
        // dump($newSettlementRatio);
        //最新结算比 如果没有 则用子订单的
        $lastSettlementRatio = $newSettlementRatio[0] ?? $subOrder['settlement_ratio'];
        $amount += $subOrder['order_amount'] * (100 - $lastSettlementRatio) / 100; // 订单金额 * 结算比例 = 订单应开金额
        array_push($subOrderData, [
          'id' => $items,
          // 'invoice_finished_amount' => $subOrder['invoice_finished_amount'] + $v['amount'],
          // 'invoice_last_amount' => $v['amount'],
          'invoice_type' => $params['invoice_type'],
          'settlement_ratio' => $lastSettlementRatio
        ]);
      }

      $BySubOrder = new BySubOrder();
      $BySubOrder->allowField(true)->saveAll($subOrderData);


      // 更新发票单 $amount
      $invoice = $this->model->get($invoice_id);
      $invoice->amount = round($amount, 2);
      $invoice->save();

      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();

      $this->error($th->getMessage());
    }

    $this->success('ok', null, $params['invoice_type']);
  }

  /**
   * 销项发票记录
   * 
   */
  public function OutInvoiceRecord($invoiceId = null)
  {
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (!$related_type) {
      $this->error('没有权限!');
    }
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    $list = InvoiceRecord::alias('ir')->field('ir.*,bi.status')->join('by_invoice bi', 'ir.invoice_id = bi.id', 'LEFT')
      ->where('ir.invoice_id', $invoiceId)->order($sort, $order)->paginate($limit);

    $result = ['total' => $list->total(), 'rows' => $list->items()];
    return json($result);
  }

  /**
   * 进项发票记录
   * 
   */
  public function InInvoiceRecord($invoiceId = null)
  {
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (!$related_type) {
      $this->error('没有权限!');
    }
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    $list = InvoiceRecord::alias('ir')->field('ir.*,bi.status')->join('by_invoice bi', 'ir.invoice_id = bi.id', 'LEFT')
      ->where('ir.invoice_id', $invoiceId)->order($sort, $order)->paginate($limit);

    $result = ['total' => $list->total(), 'rows' => $list->items()];
    return json($result);
  }

  /**
   * 发票记录与子订单
   * 
   */
  public function InvoiceRecordOrder($ids = null)
  {
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (!$related_type) {
      $this->error('没有权限!');
    }

    $subOrderIds = InvoiceOrder::where('invoice_id', $ids)->column('sub_order_id');

    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    $subsql = InvoiceRecordOrder::alias('iro')->field('iro.id, sum(iro.amount) as amount,iro.sub_order_id')
      ->group('iro.sub_order_id')->buildSql();
    // 剩余开票金额 订单金额 * 结算比例 - (发票记录与子订单表(by_invoice_record_orde) 中 同一个sub_order_id的amount的和)
    $list = BySubOrder::alias('bs')
      ->field('bs.id,bs.sub_order_no, bs.order_amount,bs.settlement_ratio, w.amount')
      ->join([$subsql => 'w'], 'w.sub_order_id = bs.id', 'LEFT')
      ->where('bs.id', 'in', $subOrderIds)->order($sort, $order)->select();

    $result = ['rows' => $list];
    return json($result);
  }

  /**
   * 录入发票记录弹窗
   * 
   */
  public function add_invoice_record($invoice_type = null, $ids = null)
  {
    $this->relationSearch = true;
    // 获取当前登录角色
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];

    $invoiceTypeList = [
      ['id' => '1', 'name' => '普票'],
      ['id' => '3', 'name' => '专票'],
    ];
    // 获取发票类型
    $invoiceTypeName = '';
    foreach ($invoiceTypeList as $k => $v) {
      if ($v['id'] == $invoice_type) {
        $invoiceTypeName = $v['name'];
      }
    }

    // 获取最新发票数据
    $data = $this->model->get($ids);

    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('data', $data);
      $this->view->assign('invoiceTypeName', $invoiceTypeName);
      $this->view->assign('invoiceTypeList', $invoiceTypeList);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    $params1 = $this->request->post('subOrder/a');

    if (empty($params) || empty($params1)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $currentInvoice = $this->model::get($ids);
    if ($currentInvoice['finished_amount'] + $params['amount'] > $currentInvoice['amount']) {
      $this->error('开票金额不能大于未开票金额');
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
        $this->error('订单' . $v['sub_order_no'] . '本次金额超过剩余开票金额!');
      }
    }

    $result = false;
    $result1 = false;
    Db::startTrans();

    try {
      /**
       * @description: 
       * 1、新增发票记录 修改发票单已开总额 同时拿到发票记录id
       * 2、根据发票记录id 新增子订单金额记录 修改子订单累计开票金额 
       * 3、发票单状态修改 如果是第一次 修改为 2部分开票 如果金额满足 则修改为3已完成
       * 4、发票单完成 对应子订单状态更新为3 待付款
       * @return {*}
       */
      $InvoiceRecord = new InvoiceRecord();
      $result = $InvoiceRecord->allowField(true)->save(
        [
          'invoice_id' => $ids,
          'invoice_record_no' => $params['invoice_record_no'],
          'type' => $invoice_type,
          'amount' => $params['amount'],
          'certificate' => $params['certificate'],
          'invoice_at' => $params['invoice_at'],
          'receive_at' => $params['receive_at'],
          'remark' => $params['remark'],
        ]
      );

      // 修改发票单金额
      // $currentInvoice = $this->model::get($ids);
      if ($currentInvoice['finished_amount'] + $params['amount'] > $currentInvoice['amount']) {
        $this->error('开票金额不能大于未开票金额');
      }
      $currentInvoice->finished_amount = round($currentInvoice['finished_amount'] + $params['amount'], 2);
      $currentInvoice->save();

      // 拿到发票记录id
      $invoiceRecordId = $InvoiceRecord->id;
      $data = [];
      if ($invoiceRecordId) {
        foreach ($orderData as $k => $v) {
          array_push($data, [
            'invoice_record_id' => $invoiceRecordId,
            'sub_order_id' => $v['sub_order_id'],
            'type' => $invoice_type,
            'amount' => round($v['amount'], 2),
            'remark' => $v['remark']
          ]);
        }
      }
      $InvoiceRecordOrder = new InvoiceRecordOrder();
      $result1 = $InvoiceRecordOrder->allowField(true)->saveAll($data);

      // 更新子订单 数据
      if ($result1) {
        $subOrderData = [];
        foreach ($orderData as $k => $v) {
          $subOrder = BySubOrder::get($v['sub_order_id']);

          // 获取对应 结算比例表的结算比例 
          // $newSettlementRatio = SettlementRadio::where('platform_id', $subOrder['platform_company_id'])->where('related_id', $subOrder['from_id'])->where('target_id', $related_id)
          //   ->where('invoice_type', $invoice_type)
          //   ->column('settlement_ratio');

          array_push($subOrderData, [
            'id' => $v['sub_order_id'],
            'invoice_finished_amount' => round($subOrder['invoice_finished_amount'] + $v['amount'], 2),
            // 'invoice_last_amount' => $v['amount'],
            // 'invoice_type' => $invoice_type,
            // 'settlement_ratio' => $newSettlementRatio ?? $subOrder['settlement_ratio'] //如果没有 则用子订单的
          ]);
        }
        $BySubOrder = new BySubOrder();
        $BySubOrder->allowField(true)->saveAll($subOrderData);
      }

      // 更新发票单状态 每次更新 都改为2部分开票 check金额 如果等于总金额 则改为3已完成
      $currentInvoice2 = $this->model::get($ids);

      if (round($currentInvoice2->amount, 2) < round($currentInvoice2->finished_amount, 2)) {
        $this->error('发票金额不能大于未开票金额');
      } elseif (round($currentInvoice2->amount, 2) == round($currentInvoice2->finished_amount, 2)) {
        $currentInvoice2->status = 3;
        $subOrderIds = InvoiceOrder::where('invoice_id', $ids)->column('sub_order_id');
        BySubOrder::where('id', 'in', $subOrderIds)->update(['status' => 3]);
      } else {
        $currentInvoice2->status = 2;
      }
      $currentInvoice2->save();

      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();

      $this->error($th->getMessage());
    }

    return $this->success();
  }


  /**
   * 发票记录查看
   * 
   */
  public function invoice_record($ids = null)
  {
    $invoiceTypeList = [
      ['id' => '1', 'name' => '普票'],
      ['id' => '3', 'name' => '专票'],
    ];

    $row = InvoiceRecord::get($ids);

    // 获取发票类型
    $invoiceTypeName = '';
    foreach ($invoiceTypeList as $k => $v) {
      if ($v['id'] == $row['type']) {
        $invoiceTypeName = $v['name'];
      }
    }
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('row', $row);
      $this->view->assign('invoiceTypeName', $invoiceTypeName);
      $this->view->assign('cdnurl', $this->view->config['upload']['cdnurl']);
      return $this->view->fetch();
    }
  }

  //1、更新子订单 累计开票金额 删除 子订单表的记录
  //2、更新发票单已开总额 删除发票记录 同时判断是否更新发票单状态 

  /**
   * 发票记录删除
   * 
   * @param {*} $ids
   * @return {*}
   */
  public function invoice_record_del($ids = null)
  {

    $InvoiceRecord = InvoiceRecord::get($ids);

    // $count = 0;
    Db::startTrans();
    try {
      // 1 更新子订单 累计开票金额 删除 子订单表的记录
      $InvoiceRecordOrder = InvoiceRecordOrder::where('invoice_record_id', $ids)->select();
      foreach ($InvoiceRecordOrder as $k => $v) {
        $subOrder = BySubOrder::get($v['sub_order_id']);
        $amount = $subOrder->invoice_finished_amount;
        $subOrder->invoice_finished_amount =  $amount - $v['amount'];
        $subOrder->save();
      }

      foreach ($InvoiceRecordOrder as $item) {
        // $count +=
        $item->delete();
      }

      // 2 更新发票单已开总额 删除发票记录 同时判断是否更新发票单状态 
      //(发票单记录为空 则将发票单状态改为1待开票 )
      $invoice = $this->model::get($InvoiceRecord['invoice_id']);
      $invoice->finished_amount =  $invoice->finished_amount - $InvoiceRecord['amount'];

      InvoiceRecord::destroy($ids);
      $InvoiceRecordIds = InvoiceRecord::where('invoice_id', $InvoiceRecord['invoice_id'])->column('id');
      if (empty($InvoiceRecordIds)) {
        $invoice->status = 1;
      }
      $invoice->save();
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();

      $this->error($th->getMessage());
    }

    $this->success();
  }

  // 回退sub_order表 删除invoice表 删除invoice_order表
  /**
   * 发票单删除
   * 
   */
  public function output_del($ids = null)
  {
    $invoice = $this->model::get($ids);

    Db::startTrans();
    try {
      // 回退sub_order表
      $subOrderIds = InvoiceOrder::where('invoice_id', $invoice['id'])->column('sub_order_id');
      BySubOrder::where('id', 'in', $subOrderIds)->update(['status' => 1]);

      // 删除invoice表
      $invoice->delete();

      // 删除invoice_order表
      $invoiceOrderList = InvoiceOrder::where('invoice_id', $invoice['id'])->select();
      foreach ($invoiceOrderList as $item) {
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
