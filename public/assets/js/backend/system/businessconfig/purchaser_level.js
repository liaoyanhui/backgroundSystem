/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-13 14:57:04
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-14 17:36:23
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/purchaser_level.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'system/businessconfig/purchaser_level/index' + location.search,
          add_url: 'system/businessconfig/purchaser_level/add',
          edit_url: 'system/businessconfig/purchaser_level/edit',
          del_url: 'system/businessconfig/purchaser_level/del',
          multi_url: 'system/businessconfig/purchaser_level/multi',
          import_url: 'system/businessconfig/purchaser_level/import',
          table: 'purchaser_level',
        }
      });

      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        columns: [
          [
            { checkbox: true },
            { field: 'id', title: __('Id') },
            { field: 'name', title: __('Name'), operate: 'LIKE' },
            // {field: 'parent_id', title: __('Parent_id')},
            { field: 'level', title: __('Level') },
            { field: 'eshortname', title: __('Eshortname') },
            // {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
            // {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, cellStyle: { css: { 'white-space': 'nowrap' } } }
          ]
        ]
      });

      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    add: function () {
      Controller.api.bindevent();
    },
    edit: function () {
      Controller.api.bindevent();
    },
    add2: function () {
      Controller.api.bindevent();
    },
    edit2: function () {
      Controller.api.bindevent();
    },
    detail: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'system/businessconfig/purchaser_level/detail/ids/' + Config.ids + location.search,
          add_url: 'system/businessconfig/purchaser_level/add2/ids/' + Fast.api.query('ids'),
          edit_url: 'system/businessconfig/purchaser_level/edit2',
          del_url: 'system/businessconfig/purchaser_level/del',
          multi_url: 'system/businessconfig/purchaser_level/multi',
          import_url: 'system/businessconfig/purchaser_level/import',
          table: 'purchaser_level_detail',
        }
      });

      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        columns: [
          [
            { checkbox: true },
            { field: 'id', title: __('Id') },
            { field: 'name', title: __('Name'), operate: 'LIKE' },
            {
              field: 'level_text', title: __('Level'), operate: 'LIKE'
            },
            { field: 'parent_name', title: __('Parent_id') },
            // { field: 'eshortname', title: __('Eshortname') },
            // {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
            // {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, cellStyle: { css: { 'white-space': 'nowrap' } } }
          ]
        ]
      });

      // 为表格绑定事件
      Table.api.bindevent(table);
    },
    api: {
      bindevent: function () {
        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
