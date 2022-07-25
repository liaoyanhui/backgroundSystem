<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-21 10:06:58
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-20 16:25:04
 * @FilePath: /baiying/application/admin/controller/order/Order.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\order;

use app\admin\Constants;
use app\admin\model\ByBusinessCompanySalesman;
use app\admin\model\order\SubOrder;
use app\admin\model\project\Project;
use app\admin\model\SettlementRadio;
use app\admin\model\SupplierCompany;
use app\admin\model\system\businessconfig\ByBusinessCompany;
use app\admin\model\system\businessconfig\ByContractingCompany;
use app\admin\model\system\businessconfig\ByPlatformCompany;
use app\admin\model\system\businessconfig\BySupplier as BusinessconfigBySupplier;
use app\admin\model\system\businessconfig\Purchaser;
use app\admin\model\system\Category;
use app\common\controller\Backend;
use think\Cache;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * 订单
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\order\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Order;
        // $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 查看
     *
     * @remark 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * @remark 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * @remark 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
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
            ->where('status', $ids);

        switch ($admin['related_type']) {
            case Constants::RELATED_TYPE_SUPPLIER:
                $query = $query->where('supplier_id', $admin['related_id']);
                break;
            case Constants::RELATED_TYPE_BUSINESS_COMPANY:
                $query = $query->where('business_company_id', $admin['related_id']);
                break;
            case Constants::RELATED_TYPE_CONTRACTING_COMPANY:
                $query = $query->where('contracting_company_id', $admin['related_id']);
                break;
        }

        $list = $query->order($sort, $order)->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    // public function sendIndex($ids = null)
    // {
    //     //设置过滤方法
    //     $this->request->filter(['strip_tags', 'trim']);
    //     if (false === $this->request->isAjax()) {
    //         return $this->view->fetch();
    //     }
    //     //如果发送的来源是 Selectpage，则转发到 Selectpage
    //     if ($this->request->request('keyField')) {
    //         return $this->selectpage();
    //     }
    //     [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    //     $list = $this->model
    //         ->where($where)
    //         ->where('status', $ids)
    //         ->order($sort, $order)
    //         ->paginate($limit);
    //     $result = ['total' => $list->total(), 'rows' => $list->items()];
    //     return json($result);
    // }
    // public function sentIndex($ids = null)
    // {
    //     //设置过滤方法
    //     $this->request->filter(['strip_tags', 'trim']);
    //     if (false === $this->request->isAjax()) {
    //         return $this->view->fetch();
    //     }
    //     //如果发送的来源是 Selectpage，则转发到 Selectpage
    //     if ($this->request->request('keyField')) {
    //         return $this->selectpage();
    //     }
    //     [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    //     $list = $this->model
    //         ->where($where)
    //         ->where('status', $ids)
    //         ->order($sort, $order)
    //         ->paginate($limit);
    //     $result = ['total' => $list->total(), 'rows' => $list->items()];
    //     return json($result);
    // }
    // public function completeIndex($ids = null)
    // {
    //     //设置过滤方法
    //     $this->request->filter(['strip_tags', 'trim']);
    //     if (false === $this->request->isAjax()) {
    //         return $this->view->fetch();
    //     }
    //     //如果发送的来源是 Selectpage，则转发到 Selectpage
    //     if ($this->request->request('keyField')) {
    //         return $this->selectpage();
    //     }
    //     [$where, $sort, $order, $offset, $limit] = $this->buildparams();
    //     $list = $this->model
    //         ->where($where)
    //         ->where('status', $ids)
    //         ->order($sort, $order)
    //         ->paginate($limit);
    //     $result = ['total' => $list->total(), 'rows' => $list->items()];
    //     return json($result);
    // }

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
        $params['order_no'] = 'S0' . (20000000000000 + Cache::inc('orderNo'));
        $project = Project::get($params['project_id']);
        $category = Category::get($params['category_id']);
        $platform = ByPlatformCompany::get($params['platform_company_id']);
        $purchaser = Purchaser::get($project['purchaser_id']);
        $contacting = ByContractingCompany::get($params['contracting_company_id']);
        $bussiness = ByBusinessCompany::get($params['business_company_id']);
        $supplier = BusinessconfigBySupplier::get($params['supplier_id']);
        $params['project_name'] = $project['name'];
        $params['project_province'] = $project['province_cname'];
        $params['project_province_id'] = $project['province_id'];
        $params['category_name'] = $category['name'];
        $params['platform_company_name'] = $platform['name'];
        $params['purchaser_name'] = $purchaser['full_name'];
        $params['purchaser_id'] = $purchaser['id'];
        $params['contracting_company_name'] = $contacting['name'];
        $params['business_company_name'] = $bussiness['name'];
        $params['supplier_name'] = $supplier['name'];
        $settlement_radio1 = SettlementRadio::get(['platform_id' => $platform['id'], 'related_id' => $bussiness['id'], 'target_id' => $supplier['id'], 'settlement_relationship' => Constants::RELATIONSHIP_B_S, 'province_id' => null]);
        $settlement_radio2 = SettlementRadio::get(['platform_id' => $platform['id'], 'related_id' => $contacting['id'], 'target_id' => $bussiness['id'], 'settlement_relationship' => Constants::RELATIONSHIP_C_B, 'province_id' => null]);
        $settlement_radio3 = SettlementRadio::get(['province_id' => $project['province_id'], 'related_id' => $platform['id'], 'target_id' => $contacting['id'], 'settlement_relationship' => Constants::RELATIONSHIP_P_C]);
        $params['settlement_type1'] = $settlement_radio1['settlement_type'];
        $params['settlement_ratio1'] = $settlement_radio1['settlement_ratio'];
        $params['settlement_type2'] = $settlement_radio2['settlement_type'];
        $params['settlement_ratio2'] = $settlement_radio2['settlement_ratio'];
        $params['settlement_type3'] = $settlement_radio3['settlement_type'];
        $params['settlement_ratio3'] = $settlement_radio3['settlement_ratio'];
        $supplierBussiness = SupplierCompany::get(['supplier_id' => $supplier['id'], 'business_company_id' => $bussiness['id']]);
        $params['salesman_id'] = $supplierBussiness['salesman_id'];
        $bussinessSalesman = ByBusinessCompanySalesman::get(['id' => $supplierBussiness['salesman_id']]);
        $params['salesman_name'] = $bussinessSalesman['name'];
        $params['amount'] = round($params['amount'], 2);


        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }



    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        $project = Project::get($params['project_id']);
        $category = Category::get($params['category_id']);
        $platform = ByPlatformCompany::get($params['platform_company_id']);
        $purchaser = Purchaser::get($project['purchaser_id']);
        $contacting = ByContractingCompany::get($params['contracting_company_id']);
        $bussiness = ByBusinessCompany::get($params['business_company_id']);
        $supplier = BusinessconfigBySupplier::get($params['supplier_id']);
        $params['project_name'] = $project['name'];
        $params['project_province'] = $project['province_cname'];
        $params['project_province_id'] = $project['province_id'];
        $params['category_name'] = $category['name'];
        $params['platform_company_name'] = $platform['name'];
        $params['purchaser_name'] = $purchaser['full_name'];
        $params['purchaser_id'] = $purchaser['id'];
        $params['contracting_company_name'] = $contacting['name'];
        $params['business_company_name'] = $bussiness['name'];
        $params['supplier_name'] = $supplier['name'];
        $settlement_radio1 = SettlementRadio::get(['platform_id' => $platform['id'], 'related_id' => $bussiness['id'], 'target_id' => $supplier['id'], 'settlement_relationship' => Constants::RELATIONSHIP_B_S, 'province_id' => null]);
        $settlement_radio2 = SettlementRadio::get(['platform_id' => $platform['id'], 'related_id' => $contacting['id'], 'target_id' => $bussiness['id'], 'settlement_relationship' => Constants::RELATIONSHIP_C_B, 'province_id' => null]);
        $settlement_radio3 = SettlementRadio::get(['province_id' => $project['province_id'], 'related_id' => $platform['id'], 'target_id' => $contacting['id'], 'settlement_relationship' => Constants::RELATIONSHIP_P_C]);
        $params['settlement_type1'] = $settlement_radio1['settlement_type'];
        $params['settlement_ratio1'] = $settlement_radio1['settlement_ratio'];
        $params['settlement_type2'] = $settlement_radio2['settlement_type'];
        $params['settlement_ratio2'] = $settlement_radio2['settlement_ratio'];
        $params['settlement_type3'] = $settlement_radio3['settlement_type'];
        $params['settlement_ratio3'] = $settlement_radio3['settlement_ratio'];
        $supplierBussiness = SupplierCompany::get(['supplier_id' => $supplier['id'], 'business_company_id' => $bussiness['id']]);
        $params['salesman_id'] = $supplierBussiness['salesman_id'];
        $bussinessSalesman = ByBusinessCompanySalesman::get(['id' => $supplierBussiness['salesman_id']]);
        $params['salesman_name'] = $bussinessSalesman['name'];
        $params['amount'] = round($params['amount'], 2);

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 审核
     *
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     * @remark 审核
     */
    public function audit($ids)
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }

        Db::startTrans();
        try {
            $result = $this->model->save([
                'status'  => Constants::ORDER_STATUS_WAIT_DELIVERY,
                'audit_at' => date('Y-m-d H:i:s', time())
            ], ['id' => $ids]);;
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were update'));
        }
        $this->success();
    }

    /**
     * 发货
     *
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @remark 发货
     */
    public function send($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['status'] = Constants::ORDER_STATUS_DELIVERED;

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 签收
     *
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @remark 签收
     */
    public function arrived($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['status'] = Constants::ORDER_STATUS_FINISHED;

        $result = false;
        $result1 = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $subOrderParams['order_id'] = $row['id'];
            $subOrderParams['sub_order_no'] = $row['order_no'];
            $subOrderParams['project_id'] = $row['project_id'];
            $subOrderParams['project_name'] = $row['project_name'];
            $subOrderParams['purchaser_id'] = $row['purchaser_id'];
            $subOrderParams['category_id'] = $row['category_id'];
            $subOrderParams['category_name'] = $row['category_name'];
            $subOrderParams['platform_company_id'] = $row['platform_company_id'];
            $subOrderParams['platform_company_name'] = $row['platform_company_name'];
            $subOrderParams['purchaser_name'] = $row['purchaser_name'];
            $subOrderParams['project_province_id'] = $row['project_province_id'];
            $subOrderParams['project_province'] = $row['project_province'];
            $subOrderParams['order_amount'] = $row['amount'];
            $subOrderParams['status'] = Constants::SUB_ORDER_STATUS_WAIT_INVOICE;


            $subOrder1 = $subOrderParams;
            $subOrder2 = $subOrderParams;
            $subOrder3 = $subOrderParams;
            $subOrder1['from_id'] = $row['business_company_id'];
            $subOrder1['from_name'] = $row['business_company_name'];
            $subOrder1['to_id'] = $row['supplier_id'];
            $subOrder1['to_name'] = $row['supplier_name'];
            $subOrder1['settlement_relationship'] = Constants::RELATIONSHIP_B_S;
            $subOrder1['settlement_type'] = $row['settlement_type1'];
            $subOrder1['settlement_ratio'] = $row['settlement_ratio1'];
            // $subOrder1['settlement_amount'] = floor($row['amount'] * $row['settlement_ratio1']) / 100;

            $subOrder2['from_id'] = $row['contracting_company_id'];
            $subOrder2['from_name'] = $row['contracting_company_name'];
            $subOrder2['to_id'] = $row['business_company_id'];
            $subOrder2['to_name'] = $row['business_company_name'];
            $subOrder2['settlement_relationship'] = Constants::RELATIONSHIP_C_B;
            $subOrder2['settlement_type'] = $row['settlement_type2'];
            $subOrder2['settlement_ratio'] = $row['settlement_ratio2'];
            // $subOrder2['settlement_amount'] = floor($row['amount'] * $row['settlement_ratio2']) / 100;

            $subOrder3['from_id'] = $row['platform_company_id'];
            $subOrder3['from_name'] = $row['platform_company_name'];
            $subOrder3['to_id'] = $row['contracting_company_id'];
            $subOrder3['to_name'] = $row['contracting_company_name'];
            $subOrder3['settlement_relationship'] = Constants::RELATIONSHIP_P_C;
            $subOrder3['settlement_type'] = $row['settlement_type3'];
            $subOrder3['settlement_ratio'] = $row['settlement_ratio3'];
            // $subOrder3['settlement_amount'] = floor($row['amount'] * $row['settlement_ratio3']) / 100;

            $result = $row->allowField(true)->save($params);
            // // 订单完成后，生成子订单
            $subOrder = new SubOrder();
            $result1 = $subOrder->saveAll([$subOrder1, $subOrder2, $subOrder3]);

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false || $result1 === false) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }
}
