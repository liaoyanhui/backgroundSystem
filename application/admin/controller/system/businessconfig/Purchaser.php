<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-13 11:24:00
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-06-17 10:28:31
 * @FilePath: /baiying/application/admin/controller/system/businessconfig/Purchaser.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\system\businessconfig;

use app\admin\model\system\businessconfig\PurchaserLevel;
use app\common\controller\Backend;
use app\common\model\Contact;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;


/**
 * 采购商管理
 *
 * @icon fa fa-circle-o
 */
class Purchaser extends Backend
{
    /**
     * Purchaser模型对象
     * @var \app\admin\model\system\businessconfig\Purchaser
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        // $this->model = new \app\admin\model\system\businessconfig\Purchaser;
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    // public function index()
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
    //         ->alias('a')
    //         ->where($where)
    //         ->join('contact b', 'b.related_id = a.id')
    //         ->field('a.*,b.contact_name')
    //         ->order($sort, $order)
    //         ->paginate($limit);
    //     $result = ['total' => $list->total(), 'rows' => $list->items()];
    //     return json($result);
    // }

		/**
		 * 管理
		 *
		 * @return string|\think\response\Json
		 * @throws \think\Exception
		 * @throws \think\exception\DbException
		 */
    public function table1()
    {
        $this->model = new \app\admin\model\system\businessconfig\Purchaser;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->where($where)
                ->join('contact b', 'b.related_id = a.id and b.related_type = 4', 'LEFT')
                ->field('a.*, b.contact_name, b.contact_duty, b.contact_way, b.id as contact_id')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            $result = array("total" => $total, "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch('index');
    }

		/**
		 * 分级
		 *
		 * @return string|\think\response\Json
		 * @throws \think\Exception
		 * @throws \think\exception\DbException
		 */
    public function table2()
    {
        $this->model = new \app\admin\model\system\businessconfig\PurchaserLevel;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where('level', 1)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where('level', 1)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            $result = array("total" => $total, "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch('index');
    }

		/**
		 * 添加
		 *
		 * @return string|void
		 * @throws \think\Exception
		 * @throws \think\exception\DbException
		 */
    public function add()
    {
        $this->model = new \app\admin\model\system\businessconfig\Purchaser;
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
        $full_name = null;
        if (!empty($params['name5_id']) && $params['name5_id'] !== '') {
            $name5 = PurchaserLevel::get(['id' => $params['name5_id']]);
            $params['name5_name'] = $name5['name'];
            $full_name =  $name5['name'] . $full_name;
        } else {
            unset($params['name5_id']);
        }
        if (!empty($params['name4_id']) && $params['name4_id'] !== '') {
            $name4 = PurchaserLevel::get(['id' => $params['name4_id']]);
            $params['name4_name'] = $name4['name'];
            $full_name =  $name4['name'] . $full_name;
        } else {
            unset($params['name4_id']);
        }
        if (!empty($params['name3_id']) && $params['name3_id'] !== '') {
            $name3 = PurchaserLevel::get(['id' => $params['name3_id']]);
            $params['name3_name'] = $name3['name'];
            $full_name = $name3['name'] . $full_name;
        } else {
            unset($params['name3_id']);
        }
        if (!empty($params['name2_id']) && $params['name2_id'] !== '') {
            $name2 = PurchaserLevel::get(['id' => $params['name2_id']]);
            $params['name2_name'] = $name2['name'];
            $full_name =  $name2['name'] . $full_name;
        } else {
            unset($params['name2_id']);
        }
        if (!empty($params['name1_id']) && $params['name1_id'] !== '') {
            $name1 = PurchaserLevel::get(['id' => $params['name1_id']]);
            $params['name1_name'] = $name1['name'];
            $params['eshortname'] = $name1['eshortname'];
            $full_name =  $name1['name'] . $full_name;
        }
        $params['full_name'] = $full_name;

        $result = false;
        $result1 = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            $Contact = new Contact();

            if ($params['contact_name'] !== '') {
                $result1 = $Contact->allowField(true)->save([
                    'related_id' => $this->model->id,
                    'contact_name' => $params['contact_name'],
                    'contact_duty' => $params['contact_duty'],
                    'contact_way' => $params['contact_way'],
                    'related_type' => 4
                ]);
            } else {
                $result1 = true;
            }

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false || $result1 === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

		/**
		 * 编辑
		 *
		 * @param $ids
		 * @return string|void
		 * @throws \think\Exception
		 * @throws \think\exception\DbException
		 */
    public function edit($ids = null)
    {
        $this->model = new \app\admin\model\system\businessconfig\Purchaser;
        $row = $this->model->get($ids);
        $Contact = new Contact();
        $contact_row = $Contact->get(['related_id' => $ids, 'related_type' => 4]);
        $row['contact_name'] = $contact_row['contact_name'];
        $row['contact_duty'] = $contact_row['contact_duty'];
        $row['contact_way'] = $contact_row['contact_way'];
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
        $full_name = null;
        if (!empty($params['name5_id']) && $params['name5_id'] !== '') {
            $name5 = PurchaserLevel::get(['id' => $params['name5_id']]);
            $params['name5_name'] = $name5['name'];
            $full_name =  $name5['name'] . $full_name;
        } else {
            $params['name5_id'] = null;
            $params['name5_name'] = null;
        }
        if (!empty($params['name4_id']) && $params['name4_id'] !== '') {
            $name4 = PurchaserLevel::get(['id' => $params['name4_id']]);
            $params['name4_name'] = $name4['name'];
            $full_name =  $name4['name'] . $full_name;
        } else {
            $params['name4_id'] = null;
            $params['name4_name'] = null;
        }
        if (!empty($params['name3_id']) && $params['name3_id'] !== '') {
            $name3 = PurchaserLevel::get(['id' => $params['name3_id']]);
            $params['name3_name'] = $name3['name'];
            $full_name =  $name3['name'] . $full_name;
        } else {
            $params['name3_id'] = null;
            $params['name3_name'] = null;
        }
        if (!empty($params['name2_id']) && $params['name2_id'] !== '') {
            $name2 = PurchaserLevel::get(['id' => $params['name2_id']]);
            $params['name2_name'] = $name2['name'];
            $full_name =  $name2['name'] . $full_name;
        } else {
            $params['name2_id'] = null;
            $params['name2_name'] = null;
        }
        if (!empty($params['name1_id']) && $params['name1_id'] !== '') {
            $name1 = PurchaserLevel::get(['id' => $params['name1_id']]);
            $params['name1_name'] = $name1['name'];
            $params['eshortname'] = $name1['eshortname'];
            $full_name =  $name1['name'] . $full_name;
        }
        $params['full_name'] = $full_name;
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
            $result = $row->allowField(true)->save($params);
            if ($params['contact_name'] !== '') {
                $result1 = $Contact->allowField(true)->save([
                    'contact_name' => $params['contact_name'],
                    'contact_duty' => $params['contact_duty'],
                    'contact_way' => $params['contact_way']
                ], ['id' => $contact_row['id']]);
            } else {
                $result1 = true;
            }
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result || false === $result1) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

		/**
		 * 删除
		 *
		 * @param $ids
		 * @return void
		 * @throws \think\db\exception\DataNotFoundException
		 * @throws \think\db\exception\ModelNotFoundException
		 * @throws \think\exception\DbException
		 */
    public function del($ids = null)
    {
        $this->model = new \app\admin\model\system\businessconfig\Purchaser;
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

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
}
