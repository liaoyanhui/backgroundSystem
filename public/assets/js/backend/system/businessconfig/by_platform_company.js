/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-15 10:01:19
 * @LastEditTime: 2022-07-14 17:43:16
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/by_platform_company.js
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'system/businessconfig/by_platform_company/index' + location.search,
          add_url: 'system/businessconfig/by_platform_company/add',
          edit_url: 'system/businessconfig/by_platform_company/edit',
          del_url: 'system/businessconfig/by_platform_company/del',
          multi_url: 'system/businessconfig/by_platform_company/multi',
          import_url: 'system/businessconfig/by_platform_company/import',
          table: 'platform_company',
        }
      });

      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        columns: [
          { checkbox: true },
          { field: 'id', title: 'ID', searchable: false },
          { field: 'name', title: '平台', operate: 'LIKE' },
          { field: 'contractCount', title: '签约单位数', operate: 'LIKE', searchable: false },
          { field: 'intro', title: '介绍', visible: false, searchable: false },
          { field: 'remark', title: '备注', searchable: false },
          // { field: 'deleted_at', title: __('Deleted_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
          // { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
          // { field: 'updated_at', title: __('Updated_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
          {
            field: 'operate', title: __('Operate'), table: table,
            buttons: [{
              name: 'addtabs',
              title: '查看',
              classname: 'btn btn-xs btn-warning btn-addtabs',
              icon: 'fa fa-list',
              // url: 'system/businessconfig/by_platform_company/platformTab'
              // url: 'system/businessconfig/by_platform_company/platformTab'
              url: 'system/businessconfig/by_platform_company/platform_tab'
            }],
            events: Table.api.events.operate,
            formatter: Table.api.formatter.operate
          }
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
    settlement_ratio_add: function () {
      Controller.api.bindevent();
    },
    settlement_ratio_edit: function () {
      Controller.api.bindevent();
    },
    platform_tab: function () {
      Table.api.init({
        extend: {
          // index_url: 'system/businessconfig/by_platform_company/platform_tab' + location.search,
          add_url: 'system/businessconfig/by_platform_company/settlement_ratio_add/platform_id/' + Fast.api.query('ids'),
          edit_url: 'system/businessconfig/by_platform_company/settlement_ratio_edit/platform_id/' + Fast.api.query('ids'),
          del_url: 'system/businessconfig/by_platform_company/settlement_ratio_del/platform_id/' + Fast.api.query('ids'),
          // multi_url: 'system/businessconfig/by_platform_company/multi',
          // import_url: 'system/businessconfig/by_platform_company/import',
          table: 'platform_tab',
        },
      });
      var table2 = $("#table2");
      table2.bootstrapTable({
        url: 'system/businessconfig/by_platform_company/settlement_ratio/ids/' + Fast.api.query("ids"),
        toolbar: '#toolbar2',
        pk: 'target_id',
        sortName: 'updated_at',
        search: false,
        commonSearch: false,
        columns: [
          [
            { checkbox: true },
            { field: 'target_id', title: 'ID(签约单位)', searchable: false },
            // { field: 'id', title: 'ID' },
            { field: 'platform_name', title: '平台' },
            { field: 'target_name', title: '签约单位' },
            {
              field: 'settlement_type', title: '结算方式', formatter: (i, c) => {
                switch (i) {
                  case 1:
                    return '订单融';
                  case 2:
                    return '月结';
                  case 3:
                    return '周结';
                  case 4:
                    return '背靠背';
                  default:
                    return '--'
                }
              }
            },
            {
              field: 'invoice_type', title: '发票类型', formatter: (i) => {
                switch (i) {
                  case 1:
                    return '普票';
                  case 3:
                    return '专票';
                  default:
                    return '--'
                }
              }
            },
            { field: 'provinces', title: '签约省份及结算比例(%)', searchable: false },
            { field: 'contact_name', title: '联系人' },
            { field: 'contact_way', title: '联系方式' },
            {
              field: 'operate', title: __('Operate'), table: table2,
              events: Table.api.events.operate, formatter: Table.api.formatter.operate
            }
          ]
        ]
      });

      // 为表格2绑定事件
      Table.api.bindevent(table2);
    },
    api: {
      bindevent: function () {
        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
