<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-20 11:19:45
 * @LastEditTime: 2022-07-15 15:59:44
 * @FilePath: /baiying/application/admin/controller/system/businessconfig/BySupplier.php
 */

namespace app\admin\controller\system\businessconfig;

use app\common\controller\Backend;

use app\admin\model\area\City;
use app\admin\model\area\District;
use app\admin\model\area\Province;
use app\admin\model\system\Category;
use app\common\model\Contact;
use app\admin\model\SupplierCompany;
use app\admin\model\ByBusinessCompanySalesman;
use app\admin\Constants;
use app\admin\model\SupplierCategory;
use EasyWeChat\Kernel\Messages\Transfer;
use app\admin\model\SettlementRadio;

use Exception;
use think\exception\PDOException;
use think\Db;
use think\Model;
use fast\Tree;


/**
 * 供应商管理
 *
 * @icon fa fa-circle-o
 */
class BySupplier extends Backend
{

  /**
   * BySupplier模型对象
   * @var \app\admin\model\system\businessconfig\BySupplier
   */
  protected $model = null;

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\system\businessconfig\BySupplier;
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
    $subsql = SupplierCategory::alias('sc')->field("sc.id, sc.supplier_id, sc.category_id, group_concat(DISTINCT cn.name SEPARATOR ';') as operate_category_list")
      ->join('category_new cn', 'cn.id = sc.category_id', 'LEFT')->group('sc.supplier_id')->buildSql();

    // $subsql1 = SupplierCompany::alias('scd')->field('scd.id,scd.supplier_id, scd.business_company_id,scd.salesman_id,bcs.name as salesman_name,bcs.contact_way')
    //   ->join('business_company_salesman bcs', 'bcs.id = scd.salesman_id', 'LEFT')->buildSql();
    $query = Db::name('supplier')
      ->alias('s')
      ->field("s.id,s.name,s.addr,s.property_id,s.operate_name,s.intro,s.remark,
			w.operate_category_list,
			c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way")
      ->join([$subsql => 'w'], 's.id = w.supplier_id', 'LEFT')
      ->join('contact c', 'c.related_id = s.id and c.related_type = ' . Constants::RELATED_TYPE_SUPPLIER . ' and c.is_default = 2', 'LEFT')
      // ->join([$subsql1 => 's1'], 's.id = s1.supplier_id', 'LEFT')
      ->where($where)
      ->where('s.deleted_at', 'null');

    if ($admin['related_type'] == Constants::RELATED_TYPE_SUPPLIER) {
      $query = $query->where('s.id', $admin['related_id']);
    }

    $list = $query->group('s.id')->order($sort, $order)->paginate($limit);
    $result = ['total' => $list->total(), 'rows' => $list->items()];

    return json($result);
  }

  /**
   * 添加
   */
  public function add()
  {
    $propertyType = [
      ['id' => 1, 'name' => '小规模纳税人'],
      ['id' => 2, 'name' => '一般纳税人'],
      ['id' => 3, 'name' => '个体工商户'],
    ];

    // 供应类型
    $categoryList = Category::all();

    // 业务员
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (false === $this->request->isPost()) {
      $this->view->assign('propertyType', $propertyType);
      $this->view->assign('categoryList', $categoryList);
      return $this->view->fetch();
    }
    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);
    $province = Province::get(['province_id' => $params['province_id']]);
    $city = City::get(['city_id' => $params['city_id']]);
    $district = District::get(['district_id' => $params['district_id']]);

    Db::startTrans();
    try {
      // 新增供应商
      $this->model->allowField(true)->save([
        'name' => $params['name'],
        'province_id' => $params['province_id'],
        'province_cname' => $province['province_cname'],
        'city_id' => $params['city_id'],
        'city_cname' => $city['city_cname'],
        'district_id' => $params['district_id'],
        'district_cname' => $district['district_cname'],
        'addr' => $params['addr'],
        'property_id' => $params['property_id'],
        'operate_name' => $params['operate_name'],
        'intro' => $params['intro'],
        'remark' => $params['remark'],
      ]);
      // 新增联系人
      $contact = new Contact();
      $contact->allowField(true)->save([
        'related_id' => $this->model->id,
        'contact_name' => $params['contact_name'],
        'contact_duty' => $params['contact_duty'],
        'contact_way' => $params['contact_way'],
        'related_type' => Constants::RELATED_TYPE_SUPPLIER
      ]);
      // // 新增业务员和供应商 
      // $suppliercompany = new SupplierCompany();
      // $ByBusinessCompanySalesman = new ByBusinessCompanySalesman();
      // $company_id = $ByBusinessCompanySalesman::where('id', $params['salesman_id'])->value('company_id');
      // $suppliercompany->allowField(true)->save([
      //   'supplier_id' => $this->model->id,
      //   'business_company_id' => $company_id,
      //   'salesman_id' => $params['salesman_id']
      // ]);
      // 新增供应商和供应类型 多个
      $suppliercategory = new SupplierCategory();
      $categoryids = $_POST['categoryid'];
      $data = [];
      foreach ($categoryids as $k => $v) {
        $sql = [];
        $sql += [
          'supplier_id' => $this->model->id,
          'category_id' => $v,
        ];
        array_push($data, $sql);
      }
      $suppliercategory->allowField(true)->saveAll($data);

      Db::commit();
    } catch (PDOException | Exception $e) {
      Db::rollback();
      $this->error($e->getMessage());
    }
    return $this->success();
  }

  /**
   * 编辑
   */
  public function edit($ids = null)
  {
    $this->relationSearch = true;

    // 当前账号信息
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];

    $propertyType = [
      ['id' => 1, 'name' => '小规模纳税人'],
      ['id' => 2, 'name' => '一般纳税人'],
      ['id' => 3, 'name' => '个体工商户'],
    ];

    $subsql = SupplierCategory::alias('sc')->field('sc.id, sc.supplier_id, sc.category_id, cn.name as category_name')
      ->join('category_new cn', 'cn.id = sc.category_id', 'LEFT')->buildSql();
    // $subsql1 = SupplierCompany::alias('sc')->field('sc.id,sc.supplier_id,sc.business_company_id,sc.salesman_id,bcs.name as salesman_name,bcs.contact_way')
    //   ->join('business_company_salesman bcs', 'sc.salesman_id = bcs.id', 'LEFT')->buildSql();
    // s1.id as supplier_company_id, s1.salesman_id, s1.salesman_name, s1.contact_way as salesman_way 业务员
    $list = Db::name('supplier')
      ->alias('s')
      ->field("s.id,s.name,s.addr,s.property_id,s.operate_name,s.intro,s.remark,
        s.province_id,s.province_cname,s.city_id,s.city_cname,s.district_id,s.district_cname,
        group_concat(w.category_id SEPARATOR ';') as operate_category_list,
        group_concat(w.id SEPARATOR ',') as operate_category_id,
        c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way")
      ->join([$subsql => 'w'], 's.id = w.supplier_id', 'LEFT')
      ->join('contact c', 'c.related_id = s.id and c.related_type = ' . Constants::RELATED_TYPE_SUPPLIER . ' and c.is_default = 2', 'LEFT')
      // ->join([$subsql1 => 's1'], 's.id = s1.supplier_id', 'LEFT')
      ->where('s.id', $ids)
      ->group('w.supplier_id')
      ->find();
    // dump($list);
    if ($list['operate_category_list']) {
      $list['operate_category_list'] = explode(';', $list['operate_category_list']);
    } else {
      $list['operate_category_list'] = [];
    }
    // dump($list['operate_category_list']);
    // 供应类型
    $categoryList = Category::all();

    // 业务员
    $userInfo = $this->auth->getUserInfo();
    $related_type = $userInfo['related_type'];
    $related_id = $userInfo['related_id'];
    if (false === $this->request->isAjax()) {
      $this->view->assign('propertyType', $propertyType);
      $this->view->assign('row', $list);
      $this->view->assign('categoryList', $categoryList);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);
    $province = Province::get(['province_id' => $params['province_id']]);
    $city = City::get(['city_id' => $params['city_id']]);
    $district = District::get(['district_id' => $params['district_id']]);

    Db::startTrans();
    try {
      // 编辑供应商
      $this->model->allowField(true)->save([
        'name' => $params['name'],
        'province_id' => $params['province_id'],
        'province_cname' => $province['province_cname'],
        'city_id' => $params['city_id'],
        'city_cname' => $city['city_cname'],
        'district_id' => $params['district_id'],
        'district_cname' => $district['district_cname'],
        'addr' => $params['addr'],
        'property_id' => $params['property_id'],
        'operate_name' => $params['operate_name'],
        'intro' => $params['intro'],
        'remark' => $params['remark'],
      ], ['id' => $list['id']]);
      // 编辑联系人
      $contact = new Contact();
      $contact->allowField(true)->save([
        'related_id' => $list['id'],
        'contact_name' => $params['contact_name'],
        'contact_duty' => $params['contact_duty'],
        'contact_way' => $params['contact_way'],
        'related_type' => Constants::RELATED_TYPE_SUPPLIER
      ],  $list['contact_id'] ? ['id' => $list['contact_id']] : []);
      // 编辑 供应商对应的业务员下的业务公司 
      // $suppliercompany = new SupplierCompany();
      // $ByBusinessCompanySalesman = new ByBusinessCompanySalesman();
      // $company_id = $ByBusinessCompanySalesman::where('id', $params['salesman_id'])->value('company_id');
      // $suppliercompany->allowField(true)->save([
      //   'supplier_id' => $list['id'],
      //   'business_company_id' => $company_id,
      //   'salesman_id' => $params['salesman_id']
      // ], ['id' => $list['supplier_company_id']]);
      // 编辑供应商和供应类型 多个 先删除 后新增
      $suppliercategory = new SupplierCategory();
      $suppliercategory::destroy($list['operate_category_id']);

      $categoryids = $_POST['categoryid'];
      $data = [];
      foreach ($categoryids as $k => $v) {
        $sql = [];
        $sql += [
          'supplier_id' => $list['id'],
          'category_id' => $v,
        ];
        array_push($data, $sql);
      }
      $suppliercategory->allowField(true)->saveAll($data);

      Db::commit();
    } catch (PDOException | Exception $e) {
      Db::rollback();
      $this->error($e->getMessage());
    }
    return $this->success();
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


    $subsql = SupplierCategory::alias('sc')->field('sc.id, sc.supplier_id, sc.category_id, cn.name as category_name')
      ->join('category_new cn', 'cn.id = sc.category_id', 'LEFT')->buildSql();
    $subsql1 = SupplierCompany::alias('sc')->field('sc.id,sc.supplier_id,sc.business_company_id,sc.salesman_id,bcs.name as salesman_name,bcs.contact_way')
      ->join('business_company_salesman bcs', 'sc.salesman_id = bcs.id', 'LEFT')->buildSql();
    $list = Db::name('supplier')
      ->alias('s')
      ->field("s.id,
        group_concat(w.id SEPARATOR ',') as operate_category_id,
        c.id as contact_id,
        s1.id as supplier_company_id")
      ->join([$subsql => 'w'], 's.id = w.supplier_id', 'LEFT')
      ->join('contact c', 'c.related_id = s.id and c.related_type = ' . Constants::RELATED_TYPE_SUPPLIER . ' and c.is_default = 2', 'LEFT')
      ->join([$subsql1 => 's1'], 's.id = s1.supplier_id', 'LEFT')
      ->where('s.id', $ids)
      ->group('w.supplier_id')
      ->find();

    Db::startTrans();
    try {
      // 删除联系人表
      if ($list['contact_id']) {
        $contact = new Contact();
        $contact::destroy($list['contact_id']);
      }


      // 删除supplier category表
      if ($list['operate_category_id']) {
        $suppliercategory = new SupplierCategory();
        $suppliercategory::destroy($list['operate_category_id']);
      }

      // 删除供应商 和 业务员表
      $scIds = SupplierCompany::where('supplier_id', $ids)->column('id');
      SupplierCompany::destroy($scIds);
      // if ($list['supplier_company_id']) {
      //   $suppliercompany = new SupplierCompany();

      // }

      // 删除结算比例表
      $srIds = Db::name('settlement_ratio')->where('target_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_B_S)->column('id');
      !empty($srIds) && SettlementRadio::destroy($srIds);

      // 删除供应商
      $bccIds = $this->model->where('id', $ids)->column('id');
      !empty($bccIds) && $this->model::destroy($bccIds);

      Db::commit();
    } catch (\Throwable $th) {
      //throw $th;
      Db::rollback();
      $this->error($th->getMessage());
    }
    return $this->success();
  }

  /**
   * 详情
   */
  public function supplier_tab($ids)
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
    unset($detailRow['province_id']);
    unset($detailRow['city_id']);
    unset($detailRow['district_id']);
    unset($detailRow['property_id']);
    unset($detailRow['operate_name']);

    // unset($detailRow['id']);
    $this->view->assign("row", $detailRow->toArray());
    return $this->view->fetch();
  }

  /**
   * 业务公司结算比
   */
  public function sbSettlementRatio($ids = null)
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
      // group_concat(sr.province_name SEPARATOR ' ; ') as provinces, 

      $list = Db::name('settlement_ratio')
        ->alias('sr')
        ->field("sr.id,sr.target_id,s.name as target_name,bc.name as business_name,
        sr.updated_at, sr.invoice_type,sr.settlement_type,
        sr.settlement_type,sr.settlement_ratio,sr.settlement_date,pc.name as platform_name,c.contact_name,c.contact_way")
        ->join('business_company bc', 'bc.id = sr.related_id', 'LEFT')
        ->join('supplier s', 's.id = sr.target_id', 'LEFT')
        ->join('platform_company pc', 'pc.id = sr.platform_id', 'LEFT')
        ->join('contact c', 'c.related_id = sr.related_id and c.related_type = ' . Constants::RELATED_TYPE_BUSINESS_COMPANY . ' and c.is_default = 2', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_B_S)
        ->where('sr.deleted_at', 'null')
        ->where('sr.target_id', $ids)
        ->group('sr.target_id, sr.platform_id, sr.related_id,sr.invoice_type')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 业务员
   *  
   */
  public function businessSalesman($supplier_id = null)
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

    $admin = $this->auth->getUserInfo();
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();

    $query = SupplierCompany::alias('sc')->field('sc.id, s.name as supplier_name, bcs.name as salesman_name,bcs.contact_way as salesman_way, bcs.company_name as business_company_name')
      ->join('supplier s', 's.id = sc.supplier_id', 'LEFT')
      ->join('business_company_salesman bcs', 'sc.salesman_id = bcs.id', 'LEFT')
      ->where('sc.supplier_id', $supplier_id);

    if ($admin['related_type'] == Constants::RELATED_TYPE_SUPPLIER) {
      $query = $query->where('sc.supplier_id', $admin['related_id']);
    }

    $list = $query->group('sc.id')->order($sort, $order)->paginate($limit);
    $result = array("total" => $list->total(), "rows" => $list->items());
    return json($result);
  }

  /**
   * 添加业务员
   * 
   */
  public function salesman_add($supplier_id = null)
  {
    $this->relationSearch = true;

    // 删选出没有选过的业务员
    $salesmanList = [];
    $salesmanIds = SupplierCompany::where('supplier_id', $supplier_id)->column('salesman_id');
    $salesmanList = ByBusinessCompanySalesman::where('id', 'not in', $salesmanIds)->select();

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('salesmanList', $salesmanList);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    // 新增业务员和供应商 
    $suppliercompany = new SupplierCompany();
    $company_id = ByBusinessCompanySalesman::where('id', $params['salesman_id'])->value('company_id');
    $suppliercompany->allowField(true)->save([
      'supplier_id' => $supplier_id,
      'business_company_id' => $company_id,
      'salesman_id' => $params['salesman_id']
    ]);

    $this->success();
  }

  /**
   * 编辑业务员
   * 
   */
  public function salesman_edit($supplier_id = null, $ids = null)
  {
    $this->relationSearch = true;

    // 删选出没有选过的业务员
    $salesmanList = [];
    $salesmanIds = SupplierCompany::where('supplier_id', $supplier_id)->where('id', 'neq', $ids)->column('salesman_id');
    $salesmanList = ByBusinessCompanySalesman::where('id', 'not in', $salesmanIds)->select();

    // 获取当前编辑对象
    $list = SupplierCompany::get($ids);

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('salesmanList', $salesmanList);
      $this->view->assign('row', $list);
      return $this->view->fetch();
    }

    $params = $this->request->post('row/a');
    if (empty($params)) {
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $params = $this->preExcludeFields($params);

    // 新增业务员和供应商 
    $suppliercompany = new SupplierCompany();
    $company_id = ByBusinessCompanySalesman::where('id', $params['salesman_id'])->value('company_id');
    $suppliercompany->allowField(true)->save([
      'supplier_id' => $supplier_id,
      'business_company_id' => $company_id,
      'salesman_id' => $params['salesman_id']
    ], ['id' => $ids]);

    $this->success();
  }

  /**
   * 删除业务员
   * 
   */
  public function salesman_del($ids = null)
  {
    SupplierCompany::destroy($ids);
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
    $total = $this->model->where($where)
      ->alias('a')
      ->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.platform_id = ' . $custom['platform_id'] . ' And b.settlement_relationship = 1 And b.invoice_type = 3  And a.id = b.target_id')
      ->join('by_supplier_category c', 'c.category_id = ' . $custom['category_id'] . ' And c.supplier_id = a.id And c.deleted_at is null')
      ->count();
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
        ->alias('a')
        ->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.platform_id = ' . $custom['platform_id'] . ' And b.settlement_relationship = 1 And b.invoice_type = 3 And a.id = b.target_id')
        ->join('by_supplier_category c', 'c.category_id = ' . $custom['category_id'] . ' And c.supplier_id = a.id And c.deleted_at is null')
        ->field('a.*')
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
