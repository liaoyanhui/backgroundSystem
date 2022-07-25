<?php
/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-13 15:03:06
 * @LastEditTime: 2022-07-19 14:42:12
 * @FilePath: /baiying/application/admin/controller/system/businessconfig/ByPlatformCompany.php
 */

namespace app\admin\controller\system\businessconfig;

use app\common\controller\Backend;
use think\Db;
use app\admin\model\SettlementRadio;
use think\Validate;


use Exception;
use think\exception\PDOException;
use app\admin\Constants;

/**
 * 平台公司管理
 *
 * @icon fa fa-circle-o
 */
class ByPlatformCompany extends Backend
{

  /**
   * ByPlatformCompany模型对象
   * @var \app\admin\model\system\businessconfig\ByPlatformCompany
   */
  protected $model = null;

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\system\businessconfig\ByPlatformCompany;
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


    $subsql = SettlementRadio::field('related_id, count(DISTINCT(target_id)) as contractCount')
      ->where('deleted_at', 'null')->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->group('related_id')->buildSql();
    [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    $list = Db::name('platform_company')
      ->alias('pc')
      ->field('pc.id as id,pc.name,pc.intro,pc.remark,w.contractCount')
      ->join([$subsql => 'w'], 'pc.id = w.related_id', 'LEFT')
      ->where($where)
      ->where('deleted_at', 'null')
      ->order($sort, $order)
      ->paginate($limit);
    $result = ['total' => $list->total(), 'rows' => $list->items()];

    return json($result);
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
    $bpcIds = $this->model->where('id', $ids)->column('id');
    $srIds = Db::name('settlement_ratio')->where('related_id', $ids)->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->column('id');
    Db::startTrans();
    try {
      !empty($bpcIds) && $this->model::destroy($bpcIds);
      !empty($srIds) && SettlementRadio::destroy($srIds);
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
  public function platform_tab($ids)
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
   * 结算比例表
   */
  public function settlement_ratio($ids = null)
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
        ->field("sr.id,sr.target_id,cc.name as target_name,sr.invoice_type,sr.settlement_type,pc.name as platform_name,
        sr.updated_at,group_concat(sr.province_name,sr.settlement_ratio SEPARATOR ' ; ') as provinces, c.contact_way, c.contact_name")
        ->join('contracting_company cc', 'cc.id = sr.target_id', 'LEFT')
        ->join('contact c', 'c.related_id = sr.target_id and c.related_type = ' . Constants::RELATED_TYPE_CONTRACTING_COMPANY . ' and c.is_default = 2', 'LEFT')
        ->where($where)
        ->where('settlement_relationship', Constants::RELATIONSHIP_P_C)
        ->where('sr.related_id', $ids)
        ->where('sr.deleted_at', 'null')
        ->join('platform_company pc', 'pc.id = sr.platform_id', 'LEFT')
        ->group('sr.target_id')
        ->order($sort, $order)
        ->paginate($limit);
      $result = array("total" => $list->total(), "rows" => $list->items());
      return json($result);
    }
  }

  /**
   * 添加签约单位
   */
  public function settlement_ratio_add($platform_id = null)
  {
    $this->relationSearch = true;
    $settlementRatioList = Db::name('settlement_ratio')
      ->alias('sr')
      ->field("sr.id,sr.target_id,cc.name as target_name")
      // ->field("sr.target_id,cc.name as target_name")
      ->join('contracting_company cc', 'cc.id = sr.target_id', 'LEFT')
      ->where('settlement_relationship', Constants::RELATIONSHIP_P_C)
      ->where('sr.related_id', $platform_id)
      ->where('sr.deleted_at', 'null')
      ->group('sr.target_id')
      ->column('sr.target_id');

    $contractingCompayList = Db::name('contracting_company')->where('id', 'not in', $settlementRatioList)->select();

    $province = Db::name('province')->select();

    foreach ($province as $k => $v) {
      $province[$k]['settlement_radio'] = 0;
    };

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('contractingCompayList', $contractingCompayList);
      $this->view->assign('province', $province);
      return $this->view->fetch();
    }
    //如果发送的来源是 Selectpage，则转发到 Selectpage
    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    $params = $this->request->post('id/a');
    $params1 = $this->request->post('row/a');

    if (empty($params)) {
      $this->error('签约省份不能为空');
    }
    $params = $this->preExcludeFields($params);
    $params1 = $this->preExcludeFields($params1);
    // dump($params1);
    $listIds = [];
    foreach ($params as $k => $v) {
      array_push($listIds, $k);
    }
    $data = [];
    foreach ($listIds as $k => $v) {
      $sql = [];
      $sql += [
        'related_id' => $platform_id, 'target_id' => $params1['contracting_company'],
        'province_id' => $v,  'province_name' => $params[$v],  'settlement_ratio' => $params1[$v], 'settlement_relationship' => Constants::RELATIONSHIP_P_C,
        'settlement_type' => Constants::SETTLEMENT_TYPE_MONTH, 'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13, 'platform_id' => $platform_id,
      ];
      array_push($data, $sql);
    }
    $validate = new Validate([
      'settlement_ratio|结算比例' => 'require|between:0,100',
    ]);
    foreach ($data as $k => $v) {
      if (!$validate->check($v)) {
        $this->error($validate->getError());
      }
    }
    Db::name('settlement_ratio')->insertAll($data);
    return $this->success();
  }

  /**
   * 删除签约单位
   */
  public function settlement_ratio_del($platform_id = null, $ids = null)
  {
    if (false === $this->request->isPost()) {
      $this->error(__("Invalid parameters"));
    }
    $ids = $ids ?: $this->request->post("ids");
    if (empty($ids)) {
      $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    $settlementRatio = new SettlementRadio();
    $list = $settlementRatio->where('target_id', $ids)->where('related_id', $platform_id)->where('platform_id', $platform_id)->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->select();
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
   * 编辑签约单位
   */
  public function settlement_ratio_edit($platform_id = null, $ids = null)
  {
    $this->relationSearch = true;
    // 编辑的签约单位名称
    $contractingCompay = Db::name('contracting_company')->where('id', $ids)->find();

    // 获取所有省份 同时获取 该签约单位已经签了的省份 
    $province = Db::name('province')->select();
    $contractingProvince = Db::name('settlement_ratio')->where('platform_id', $platform_id)->where('target_id', $ids)->where('related_id', $platform_id)->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->select();

    // 筛选出已经设置过的城市 合并结算比例数据 
    foreach ($province as $k => $v) {
      $province[$k]['settlement_radio'] = 0;
      $province[$k]['checked'] = 1;
      foreach ($contractingProvince as $c => $m) {

        if ($m['province_id'] == $v['province_id']) {
          $province[$k]['settlement_radio'] = $m['settlement_ratio'];
          $province[$k]['checked'] = 2;
        }
      }
    };

    // 设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
      $this->view->assign('contractingCompay', $contractingCompay);
      $this->view->assign('province', $province);
      return $this->view->fetch();
    }
    //如果发送的来源是 Selectpage，则转发到 Selectpage
    if ($this->request->request('keyField')) {
      return $this->selectpage();
    }
    $params = $this->request->post('id/a');
    $params1 = $this->request->post('row/a');

    if (empty($params)) {
      $this->error('签约省份不能为空');
    }
    $params = $this->preExcludeFields($params);
    $params1 = $this->preExcludeFields($params1);

    $listIds = [];
    foreach ($params as $k => $v) {
      array_push($listIds, $k);
    }

    // 开启事务 先删除该签约单位下签约的所有城市 然后根据选中的重新设置
    $result = false;
    $result1 = false;
    Db::startTrans();
    try {
      $result = Db::name('settlement_ratio')->where('platform_id', $platform_id)->where('target_id', $ids)->where('related_id', $platform_id)->where('settlement_relationship', Constants::RELATIONSHIP_P_C)->delete();
      $data = [];
      foreach ($listIds as $k => $v) {
        $sql = [];
        $sql += [
          'related_id' => $platform_id, 'target_id' => $ids,
          'province_id' => $v,  'province_name' => $params[$v],  'settlement_ratio' => $params1[$v], 'settlement_relationship' => Constants::RELATIONSHIP_P_C,
          'settlement_type' => Constants::SETTLEMENT_TYPE_MONTH, 'invoice_type' => Constants::INVOICE_TYPE_SPECIAL_13, 'platform_id' => $platform_id,
        ];
        array_push($data, $sql);
      }
      $validate = new Validate([
        'settlement_ratio|结算比例' => 'require|between:0,100',
      ]);
      // dump($data);
      foreach ($data as $k => $v) {
        if (!$validate->check($v)) {
          $this->error($validate->getError());
        }
      }
      $result1 = Db::name('settlement_ratio')->insertAll($data);
      Db::commit();
    } catch (\Throwable $th) {
      Db::rollback();
      $this->error($th->getMessage());
    }
    if (false === $result || false === $result1) {
      $this->error(__('No rows were updated'));
    }
    return $this->success();
  }

  /**
   * 下拉
   *
   * @return \think\response\Json
   */
  public function selectpage()
  {
    return parent::selectpage();
  }
}
