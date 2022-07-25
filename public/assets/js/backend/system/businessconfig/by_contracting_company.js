/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-06 14:45:53
 * @LastEditTime: 2022-07-15 16:07:28
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/by_contracting_company.js
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'system/businessconfig/by_contracting_company/index' + location.search,
          add_url: 'system/businessconfig/by_contracting_company/add',
          edit_url: 'system/businessconfig/by_contracting_company/edit',
          del_url: 'system/businessconfig/by_contracting_company/del',
          multi_url: 'system/businessconfig/by_contracting_company/multi',
          import_url: 'system/businessconfig/by_contracting_company/import',
          table: 'contracting_company',
        }
      });

      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        // sortOrder: 'asc',
        columns: [
          [
            { checkbox: true },
            { field: 'id', title: 'ID' },
            { field: 'name', title: '签约单位', operate: 'LIKE' },
            { field: 'addr', title: '详细地址' },
            { field: 'contact_name', title: '联系人', searchable: false },
            { field: 'contact_way', title: '联系方式', searchable: false },
            {
              field: 'operate', title: __('Operate'), table: table,
              buttons: [{
                name: 'settlementradio',
                title: '查看',
                icon: 'fa fa-list',
                classname: 'btn btn-warning btn-xs addtabsit',
                url: 'system/businessconfig/by_contracting_company/contracting_tab'
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
    c_b_settlement_ratio_add: function () {
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
    c_b_settlement_ratio_edit: function () {
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
    contracting_tab: function () {
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
        var cbSettlementRatio = $("#cbSettlementRatio");
        cbSettlementRatio.bootstrapTable({
          url: 'system/businessconfig/by_contracting_company/cbSettlementRatio/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar1',
          pk: 'id',
          sortName: 'updated_at',
          commonSearch: false,
          search: false,
          extend: {
            add_url: 'system/businessconfig/by_contracting_company/c_b_settlement_ratio_add/contracting_id/' + Fast.api.query('ids'),
            edit_url: 'system/businessconfig/by_contracting_company/c_b_settlement_ratio_edit/contracting_id/' + Fast.api.query('ids'),
            del_url: 'system/businessconfig/by_contracting_company/c_b_settlement_ratio_del/contracting_id/' + Fast.api.query('ids'),
            table: 'contracting_tab',
          },
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: 'ID' },
              // { field: 'target_id', title: '业务公司ID' },
              { field: 'contracting_name', title: '签约单位' },
              { field: 'target_name', title: '业务公司' },
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
                field: 'operate', title: __('Operate'), table: cbSettlementRatio,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(cbSettlementRatio);
      },
      third: function () {
        var cpSettlementRatio = $("#cpSettlementRatio");
        cpSettlementRatio.bootstrapTable({
          url: 'system/businessconfig/by_contracting_company/cpSettlementRatio/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar2',
          pk: 'target_id',
          sortName: 'updated_at',
          commonSearch: false,
          search: false,
          extend: {
            // index_url: 'system/businessconfig/by_business_company/platform_tab' + location.search,
            // add_url: 'system/businessconfig/by_business_company/b_c_settlement_ratio_add/business_id/' + Fast.api.query('ids'),
            // edit_url: 'system/businessconfig/by_business_company/b_c_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_c_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            // multi_url: 'system/businessconfig/by_business_company/multi',
            // import_url: 'system/businessconfig/by_business_company/import',
            table: 'business_tab',
          },
          // search: false,
          columns: [
            [
              // { checkbox: true },
              // { field: 'target_id', title: '签约单ID' },
              { field: 'target_name', title: '签约单位' },
              { field: 'related_name', title: '平台' },
              // { field: 'target_name', title: '供应商' },
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
              { field: 'provinces', title: '签约省份及结算比例(%)' },
              // {
              //   field: 'operate', title: __('Operate'), table: cpSettlementRatio,
              //   events: Table.api.events.operate, formatter: Table.api.formatter.operate
              // }
            ]
          ]
        });
        Table.api.bindevent(cpSettlementRatio);
      }
    },
    api: {
      bindevent: function () {
        // 添加或者编辑结算比例 选中平台 后 传入业务公司的平台id
        let business_company_id = $('#c-business_company_id').val();
        $(document).on('change', '#c-business_company_id', (e) => {
          business_company_id = $('#c-business_company_id').val();
          $("#c-platform_id").selectPageClear('');
        })
        $("#c-platform_id").data("params", function () {
          return { custom: { business_company_id } };;
        });

        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
