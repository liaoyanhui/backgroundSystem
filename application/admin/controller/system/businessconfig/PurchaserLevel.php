<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-13 14:57:04
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-12 13:47:17
 * @FilePath: /baiying/application/admin/controller/system/businessconfig/PurchaserLevel.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\system\businessconfig;

use app\common\controller\Backend;
use EasyWeChat\Kernel\Messages\Location;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 采购商分级管理
 *
 * @icon fa fa-circle-o
 */
class PurchaserLevel extends Backend
{

    /**
     * PurchaserLevel模型对象
     * @var \app\admin\model\system\businessconfig\PurchaserLevel
     */
    protected $model = null;
		protected $noNeedRight = ['list'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\system\businessconfig\PurchaserLevel;
    }


    /**
     * 分级详情
     * 
     * @remark 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * @remark 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * @remark 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function detail($ids = null)
    {
        $this->assignconfig("ids", $this->request->param("ids"));

        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $row = $this->model->get(['id' => $ids]);
            $this->view->assign("row", $row);
            return $this->view->fetch();
        }
        [$where, $sort, $order, $offset, $limit, $addtabs] = $this->buildparams();

        $list = $this->model
            ->alias('pr')
            ->where($where)
            ->where('pr.ancestry_id', $ids)
            ->where('pr.deleted_at', NULL)
            ->join('purchaser_level pl', 'pr.parent_id = pl.id')
            ->field('pr.*,pl.name as parent_name')
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    // public function detail($ids = null)
    // {
    //     //设置过滤方法
    //     $this->request->filter(['strip_tags', 'trim']);
    //     if (false === $this->request->isAjax()) {
    //         $row = $this->model->get(['id' => $ids]);
    //         $this->view->assign("row", $row);
    //         return $this->view->fetch();
    //     }
    //     [$where, $sort, $order, $offset, $limit, $addtabs] = $this->buildparams();

    //     $list = $this->model
    //         ->alias('pr')
    //         ->where($where)
    //         ->where('pr.ancestry_id', $addtabs)
    //         ->join('purchaser_level pl', 'pr.parent_id = pl.id')
    //         ->field('pr.*,pl.name as parent_name')
    //         ->order($sort, $order)
    //         ->paginate($limit);
    //     $result = ['total' => $list->total(), 'rows' => $list->items()];
    //     return json($result);
    // }

		/**
		 * 多级添加
		 *
		 * @param $ids
		 * @return string|void
		 * @throws \think\Exception
		 */
    public function add2($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->view->assign("parent_id", $ids);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');

        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if (!empty($params['three']) && $params['three'] !== '') {
            $params['parent_id'] = $params['three'];
            $params['level'] = 5;
        } else if (!empty($params['second']) && $params['second'] !== '') {
            $params['parent_id'] = $params['second'];
            $params['level'] = 4;
        } else if (!empty($params['first']) && $params['first'] !== '') {
            $params['parent_id'] = $params['first'];
            $params['level'] = 3;
        } else {
            $params['parent_id'] = $ids;
            $params['level'] = 2;
        }
        $params['ancestry_id'] = $ids;

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

		/**
		 * 多级编辑
		 *
		 * @param $ids
		 * @return string|void
		 * @throws \think\Exception
		 * @throws \think\exception\DbException
		 */
    public function edit2($ids = null)
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
            if ($row['level'] === 3) {
                $parentRow = $this->model->get($row['parent_id']);
                $row['first_name'] = $parentRow['name'];
                $row['first_id'] = $parentRow['id'];
            } else  if ($row['level'] === 4) {
                $parentRow = $this->model->get($row['parent_id']);
                $parentParentRow = $this->model->get($parentRow['parent_id']);
                $row['first_name'] = $parentRow['name'];
                $row['first_id'] = $parentRow['id'];
                $row['second_name'] = $parentParentRow['name'];
                $row['second_id'] = $parentParentRow['id'];
            } else  if ($row['level'] === 5) {
                $parentRow = $this->model->get($row['parent_id']);
                $parentParentRow = $this->model->get($parentRow['parent_id']);
                $parentParentParentRow = $this->model->get($parentParentRow['parent_id']);
                $row['first_name'] = $parentRow['name'];
                $row['first_id'] = $parentRow['id'];
                $row['second_name'] = $parentParentRow['name'];
                $row['second_id'] = $parentParentRow['id'];
                $row['three_name'] = $parentParentParentRow['name'];
                $row['three_id'] = $parentParentParentRow['id'];
            }

            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        if (!empty($params['three']) && $params['three'] !== '') {
            $params['parent_id'] = $params['three'];
            $params['level'] = 5;
        } else if (!empty($params['second']) && $params['second'] !== '') {
            $params['parent_id'] = $params['second'];
            $params['level'] = 4;
        } else if (!empty($params['first']) && $params['first'] !== '') {
            $params['parent_id'] = $params['first'];
            $params['level'] = 3;
        } else {
            $params['parent_id'] = $row['ancestry_id'];
            $params['level'] = 2;
        }
        $params['ancestry_id'] = $row['ancestry_id'];
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
     * 读取分类数据,联动列表
		 *
		 * @internal
     */
    public function list()
    {
        $pid = $this->request->get('pid', '');
        $ids = $this->request->get('ids', '');
        $purcharlist = null;
        if ($pid) {
            $where['parent_id'] = $pid;
        } else {
            $where['parent_id'] = 0;
        }
        // // if ($type) {
        // //     $where['type'] = $type;
        // // }
        $purcharlist = Db::name('purchaser_level')->where($where)->where('id', 'not in', $ids)->field('id as value,name')->order('id desc')->select();

        $this->success('', '', $purcharlist);
    }
}
