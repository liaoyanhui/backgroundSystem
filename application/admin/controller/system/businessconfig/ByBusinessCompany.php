<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-17 09:38:21
 * @LastEditTime: 2022-07-15 10:16:25
 * @FilePath: /baiying/application/admin/controller/system/businessconfig/ByBusinessCompany.php
 */

namespace app\admin\controller\system\businessconfig;

use app\common\controller\Backend;
use think\Db;
use think\Validate;

use app\common\model\Contact;
use app\admin\Constants;

use app\admin\model\SettlementRadio;
use Exception;
use think\exception\PDOException;
use app\admin\model\BusinessCompanyDepartment;
use app\admin\model\ByBusinessCompanySalesman;
use app\admin\model\system\businessconfig\BySupplier;
use app\admin\model\SupplierCompany;
use think\Model;
use fast\Tree;

/**
 * 业务公司管理
 *
 * @icon fa fa-circle-o
 */
class ByBusinessCompany extends Backend
{

  /**
   * ByBusinessCompany模型对象
   * @var \app\admin\model\system\businessconfig\ByBusinessCompany
   */
  protected $model = null;
  // protected $relationSearch = true;
  protected $noNeedRight = ['platformBySupplier'];

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\system\businessconfig\ByBusinessCompany;
  }



  /**
   * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
   * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
   * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
   */

  /**
   * 查看
   *
   * @return \think\response\Json
   * @throws \think\Exception
   * @throws DbException
   */
  public function index()
  {
    $this->relationSearch = true;

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      return $this->view->fetch();
    }
    //如果发送的来源是 Selectpage，则转发到 Selectpage
    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();

    $admin = $this->auth->getUserInfo();
    $query = Db::name('business_company')
      ->alias('bc')
      ->field('bc.id,bc.name,bc.addr,bc.province_id,bc.province_cname,bc.intro,bc.remark,c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way')
      ->join('contact c', 'c.related_id = bc.id and  c.related_type = ' . Constants::RELATED_TYPE_BUSINESS_COMPANY . ' and c.is_default = 2', 'LEFT')
      ->where($where)
      ->where('bc.deleted_at', 'null');
    if ($admin['related_type'] == Constants::RELATED_TYPE_BUSINESS_COMPANY) {
      $query = $query->where('bc.id', $admin['related_id']);
    }

    $list = $query->order($sort, $order)->paginate($limit);
    $result = ['total' => $list->total(), 'rows' => $list->items()];
    return json($result);
  }


  /**
   * 添加
   */
  public function add()
  {
    if (false === $this->request->isPost()) {
      return $this->view->fetch();
    }
    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
      $params[$this->dataLimitField] = $this->auth->id;
    }

    $businessResult = false;
    $contactResult = false;

    Db::startTrans();
    try {
      $businessResult = $this->model->allowField(true)->save([
        'name' => $params['name'],
        'addr' => $params['addr'],
        'intro' => $params['intro'],
        'remark' => $params['remark'],
        'province_id' => $params['province_id'],
        'province_cname' => $params['province_cname']
      ]);
      $contact = new Contact();
      $contactResult = $contact->allowField(true)->save([
        'related_id' => $this->model->id,
        'contact_name' => $params['contact_name'],
        'contact_duty' => $params['contact_duty'],
        'contact_way' => $params['contact_way'],
        'related_type' => Constants::RELATED_TYPE_BUSINESS_COMPANY
      ]);
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }
    if ($contactResult === false || $businessResult === false) {
      $this->error(__('No rows were inserted'));
    }

    $this->success();
  }

  /**
   * 编辑
   */
  public function edit($ids = null)
  {
    $list = Db::name('business_company')
      ->alias('bc')
      ->field('bc.id,bc.name,bc.addr,bc.province_id,bc.province_cname,bc.intro,bc.remark,c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way')
      ->join('contact c', 'c.related_id = bc.id and c.related_type = ' . Constants::RELATED_TYPE_BUSINESS_COMPANY . ' and c.is_default = 2', 'LEFT')
      ->where('bc.id', '=', $ids)
      ->find();
    if (!$list) {
      $this->error(__('No Results were found'));
    }
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds) && !in_array($list[$this->dataLimitField], $adminIds)) {
      $this->error(__('You have no permission'));
    }
    if (false === $this->request->isPost()) {
      $this->view->assign('row', $list);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);
    $result = false;
    $result1 = false;

    Db::startTrans();
    try {
      $contact = new Contact();
      $result = $contact->allowField(true)->save(
        [
          'contact_name' => $params['contact_name'],
          'contact_duty' => $params['contact_duty'],
          'contact_way' => $params['contact_way'],
          'related_id' => $ids,
          'related_type' => Constants::RELATED_TYPE_BUSINESS_COMPANY
        ],
        $params['contact_id'] ? ['id' =>  $params['contact_id']] : []
      );
      $result1 = $this->model->allowField(true)->save(
        [
          'name' => $params['name'],
          'addr' => $params['addr'],
          'intro' => $params['intro'],
          'remark' => $params['remark'],
          'province_id' => $params['province_id'],
          'province_cname' => $params['province_cname']
        ],
        ['id' => $ids]
      );
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }

    if (false === $result || false === $result1) {
      $this->error(__('No rows were updated'));
    }
    $this->success();
  }

  /**
   * 删除
   */
  public function del($ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    if (empty($ids)) {
      $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    // $pk = $this->model->getPk();
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      $this->model->where($this->dataLimitField, 'in', $adminIds);
    }

    $bbcIds = $this->model->where('id', $ids)->column('id');
    $srIds1 = Db::name('settlement_ratio')->where('related_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_B_S)->column('id');
    $srIds2 = Db::name('settlement_ratio')->where('target_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_C_B)->column('id');

    Db::startTrans();
    try {

      !empty($bbcIds) && $this->model::destroy($bbcIds);
      !empty($srIds1) && SettlementRadio::destroy($srIds1);
      !empty($srIds2) && SettlementRadio::destroy($srIds2);

      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }
    $this->success();
  }

  /**
   * 详情
   */
  public function business_tab($ids)
  {
    $row = $this->model->get(['id' => $ids]);
    if (!$row) {
      $this->error(__('No Results were found'));
    }
    if ($this->request->isAjax()) {
      $this->success("Ajax请求成功", null, ['id' => $ids]);
    }
    $detailRow = $row;
    unset($detailRow['created_at']);
    unset($detailRow['deleted_at']);
    unset($detailRow['updated_at']);
    unset($detailRow['id']);
    unset($detailRow['province_id']);
    $this->view->assign("row", $row->toArray());
    return $this->view->fetch();
  }

  /**
   * 供应商结算比
   */
  public function bsSettlementRatio($ids = null)
  {
    $this->model = model('SettlementRadio');
    //设置过滤方法
    $this->request->filter(['strip_tags']);
    if ($this->request->isAjax()) {
      //如果发送的来源是Selectpage，则转发到Selectpage
      if ($this->request->request('keyField')) {
        return $this->selectpage();
      }
      list($where, $sort, $order, $offset, $limit) = $this->buildparams();
      $list = Db::name('settlement_ratio')
        ->alias('sr')
        ->field("sr.id,sr.target_id,s.name as target_name,bc.name as business_name, sr.invoice_type,
        group_concat(sr.province_name SEPARATOR ' ; ') as provinces, 
        sr.updated_at,sr.settlement_type,sr.settlement_ratio,sr.settlement_date,
        sr.platform_id, pc.name as platform_name, c.contact_way, c.contact_name")
        ->join('business_company bc', 'bc.id = sr.related_id', 'LEFT')
        ->join('supplier s', 's.id = sr.target_id', 'LEFT')
        ->join('platform_company pc', 'pc.id = sr.platform_id')
        ->join('contact c', 'c.related_id = sr.target_id and c.related_type = ' . Constants::RELATED_TYPE_SUPPLIER . ' and c.is_default = 2', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_B_S)
        ->where('sr.related_id', $ids)
        ->where('sr.deleted_at', 'null')
        ->group('sr.target_id, sr.platform_id, sr.related_id,sr.invoice_type')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 选中的供应商中 未被选中的平台
   * @internal
   */
  public function platformBySupplier($business_id = null, $platform_id = null)
  {
    //自定义搜索条件
    $custom = (array)$this->request->request("custom/a");
    $supplier_id = $custom['supplier_id'];

    // 如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
    $primarykey = $this->request->request("keyField");
    $primaryvalue = $this->request->request("keyValue");

    $where = [$primarykey => ['in', $primaryvalue]];

    // 去结算比例表获取当前业务公司下的所有平台  
    $sql = Db::name('settlement_ratio')
      ->alias('sr')
      ->join('platform_company pc', 'pc.id = sr.platform_id', 'LEFT')
      ->where('settlement_relationship', Constants::RELATIONSHIP_B_S)
      ->where('sr.target_id', $supplier_id)
      ->where('sr.related_id', $business_id)
      ->where('sr.deleted_at', 'null');

    // 如果有平台id 说明是编辑状态
    if ($platform_id) {
      $sql = $sql->where('sr.platform_id', 'neq', $platform_id)
        ->group('sr.platform_id')
        ->column('sr.platform_id');
    } else {
      $sql = $sql->group('sr.platform_id')
        ->column('sr.platform_id');
    }

    // 如果有默认值 说明是编辑状态
    if ($primaryvalue) {
      $list = Db::name('platform_company')->where($where)->where('id', 'not in', $sql)->where('deleted_at', 'null')->select();
    } else {
      $list = Db::name('platform_company')->where('id', 'not in', $sql)->where('deleted_at', 'null')->select();
    }

    // 剔除已有的业务公司 获得还能够被选的平台
    // $list = Db::name('business_company')->where('id', 'not in', $srIds)->where('deleted_at', 'null')->select();
    // $total = Db::name('business_company')->where('id', 'not in', $srIds)->where('deleted_at', 'null')->count();
    return json(['list' => $list]);
  }

  /**
   * 添加供应商结算比
   */
  public function b_s_settlement_ratio_add($business_id = null)
  {
    $this->relationSearch = true;
    $supplierIds = SupplierCompany::where('business_company_id', $business_id)->column('supplier_id');
    $supplierList = empty($supplierIds) ? [] : BySupplier::all($supplierIds);
    // $supplierList = BySupplier::where()->select();

    // 1订单结算 2月结算 3周结算 4背靠背结算
    $settlementType = [
      ['id' => Constants::SETTLEMENT_TYPE_MONTH, 'name' => '月结'],
      ['id' => Constants::SETTLEMENT_TYPE_ORDER, 'name' => '订单融'],
      ['id' => Constants::SETTLEMENT_TYPE_WEEK, 'name' => '周结'],
      ['id' => Constants::SETTLEMENT_TYPE_BACK, 'name' => '背靠背']
    ];


    if (false === $this->request->isAjax()) {
      $this->view->assign('supplierList', $supplierList);
      $this->view->assign('business_id', $business_id);
      $this->view->assign('settlementType', $settlementType);
      return $this->view->fetch();
    }

    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    $params1 = $this->request->post('row/a');


    $validate = new Validate([
      'settlement_ratio_13|结算比例' => 'require|between:0,100',
      'settlement_ratio_plain|结算比例' => 'require|between:0,100',
    ]);

    if (!$validate->check($params1)) {
      $this->error($validate->getError());
    }

    // // 根据发票类型 生成两条数据
    // if (!$params1['settlement_ratio_13'] && !$params1['settlement_ratio_plain']) {
    //   $this->error('结算比例必须填一个');
    // }
    // $data = [];
    $data1 = [
      'platform_id' => $params1['platform_id'],
      'related_id' => $business_id, 'target_id' => $params1['supplier_id'],
      'settlement_type' => $params1['settlement_type'],
      'settlement_relationship' => Constants::RELATIONSHIP_B_S,
      'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13, 'settlement_ratio' => $params1['settlement_ratio_13']
    ];
    $data2 = [
      'platform_id' => $params1['platform_id'],
      'related_id' => $business_id, 'target_id' => $params1['supplier_id'],
      'settlement_type' => $params1['settlement_type'],
      'settlement_relationship' => Constants::RELATIONSHIP_B_S,
      'invoice_type' => Constants::INVOICE_TYPE_PLAIN, 'settlement_ratio' => $params1['settlement_ratio_plain']
    ];
    // if ($params1['settlement_ratio_13']) {
    //   $data1 = [
    //     'platform_id' => $params1['platform_id'],
    //     'related_id' => $business_id, 'target_id' => $params1['supplier_id'],
    //     'settlement_type' => $params1['settlement_type'],
    //     'settlement_relationship' => Constants::RELATIONSHIP_B_S,
    //     'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13, 'settlement_ratio' => $params1['settlement_ratio_13']
    //   ];
    //   array_push($data, $data1);
    // }

    // if ($params1['settlement_ratio_plain']) {
    //   $data2 = [
    //     'platform_id' => $params1['platform_id'],
    //     'related_id' => $business_id, 'target_id' => $params1['supplier_id'],
    //     'settlement_type' => $params1['settlement_type'],
    //     'settlement_relationship' => Constants::RELATIONSHIP_B_S,
    //     'invoice_type' => Constants::INVOICE_TYPE_PLAIN, 'settlement_ratio' => $params1['settlement_ratio_plain']
    //   ];
    //   array_push($data, $data2);
    // }


    Db::name('settlement_ratio')->insertAll([$data1, $data2]);
    return $this->success();
  }

  /**
   * 删除供应商结算比
   */
  public function b_s_settlement_ratio_del($ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    $ids = $ids ?: $this->request->post("ids");
    if (empty($ids)) {
      $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    $list = SettlementRadio::where('id', $ids)->select();
    $count = 0;
    Db::startTrans();
    try {
      foreach ($list as $item) {
        $count += $item->delete();
      }
      Db::commit();
    } catch (PDOException | Exception $e) {
      Db::rollback();
      $this->error($e->getMessage());
    }
    if ($count) {
      $this->success();
    }
    $this->error(__('No rows were deleted'));
  }

  /**
   * 编辑供应商结算比
   */
  public function b_s_settlement_ratio_edit($business_id = null, $ids = null)
  {
    $this->relationSearch = true;
    // 1订单结算 2月结算 3周结算 4背靠背结算'
    $settlementType = [
      ['id' => 1, 'name' => '订单融'],
      ['id' => 2, 'name' => '月结'],
      ['id' => 3, 'name' => '周结'],
      ['id' => 4, 'name' => '背靠背']
    ];

    $supplierList = BySupplier::all();
    $data = SettlementRadio::get($ids);

    // 判断发票类型
    if ($data['invoice_type'] == Constants::INVOICE_TYPE_SPECIAL_13) {
      // $data['settlement_ratio_13'] = $data['settlement_ratio'];
      // $data['settlement_ratio_plain'] = null;
      $data['invoice_type_value'] = '专票';
    } elseif ($data['invoice_type'] == Constants::INVOICE_TYPE_PLAIN) {
      // $data['settlement_ratio_plain'] = $data['settlement_ratio'];
      // $data['settlement_ratio_13'] = null;
      $data['invoice_type_value'] = '普票';
    }

    // 设置已经选中的平台
    foreach ($supplierList as $k => $v) {
      if ($v['id'] == $data['target_id']) {
        $supplierList[$k]['selected'] = true;
      } else {
        $supplierList[$k]['selected'] = false;
      }
    }

    // 对应的结算方式 设为checked
    foreach ($settlementType as $k => $v) {
      if ($v['id'] == $data['settlement_type']) {
        $settlementType[$k]['selected'] = true;
      } else {
        $settlementType[$k]['selected'] = false;
      }
    }
    // dump($settlementType);
    // dump($supplierProvinceOne);

    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('data', $data);
      $this->view->assign('supplierList', $supplierList);
      $this->view->assign('business_id', $business_id);
      $this->view->assign('settlementType', $settlementType);
      return $this->view->fetch();
    }

    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    // $params = $this->request->post('id/a');
    $params1 = $this->request->post('row/a');

    $params1 = $this->preExcludeFields($params1);

    // $listIds = [];
    // foreach ($params as $k => $v) {
    //   array_push($listIds, $k);
    // }
    // 开启事务 先删除该业务公司下签约的所有城市 然后根据选中的重新设置
    $result = false;
    $result1 = false;
    Db::startTrans();

    try {
      $result = Db::name('settlement_ratio')->where('id', $ids)->delete();
      $data = [
        'platform_id' => $data['platform_id'],
        'related_id' => $business_id, 'target_id' => $data['target_id'],
        'settlement_type' => $params1['settlement_type'],
        'settlement_relationship' => Constants::RELATIONSHIP_B_S,
        'invoice_type' => $data['invoice_type'], 'settlement_ratio' => $params1['settlement_ratio']
      ];
      // foreach ($listIds as $k => $v) {
      //   $sql = [];
      //   $sql += [
      //     'related_id' => $business_id, 'target_id' => $ids,
      //     'province_id' => $v,  'province_name' => $params[$v],
      //     'settlement_type' => $params1['settlement_type'],
      //     'settlement_relationship' => Constants::RELATIONSHIP_B_S
      //   ];
      //   if ($params1['settlement_type'] == '2') {
      //     $sql += ['settlement_date' => $params1['settlement_date']];
      //   } else {
      //     $sql += ['settlement_ratio' => $params1['settlement_ratio']];
      //   }
      //   array_push($data, $sql);
      // }
      $validate = new Validate([
        'settlement_ratio|结算比例' => 'require|between:0,100',
      ]);
      if (!$validate->check($data)) {
        $this->error($validate->getError());
      }
      $result1 = Db::name('settlement_ratio')->insert($data);
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      if (!$validate->check($data)) {
        $this->error($validate->getError());
      }
      $this->error($th->getMessage());
    }
    if (false === $result || false === $result1) {
      $this->error(__('No rows were updated'));
    }
    return $this->success();
  }


  /**
   * 签约单位结算比
   */
  public function bcSettlementRatio($ids = null)
  {
    $this->model = model('SettlementRadio');
    //设置过滤方法
    $this->request->filter(['strip_tags']);
    if ($this->request->isAjax()) {
      //如果发送的来源是Selectpage，则转发到Selectpage
      if ($this->request->request('keyField')) {
        return $this->selectpage();
      }
      list($where, $sort, $order, $offset, $limit) = $this->buildparams();
      // group_concat(sr.province_name,sr.settlement_ratio SEPARATOR ' ; ') as provinces, 
      $list = Db::name('settlement_ratio')
        ->alias('sr')
        ->field("sr.id, sr.related_id,bc.name as target_name,cc.name as related_name,sr.invoice_type,
        sr.updated_at,sr.settlement_type,sr.settlement_ratio,sr.settlement_date,pc.name as platform_name,c.contact_name,c.contact_way")
        ->join('business_company bc', 'bc.id = sr.target_id', 'LEFT')
        ->join('contracting_company cc', 'cc.id = sr.related_id', 'LEFT')
        ->join('platform_company pc', 'pc.id = sr.platform_id', 'LEFT')
        ->join('contact c', 'c.related_id = sr.related_id and c.related_type = ' . Constants::RELATED_TYPE_CONTRACTING_COMPANY . ' and c.is_default = 2', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_C_B)
        ->where('sr.target_id', $ids)
        ->where('sr.deleted_at', 'null')
        ->group('sr.target_id, sr.platform_id, sr.related_id,sr.invoice_type')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }



  /****************** 部门 人员管理 ******************/

  /**
   * 部门
   */
  public function department($ids)
  {
    //设置过滤方法
    $this->request->filter(['strip_tags']);
    if ($this->request->isAjax()) {
      //如果发送的来源是Selectpage，则转发到Selectpage
      if ($this->request->request('keyField')) {
        return $this->selectpage();
      }
      list($where, $sort, $order, $offset, $limit) = $this->buildparams();

      $list = Db::name('business_company_department')
        ->where($where)
        ->where('business_company_id', $ids)
        ->where('deleted_at', 'null')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 添加部门
   */
  public function department_add($business_id = null)
  {
    if (false === $this->request->isPost()) {
      return $this->view->fetch();
    }
    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $business = $this->model->get(['id' => $business_id]);

    $result = false;
    $department = new BusinessCompanyDepartment();
    $result = $department->allowField(true)->save([
      'name' => $params['name'],
      'manager' => $params['manager'],
      'contact_info' => $params['contact_info'],
      'remark' => $params['remark'],
      'business_company_id' => $business_id,
      'business_company_name' => $business['name'],
    ]);

    if ($result == false) {
      $this->error(__('No rows were inserted'));
    }
    $this->success();
  }

  /**
   * 删除部门
   */
  public function department_del($business_id = null, $ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    if (empty($ids)) {
      $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    $department = new BusinessCompanyDepartment();
    $salesman = new ByBusinessCompanySalesman();

    $departmentOne = $department::get($ids);
    // $salesmanList = $salesman::all(['department_id' => $ids]);

    // 开启事务 先删除该业务公司下签约的所有城市 然后根据选中的重新设置
    $result = false;
    $result1 = false;
    Db::startTrans();
    try {
      $departmentOne->delete();
      $dIds = $salesman::where('department_id', $ids)->where('company_id', $business_id)->column('id');
      !empty($dIds) && ByBusinessCompanySalesman::destroy($dIds);

      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }
    // if (false === $result || false === $result1) {
    //   $this->error(__('No rows were updated'));
    // }
    return $this->success();
  }

  /**
   * 编辑部门
   */
  public function department_edit($business_id = null, $ids = null)
  {
    $department = new BusinessCompanyDepartment();
    $departmentOne = $department::get($ids);

    if (!$departmentOne) {
      $this->error(__('No Results were found'));
    }

    if (false === $this->request->isPost()) {
      $this->view->assign('row', $departmentOne);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $business = $this->model->get(['id' => $business_id]);

    $result = false;
    $department = new BusinessCompanyDepartment();
    $result = $department->allowField(true)->save([
      'name' => $params['name'],
      'manager' => $params['manager'],
      'contact_info' => $params['contact_info'],
      'remark' => $params['remark'],
      'business_company_id' => $business_id,
      'business_company_name' => $business['name'],
    ], ['id' => $ids]);

    if ($result == false) {
      $this->error(__('No rows were inserted'));
    }
    $this->success();
  }

  /**
   * 业务员
   */
  public function salesman($ids)
  {
    $this->model = model('ByBusinessCompanySalesman');
    $this->relationSearch = true;

    //设置过滤方法
    $this->request->filter(['strip_tags']);
    if ($this->request->isAjax()) {
      //如果发送的来源是Selectpage，则转发到Selectpage
      if ($this->request->request('keyField')) {
        return $this->selectpage();
      }
      list($where, $sort, $order, $offset, $limit) = $this->buildparams();

      // dump($where);
      $list = Db::name('business_company_salesman')
        ->alias('bcs')
        ->field('bcs.id,bcs.name,bcd.name as department_name,bcs.contact_way,bcs.remark')
        ->join('business_company_department bcd', 'bcs.department_id = bcd.id', 'LEFT')
        ->where($where)
        ->where('bcs.company_id', $ids)
        ->where('bcs.deleted_at', 'null')
        // ->order($sort, $order)
        ->order('bcs.id', $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 添加业务员
   */
  public function salesman_add($business_id)
  {
    $departmentList = Db::name('business_company_department')
      ->alias('d')
      ->field('d.id,d.name')
      ->where('d.business_company_id', $business_id)
      ->where('d.deleted_at', 'null')
      ->select();
    if (false === $this->request->isPost()) {
      $this->view->assign('departmentList', $departmentList);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $business = $this->model->get(['id' => $business_id]);

    $result = false;
    $salesman = new ByBusinessCompanySalesman();
    $result = $salesman->allowField(true)->save([
      'name' => $params['name'],
      'department_id' => $params['department_id'],
      'contact_way' => $params['contact_way'],
      'remark' => $params['remark'],
      'company_id' => $business_id,
      'company_name' => $business['name'],
    ]);

    if ($result == false) {
      $this->error(__('No rows were inserted'));
    }
    $this->success();
  }

  /**
   * 删除业务员
   */
  public function salesman_del($business_id = null, $ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    if (empty($ids)) {
      $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    $salesman = new ByBusinessCompanySalesman();
    $salesmanOne = $salesman::get($ids);
    $result = $salesmanOne->delete();

    if ($result) {
      return $this->success();
    }
    $this->error(__('No rows were deleted'));
  }

  /**
   * 编辑业务员
   */
  public function salesman_edit($business_id = null, $ids = null)
  {
    $salesman = new ByBusinessCompanySalesman();
    $salesmanOne = $salesman::get($ids);

    if (!$salesmanOne) {
      $this->error(__('No Results were found'));
    }

    $departmentList = Db::name('business_company_department')
      ->alias('d')
      ->field('d.id,d.name')
      ->where('d.business_company_id', $business_id)
      ->where('d.deleted_at', 'null')
      ->select();

    if (false === $this->request->isPost()) {
      $this->view->assign('row', $salesmanOne);
      $this->view->assign('departmentList', $departmentList);
      return $this->view->fetch();
    }


    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    $business = $this->model->get(['id' => $business_id]);

    $result = false;
    $salesman = new ByBusinessCompanySalesman();
    $result = $salesman->allowField(true)->save([
      'name' => $params['name'],
      'department_id' => $params['department_id'],
      'contact_way' => $params['contact_way'],
      'remark' => $params['remark'],
      'company_id' => $business_id,
      'company_name' => $business['name'],
    ], ['id' => $ids]);

    // if ($result == false) {
    //   $this->error(__('No rows were inserted'));
    // }
    $this->success();
  }

  /**
   * 下拉
   *
   * @return string|\think\response\Json|void
   * @throws \think\Exception
   */
  public function order()
  {
    $this->relationSearch = true;

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      return $this->view->fetch();
    }
    //如果发送的来源是 Selectpage，则转发到 Selectpage
    if ($this->request->request('keyField')) {
      return $this->orderSelectPage();
    }
  }

  /**
   * Selectpage的实现方法
   *
   * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
   * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
   *
   */
  protected function orderSelectPage()
  {
    //设置过滤方法
    $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

    //搜索关键词,客户端输入以空格分开,这里接收为数组
    $word = (array)$this->request->request("q_word/a");
    //当前页
    $page = $this->request->request("pageNumber");
    //分页大小
    $pagesize = $this->request->request("pageSize");
    //搜索条件
    $andor = $this->request->request("andOr", "and", "strtoupper");
    //排序方式
    $orderby = (array)$this->request->request("orderBy/a");
    //显示的字段
    $field = $this->request->request("showField");
    //主键
    $primarykey = $this->request->request("keyField");
    //主键值
    $primaryvalue = $this->request->request("keyValue");
    //搜索字段
    $searchfield = (array)$this->request->request("searchField/a");
    //自定义搜索条件
    $custom = (array)$this->request->request("custom/a");
    //是否返回树形结构
    $istree = $this->request->request("isTree", 0);
    $ishtml = $this->request->request("isHtml", 0);
    if ($istree) {
      $word = [];
      $pagesize = 999999;
    }
    $order = [];
    foreach ($orderby as $k => $v) {
      $order[$v[0]] = $v[1];
    }
    $field = $field ? $field : 'name';

    //如果有primaryvalue,说明当前是初始化传值
    if ($primaryvalue !== null) {
      $where = ['a.' . $primarykey => ['in', $primaryvalue]];
      $pagesize = 999999;
    } else {
      $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
        $logic = $andor == 'AND' ? '&' : '|';
        $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
        $searchfield = str_replace(',', $logic, $searchfield);
        $word = array_filter(array_unique($word));
        if (count($word) == 1) {
          $query->where($searchfield, "like", "%" . reset($word) . "%");
        } else {
          $query->where(function ($query) use ($word, $searchfield) {
            foreach ($word as $index => $item) {
              $query->whereOr(function ($query) use ($item, $searchfield) {
                $query->where($searchfield, "like", "%{$item}%");
              });
            }
          });
        }

        // if ($custom && is_array($custom)) {
        //   foreach ($custom as $k => $v) {
        //     if (is_array($v) && 2 == count($v)) {
        //       $query->where($k, trim($v[0]), $v[1]);
        //     } else {
        //       $query->where($k, '=', $v);
        //     }
        //   }
        // }

      };
    }
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      $this->model->where($this->dataLimitField, 'in', $adminIds);
    }
    $list = [];
    $total = $this->model->where($where)->alias('a')->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.platform_id = ' . $custom['platform_id'] . ' And b.settlement_relationship = 2 And b.invoice_type = 3 And a.id = b.target_id')->count();
    if ($total > 0) {
      if (is_array($adminIds)) {
        $this->model->where($this->dataLimitField, 'in', $adminIds);
      }

      $fields = is_array($this->selectpageFields) ? $this->selectpageFields : ($this->selectpageFields && $this->selectpageFields != '*' ? explode(',', $this->selectpageFields) : []);

      //如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
      if ($primaryvalue !== null && preg_match("/^[a-z0-9_\-]+$/i", $primarykey)) {
        $primaryvalue = array_unique(is_array($primaryvalue) ? $primaryvalue : explode(',', $primaryvalue));
        //修复自定义data-primary-key为字符串内容时，给排序字段添加上引号
        $primaryvalue = array_map(function ($value) {
          return '\'' . $value . '\'';
        }, $primaryvalue);

        $primaryvalue = implode(',', $primaryvalue);

        $this->model->orderRaw("FIELD(`a`.`{$primarykey}`, {$primaryvalue})");
      } else {
        $this->model->order($order);
      }

      $datalist = $this->model->where($where)
        ->alias('a')->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.platform_id = ' . $custom['platform_id'] . ' And b.settlement_relationship = 2 And b.invoice_type = 3 And a.id = b.target_id')->field('a.*')
        ->page($page, $pagesize)
        ->select();


      foreach ($datalist as $index => $item) {
        unset($item['password'], $item['salt']);
        if ($this->selectpageFields == '*') {
          $result = [
            $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
            $field      => isset($item[$field]) ? $item[$field] : '',
          ];
        } else {
          $result = array_intersect_key(($item instanceof Model ? $item->toArray() : (array)$item), array_flip($fields));
        }
        $result['pid'] = isset($item['pid']) ? $item['pid'] : (isset($item['parent_id']) ? $item['parent_id'] : 0);
        $list[] = $result;
      }
      if ($istree && !$primaryvalue) {
        $tree = Tree::instance();
        $tree->init(collection($list)->toArray(), 'pid');
        $list = $tree->getTreeList($tree->getTreeArray(0), $field);
        if (!$ishtml) {
          foreach ($list as &$item) {
            $item = str_replace('&nbsp;', ' ', $item);
          }
          unset($item);
        }
      }
    }
    //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
    return json(['list' => $list, 'total' => $total]);
  }
}
