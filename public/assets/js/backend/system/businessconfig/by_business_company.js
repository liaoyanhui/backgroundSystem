/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-17 09:38:21
 * @LastEditTime: 2022-07-14 17:55:43
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/by_business_company.js
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init(
        {
          extend: {
            index_url: 'system/businessconfig/by_business_company/index' + location.search,
            add_url: 'system/businessconfig/by_business_company/add',
            edit_url: 'system/businessconfig/by_business_company/edit',
            del_url: 'system/businessconfig/by_business_company/del',
            multi_url: 'system/businessconfig/by_business_company/multi',
            import_url: 'system/businessconfig/by_business_company/import',
            table: 'business_company',
          }
        }
      );
      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        fixedColumns: true,
        fixedRightNumber: 1,
        columns: [
          [
            { checkbox: true },
            { field: 'id', title: 'ID' },
            { field: 'name', title: '业务公司', operate: 'LIKE' },
            { field: 'province_id', title: __('Province_id'), visible: false, searchable: false },
            { field: 'province_cname', title: '省份', visible: false, searchable: false },
            { field: 'addr', title: '详细地址', visible: false, searchable: false },
            { field: 'contact_name', title: '联系人', searchable: false },
            { field: 'contact_way', title: '联系方式', searchable: false },
            // { field: 'deleted_at', title: __('Deleted_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            // { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            // { field: 'updated_at', title: __('Updated_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            { field: 'remark', title: __('Remark'), searchable: false },
            {
              field: 'operate', title: __('Operate'), table: table,
              buttons: [{
                name: 'addtabs',
                title: '查看',
                classname: 'btn btn-xs btn-warning btn-addtabs',
                icon: 'fa fa-list',
                url: 'system/businessconfig/by_business_company/business_tab'
              }],
              events: Table.api.events.operate, formatter: Table.api.formatter.operate
            }
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
    b_s_settlement_ratio_add: function () {
      // 监听结算方式选择
      // $('#c_ratio').show();
      // $('#c-settlement_date').hide();

      // $(document).on('change', '#c-settlement_type', function (e) {
      //   if ($('#c-settlement_type').val() == '2') {
      //     $('#c_ratio').hide();
      //     $('#c-settlement_date').show();
      //   } else {
      //     $('#c_ratio').show();
      //     $('#c-settlement_date').hide();
      //   }
      // })
      Controller.api.bindevent();
    },
    b_s_settlement_ratio_edit: function () {
      // if ($('#c-settlement_type').val() == '2') {
      //   $('#c_ratio').hide();
      //   $('#c-settlement_date').show();
      // } else {
      //   $('#c_ratio').show();
      //   $('#c-settlement_date').hide();
      // }

      // $(document).on('change', '#c-settlement_type', function (e) {
      //   if ($('#c-settlement_type').val() == '2') {
      //     $('#c_ratio').hide();
      //     $('#c-settlement_date').show();
      //   } else {
      //     $('#c_ratio').show();
      //     $('#c-settlement_date').hide();
      //   }
      // })
      Controller.api.bindevent();
    },
    department_add: function () {
      Controller.api.bindevent();
    },
    department_del: function () {
      Controller.api.bindevent();
    },
    department_edit: function () {
      Controller.api.bindevent();
    },
    salesman_add: function () {
      Controller.api.bindevent();
    },
    salesman_del: function () {
      Controller.api.bindevent();
    },
    salesman_edit: function () {
      Controller.api.bindevent();
    },
    business_tab: function () {
      Table.api.init();

      //绑定事件
      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var panel = $($(this).attr("href"));
        if (panel.size() > 0) {
          Controller.table[panel.attr("id")] && Controller.table[panel.attr("id")].call(this);
          $(this).on('click', function (e) {
            $($(this).attr("href")).find(".btn-refresh").trigger("click");
          });
        }
        //移除绑定的事件
        $(this).unbind('shown.bs.tab');
      });

      //必须默认触发shown.bs.tab事件
      $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

    },
    table: {
      second: function () {
        var bsSettlementRatio = $("#bsSettlementRatio");
        bsSettlementRatio.bootstrapTable({
          url: 'system/businessconfig/by_business_company/bsSettlementRatio/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar2',
          pk: 'id',
          sortName: 'updated_at',
          commonSearch: false,
          search: false,
          extend: {
            // index_url: 'system/businessconfig/by_business_company/platform_tab' + location.search,
            add_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_add/business_id/' + Fast.api.query('ids'),
            edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            // multi_url: 'system/businessconfig/by_business_company/multi',
            // import_url: 'system/businessconfig/by_business_company/import',
            table: 'business_tab',
          },
          // search: false,
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: 'ID' },
              // { field: 'target_id', title: '供应商ID' },
              { field: 'business_name', title: '业务公司' },
              { field: 'target_name', title: '供应商' },
              { field: 'platform_name', title: '平台' },
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

              {
                field: 'settlement_ratio', title: '结算比例(%)'
              },
              { field: 'contact_name', title: '联系人' },
              { field: 'contact_way', title: '联系方式' },
              // { field: 'settlement_date', title: '结算日期' },
              {
                field: 'operate', title: __('Operate'), table: bsSettlementRatio,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(bsSettlementRatio);
      },
      third: function () {
        var bcSettlementRatio = $("#bcSettlementRatio");
        bcSettlementRatio.bootstrapTable({
          url: 'system/businessconfig/by_business_company/bcSettlementRatio/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar3',
          pk: 'target_id',
          sortName: 'updated_at',
          extend: {
            table: 'business_tab',
          },
          commonSearch: false,
          search: false,
          columns: [
            [
              // { checkbox: true },
              { field: 'id', title: 'ID' },
              { field: 'target_name', title: '业务公司' },
              { field: 'related_name', title: '签约单位' },
              { field: 'platform_name', title: '平台' },
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
              { field: 'settlement_ratio', title: '结算比例(%)' },
              { field: 'contact_name', title: '联系人' },
              { field: 'contact_way', title: '联系方式' },
              // { field: 'settlement_date', title: '结算日期' },
              // {
              //   field: 'operate', title: __('Operate'), table: bcSettlementRatio,
              //   events: Table.api.events.operate, formatter: Table.api.formatter.operate
              // }
            ]
          ]
        });
        Table.api.bindevent(bcSettlementRatio);
      },
      fourth: function () {
        var department = $("#department");
        department.bootstrapTable({
          url: 'system/businessconfig/by_business_company/department/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar4',
          pk: 'id',
          extend: {
            add_url: 'system/businessconfig/by_business_company/department_add/business_id/' + Fast.api.query('ids'),
            edit_url: 'system/businessconfig/by_business_company/department_edit/business_id/' + Fast.api.query('ids'),
            del_url: 'system/businessconfig/by_business_company/department_del/business_id/' + Fast.api.query('ids'),
            table: 'business_tab',
          },
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: 'ID' },
              { field: 'name', title: '部门' },
              { field: 'manager', title: '主管' },
              { field: 'contact_info', title: '联系方式' },
              { field: 'remark', title: '备注' },
              {
                field: 'operate', title: __('Operate'), table: department,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(department);
      },
      fifth: function () {
        var salesman = $("#salesman");
        salesman.bootstrapTable({
          url: 'system/businessconfig/by_business_company/salesman/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar5',
          pk: 'id',
          extend: {
            add_url: 'system/businessconfig/by_business_company/salesman_add/business_id/' + Fast.api.query('ids'),
            edit_url: 'system/businessconfig/by_business_company/salesman_edit/business_id/' + Fast.api.query('ids'),
            del_url: 'system/businessconfig/by_business_company/salesman_del/business_id/' + Fast.api.query('ids'),
            table: 'business_tab',
          },
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: 'ID' },
              { field: 'name', title: '业务员' },
              { field: 'department_name', title: '部门', searchable: false },
              { field: 'contact_way', title: '联系方式' },
              { field: 'remark', title: '备注' },
              {
                field: 'operate', title: __('Operate'), table: salesman,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(salesman);
      }
    },
    api: {
      bindevent: function () {
        $(document).on("change", "#c-province_id", function (data) {
          $('#c-province_cname').val($('#c-province_id').selectPageText());
        });

        let supplier_id = $('#c-supplier_id').val();
        $(document).on('change', '#c-supplier_id', (e) => {
          supplier_id = $('#c-supplier_id').val();
          $("#c-platform_id").selectPageClear('');
        })
        $("#c-platform_id").data("params", function () {
          return { custom: { supplier_id } };
        });
        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
