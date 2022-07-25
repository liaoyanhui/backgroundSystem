<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-07 17:14:50
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-13 16:06:23
 * @FilePath: /baiying/application/admin/controller/project/Project.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\controller\project;

use app\admin\model\area\City;
use app\admin\model\area\District;
use app\admin\model\area\Province;
use app\admin\model\system\businessconfig\Purchaser;
use Exception;
use app\common\controller\Backend;
use think\Cache;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;
use fast\Tree;

/**
 * 项目
 *
 * @icon fa fa-circle-o
 */
class Project extends Backend
{

  /**
   * Project模型对象
   * @var \app\admin\model\project\Project
   */
  protected $model = null;

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\project\Project;
  }



  /**
   * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
   * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
   * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
   */

  /**
   * 添加
   *
   * @return string
   * @throws \think\Exception
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
    $province = Province::get(['province_id' => $params['province_id']]);
    $city = City::get(['city_id' => $params['city_id']]);
    $district = District::get(['district_id' => $params['district_id']]);
    $params['province_cname'] = $province['province_cname'];
    $params['city_cname'] = $city['city_cname'];
    $params['district_cname'] = $district['district_cname'];
    $purchaser = Purchaser::get(['id' => $params['purchaser_id']]);
    $inc = str_pad(Cache::inc('BY' . $province['province_eshortname'] . $purchaser['eshortname'] . $province['province_id'] . $params['purchaser_id']), 6, '0', STR_PAD_LEFT);
    $params['project_no'] = $province['province_eshortname'] . $purchaser['eshortname'] . $inc;

    $params['purchaser_fullname'] = $purchaser['full_name'];
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
   * 编辑
   *
   * @param $ids
   * @return string
   * @throws DbException
   * @throws \think\Exception
   */
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
    $province = Province::get(['province_id' => $params['province_id']]);
    $city = City::get(['city_id' => $params['city_id']]);
    $district = District::get(['district_id' => $params['district_id']]);
    $params['province_cname'] = $province['province_cname'];
    $params['city_cname'] = $city['city_cname'];
    $params['district_cname'] = $district['district_cname'];
    $purchaser = Purchaser::get(['id' => $params['purchaser_id']]);
    $params['purchaser_fullname'] = $purchaser['full_name'];
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
   * 下拉
   *
   * @return \think\response\Json
   * @throws \think\Exception
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\ModelNotFoundException
   * @throws \think\exception\DbException
   */
  public function selectpage()
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
      $where = [$primarykey => ['in', $primaryvalue]];
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
        if ($custom && is_array($custom)) {
          foreach ($custom as $k => $v) {
            if (is_array($v) && 2 == count($v)) {
              $query->where($k, trim($v[0]), $v[1]);
            } else {
              $query->where($k, '=', $v);
            }
          }
        }
      };
    }
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      $this->model->where($this->dataLimitField, 'in', $adminIds);
    }
    $list = [];
    $total = $this->model->where($where)->count();
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

        $this->model->orderRaw("FIELD(`{$primarykey}`, {$primaryvalue})");
      } else {
        $this->model->order($order);
      }

      $datalist = $this->model->where($where)
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
        $result['purchaser_fullname'] = $item['purchaser_fullname'];
        $result['purchaser_id'] = $item['purchaser_id'];
        $result['province_id'] = $item['province_id'];
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

  /**
   * 详情
   *
   * @param $ids
   * @return string
   * @throws \think\Exception
   * @throws \think\exception\DbException
   */
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
