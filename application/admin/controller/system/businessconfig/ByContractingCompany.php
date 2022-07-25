<?php

namespace app\admin\controller\system\businessconfig;

use app\admin\Constants;

use app\common\controller\Backend;
use think\Db;
use think\Validate;

use app\common\model\Contact;


use app\admin\model\SettlementRadio;
use app\admin\model\system\businessconfig\ByPlatformCompany;
use app\admin\model\system\businessconfig\ByBusinessCompany;
use Exception;
use think\exception\PDOException;
use think\Model;
use fast\Tree;

/**
 * 签约单位
 *
 * @icon fa fa-circle-o
 */
class ByContractingCompany extends Backend
{

  /**
   * ByContractingCompany模型对象
   * @var \app\admin\model\system\businessconfig\ByContractingCompany
   */
  protected $model = null;

  protected $noNeedRight = ['platformByBusiness'];

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\system\businessconfig\ByContractingCompany;
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
    $query = Db::name('contracting_company')
      ->alias('cc')
      ->field('cc.id,cc.name,cc.addr,c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way')
      ->join('by_contact c', 'c.related_id = cc.id and c.related_type = ' . Constants::RELATED_TYPE_CONTRACTING_COMPANY . ' and c.is_default = 2', 'LEFT')
      ->where($where)
      ->where('cc.deleted_at', 'null');
    if ($admin['related_type'] == Constants::RELATED_TYPE_CONTRACTING_COMPANY) {
      $query = $query->where('cc.id', $admin['related_id']);
    }

    $list = $query->order($sort, $order)->paginate($limit);

    $result = ['total' => $list->total(), 'rows' => $list->items()];
    return json($result);
  }

  /**
   * 编辑
   */
  public function edit($ids = null)
  {
    $list = Db::name('contracting_company')
      ->alias('cc')
      ->field('cc.id,cc.name,cc.addr,cc.intro,cc.remark,c.id as contact_id,c.contact_name,c.contact_duty,c.contact_way')
      ->join('by_contact c', 'c.related_id = cc.id and c.related_type = ' . Constants::RELATED_TYPE_CONTRACTING_COMPANY . ' and c.is_default = 2', 'LEFT')
      ->where('cc.id', '=', $ids)
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
          'related_type' => Constants::RELATED_TYPE_CONTRACTING_COMPANY
        ],
        $params['contact_id'] ? ['id' =>  $params['contact_id']] : []
      );
      $result1 = $this->model->allowField(true)->save(
        [
          'name' => $params['name'],
          'addr' => $params['addr'],
          'intro' => $params['intro'],
          'remark' => $params['remark']
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
    $contractResult = false;
    $contactResult = false;

    Db::startTrans();
    try {
      $contractResult = $this->model->allowField(true)->save([
        'name' => $params['name'],
        'addr' => $params['addr'],
        'intro' => $params['intro'],
        'remark' => $params['remark']
      ]);
      $contact = new Contact();
      $contactResult = $contact->allowField(true)->save([
        'related_id' => $this->model->id,
        'contact_name' => $params['contact_name'],
        'contact_duty' => $params['contact_duty'],
        'contact_way' => $params['contact_way'],
        'related_type' => Constants::RELATED_TYPE_CONTRACTING_COMPANY
      ]);
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }
    if ($contactResult === false || $contractResult === false) {
      $this->error(__('No rows were inserted'));
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

    $bccIds = $this->model->where('id', $ids)->column('id');
    $srIds1 = Db::name('settlement_ratio')->where('related_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_C_B)->column('id');
    $srIds2 = Db::name('settlement_ratio')->where('target_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->column('id');

    Db::startTrans();
    try {

      !empty($bccIds) && $this->model::destroy($bccIds);
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
  public function contracting_tab($ids)
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
    $this->view->assign("row", $detailRow->toArray());
    return $this->view->fetch();
  }

  /**
   * 业务公司结算比
   */
  public function cbSettlementRatio($ids = null)
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
      // group_concat(sr.province_name SEPARATOR ' ; ') as provinces,  省合并
      $list = Db::name('settlement_ratio')
        ->alias('sr')
        ->field("sr.id,sr.target_id,bc.name as target_name,cc.name as contracting_name, sr.invoice_type,
        sr.updated_at,sr.settlement_type,sr.settlement_ratio,
        sr.platform_id, pc.name as platform_name,c.contact_name, c.contact_way")
        ->join('contracting_company cc', 'cc.id = sr.related_id', 'LEFT')
        ->join('business_company bc', 'bc.id = sr.target_id', 'LEFT')
        ->join('platform_company pc', 'pc.id = sr.platform_id')
        ->join('contact c', 'c.related_id = sr.target_id and c.related_type = ' . Constants::RELATED_TYPE_BUSINESS_COMPANY . ' and c.is_default = 2', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_C_B)
        ->where('sr.related_id', $ids)
        ->where('sr.deleted_at', 'null')
        ->group('sr.target_id,sr.platform_id,sr.related_id')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 选中的业务公司中 未被选中的平台
   *
   * @internal
   */
  public function platformByBusiness($contracting_id = null, $platform_id = null)
  {
    //自定义搜索条件
    $custom = (array)$this->request->request("custom/a");
    $business_company_id = $custom['business_company_id'];

    // 如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
    $primarykey = $this->request->request("keyField");
    $primaryvalue = $this->request->request("keyValue");

    $where = [$primarykey => ['in', $primaryvalue]];

    // 去结算比例表获取当前业务公司下的所有平台     平台下的所有业务公司 
    $sql = Db::name('settlement_ratio')
      ->alias('sr')
      ->join('platform_company pc', 'pc.id = sr.platform_id', 'LEFT')
      ->where('settlement_relationship', Constants::RELATIONSHIP_C_B)
      ->where('sr.target_id', $business_company_id)
      ->where('sr.related_id', $contracting_id)
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
   * 添加业务公司结算比
   */
  public function c_b_settlement_ratio_add($contracting_id = null)
  {
    $this->relationSearch = true;
    $businessList = ByBusinessCompany::all();

    // 1订单结算 2月结算 3周结算 4背靠背结算
    $settlementType = [
      ['id' => Constants::SETTLEMENT_TYPE_MONTH, 'name' => '月结'],
      ['id' => Constants::SETTLEMENT_TYPE_ORDER, 'name' => '订单融'],
      ['id' => Constants::SETTLEMENT_TYPE_WEEK, 'name' => '周结'],
      ['id' => Constants::SETTLEMENT_TYPE_BACK, 'name' => '背靠背']
    ];

    if (false === $this->request->isAjax()) {
      $this->view->assign('contracting_id', $contracting_id);
      $this->view->assign('businessList', $businessList);
      $this->view->assign('settlementType', $settlementType);
      return $this->view->fetch();
    }

    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    $params1 = $this->request->post('row/a');

    $data = [
      'platform_id' => $params1['platform_id'],
      'related_id' => $contracting_id, 'target_id' => $params1['business_company_id'], 'settlement_type' => $params1['settlement_type'], 'settlement_relationship' => Constants::RELATIONSHIP_C_B,
      'settlement_ratio' => $params1['settlement_ratio'], 'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13,
    ];

    $validate = new Validate([
      'settlement_ratio|结算比例' => 'require|between:0,100',
    ]);

    if (!$validate->check($data)) {
      $this->error($validate->getError());
    }
    Db::name('settlement_ratio')->insert($data);
    return $this->success();
  }


  /**
   * 删除业务公司结算比
   */
  public function c_b_settlement_ratio_del($ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    // dump($ids);
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
   * 编辑业务公司结算比
   */
  public function c_b_settlement_ratio_edit($contracting_id = null, $ids = null)
  {

    $this->relationSearch = true;
    $settlementType = [
      ['id' => Constants::SETTLEMENT_TYPE_MONTH, 'name' => '月结算'],
      ['id' => Constants::SETTLEMENT_TYPE_ORDER, 'name' => '订单结算'],
      ['id' => Constants::SETTLEMENT_TYPE_WEEK, 'name' => '周结算'],
      ['id' => Constants::SETTLEMENT_TYPE_BACK, 'name' => '背靠背结算']
    ];
    // $platformList = ByPlatformCompany::all();
    $businessList = ByBusinessCompany::all();
    $data = SettlementRadio::get($ids);

    // 设置已选中的平台
    foreach ($businessList as $k => $v) {
      if ($v['id'] == $data['target_id']) {
        $businessList[$k]['selected'] = true;
      } else {
        $businessList[$k]['selected'] = false;
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

    // dump($data);
    // dump($platformList);
    // 编辑的签约单位
    // $business = Db::name('business_company')->where('id', $ids)->find();

    // 获取所有省份 同时获取 该签约单位已经签了的省份 
    // $province = Db::name('province')->select();
    // $businessProvince = Db::name('settlement_ratio')->where('target_id', $ids)->where('related_id', $contracting_id)->select();
    // $businessProvinceOne = Db::name('settlement_ratio')->where('target_id', $ids)->where('related_id', $contracting_id)->find();

    // 筛选出已经设置过的城市 合并结算比例数据 
    // foreach ($province as $k => $v) {
    //   $province[$k]['checked'] = 1;
    //   foreach ($businessProvince as $c => $m) {

    //     if ($m['province_id'] == $v['province_id']) {
    //       $province[$k]['checked'] = 2;
    //     }
    //   }
    // };

    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('data', $data);
      $this->view->assign('contracting_id', $contracting_id);
      $this->view->assign('businessList', $businessList);
      $this->view->assign('settlementType', $settlementType);
      return $this->view->fetch();
    }

    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }

    $params1 = $this->request->post('row/a');

    $params1 = $this->preExcludeFields($params1);


    // 开启事务 先删除该签约公司下签约的业务公司的所有城市 然后根据选中的重新设置
    $result = false;
    $result1 = false;
    Db::startTrans();

    try {
      $result = Db::name('settlement_ratio')->where('id', $ids)->delete();

      $data = [
        'platform_id' => $data['platform_id'],
        'related_id' => $contracting_id, 'target_id' => $data['target_id'], 'settlement_type' => $params1['settlement_type'], 'settlement_relationship' => Constants::RELATIONSHIP_C_B,
        'settlement_ratio' => $params1['settlement_ratio'], 'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13,
      ];

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
      // 为验证通过 会执行这里 所以在这里放一个验证失败的信息
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
   * 平台结算比
   */
  public function cpSettlementRatio($ids = null)
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
        ->field("sr.target_id,sr.related_id,cc.name as target_name, sr.invoice_type,sr.settlement_type,
        group_concat(sr.province_name,sr.settlement_ratio SEPARATOR ' ; ') as provinces, 
        sr.updated_at,sr.settlement_type,sr.settlement_ratio,sr.settlement_date, pc.name as related_name")
        ->join('contracting_company cc', 'cc.id = sr.target_id', 'LEFT')
        ->join('platform_company pc', 'pc.id = sr.related_id', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_P_C)
        ->where('sr.target_id', $ids)
        ->group('sr.related_id')
        ->where('sr.deleted_at', 'null')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
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
    // var_dump($custom);
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      $this->model->where($this->dataLimitField, 'in', $adminIds);
    }
    $list = [];
    $total = $this->model->alias('a')->where($where)->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.province_id = ' . $custom['province_id'] . ' And b.settlement_relationship = 3 And a.id = b.target_id')->count();
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


      $datalist = $this->model
        ->alias('a')->where($where)->join('by_settlement_ratio b', 'b.related_id = ' . $custom['related_id'] . ' And b.province_id = ' . $custom['province_id'] . ' And b.settlement_relationship = 3 And a.id = b.target_id')->field('a.*')
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
