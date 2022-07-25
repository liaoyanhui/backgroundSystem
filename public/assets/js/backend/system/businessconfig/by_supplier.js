/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-20 11:19:45
 * @LastEditTime: 2022-07-15 16:20:57
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/by_supplier.js
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'system/businessconfig/by_supplier/index' + location.search,
          add_url: 'system/businessconfig/by_supplier/add',
          edit_url: 'system/businessconfig/by_supplier/edit',
          del_url: 'system/businessconfig/by_supplier/del',
          multi_url: 'system/businessconfig/by_supplier/multi',
          import_url: 'system/businessconfig/by_supplier/import',
          table: 'supplier',
        }
      });

      var table = $("#table");

      // 初始化表格
      table.bootstrapTable({
        url: $.fn.bootstrapTable.defaults.extend.index_url,
        pk: 'id',
        sortName: 'id',
        fixedColumns: true,
        fixedRightNumber: 1,
        search: false,
        columns: [
          [
            { checkbox: true },
            { field: 'id', title: 'ID' },
            { field: 'name', title: '供应商', operate: 'LIKE' },
            { field: 'addr', title: '详细地址' },
            // 1小规模纳税人 2一般纳税人 3个体工商户'
            {
              field: 'property_id', title: '公司属性', searchable: false, formatter: (i) => {
                switch (i) {
                  case 1:
                    return '小规模纳税人';
                  case 2:
                    return '一般纳税人';
                  case 3:
                    return '个体工商户';
                  default:
                    return '--';
                }
              }
            },
            { field: 'operate_name', title: '经营名称' },
            { field: 'operate_category_list', title: '经营范围', searchable: false },
            { field: 'contact_name', title: '联系人', searchable: false },
            // { field: 'salesman_name', title: '业务员', searchable: false },
            // { field: 'contact_way', title: '联系方式', searchable: false },
            {
              field: 'operate', title: __('Operate'), table: table,
              buttons: [{
                name: 'settlementradio',
                title: '查看',
                icon: 'fa fa-list',
                classname: 'btn btn-warning btn-xs addtabsit',
                url: 'system/businessconfig/by_supplier/supplier_tab'
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
    salesman_add: function () {
      Controller.api.bindevent();
    },
    edit: function () {
      Controller.api.bindevent();
    },
    salesman_edit: function () {
      Controller.api.bindevent();
    },
    supplier_tab: function () {
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
        var sbSettlementRatio = $("#sbSettlementRatio");
        sbSettlementRatio.bootstrapTable({
          url: 'system/businessconfig/by_supplier/sbSettlementRatio/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar2',
          pk: 'target_id',
          sortName: 'updated_at',
          extend: {
            table: 'business_tab',
          },
          // search: false,
          commonSearch: false,
          search: false,
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: 'ID' },
              // { field: 'target_id', title: '供应商ID' },
              { field: 'target_name', title: '供应商' },
              { field: 'business_name', title: '业务公司' },
              { field: 'platform_name', title: '平台' },
              {
                field: 'settlement_type', title: '结算方式', formatter: (i, c) => {
                  switch (i) {
                    case 1:
                      return '订单融';
                    case 2:
                      return '月结';
                    case 3:
                      return '周结算';
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
              // {
              //   field: 'operate', title: __('Operate'), table: sbSettlementRatio,
              //   events: Table.api.events.operate, formatter: Table.api.formatter.operate
              // }
            ]
          ]
        });
        Table.api.bindevent(sbSettlementRatio);
      },
      third: function () {
        var businessSalesman = $("#businessSalesman");
        businessSalesman.bootstrapTable({
          url: 'system/businessconfig/by_supplier/businessSalesman/supplier_id/' + Fast.api.query("ids"),
          toolbar: '#toolbar3',
          pk: 'id',
          sortName: 'updated_at',
          extend: {
            add_url: 'system/businessconfig/by_supplier/salesman_add/supplier_id/' + Fast.api.query("ids"),
            del_url: 'system/businessconfig/by_supplier/salesman_del',
            edit_url: 'system/businessconfig/by_supplier/salesman_edit/supplier_id/' + Fast.api.query("ids"),
            table: 'business_tab',
          },
          // search: false,
          commonSearch: false,
          search: false,
          columns: [
            [
              // { checkbox: true },
              { field: 'id', title: 'ID' },
              { field: 'supplier_name', title: '供应商' },
              { field: 'salesman_name', title: '业务员' },
              { field: 'salesman_way', title: '联系方式(业务员)' },
              { field: 'business_company_name', title: '业务公司' },
              {
                field: 'operate', title: __('Operate'), table: businessSalesman,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(businessSalesman);
      }
    },
    api: {
      bindevent: function () {
        $("#c-city_id").data("params", function (obj) {
          return { province_id: $("#c-province_id").val() };
        });

        $("#c-district_id").data("params", function (obj) {
          return { city_id: $("#c-city_id").val() };
        });

        // 业务员对应的业务公司
        $('#c-business_company').val($('#c-property_type option:selected').data('businessname'))
        $(document).on('change', '#c-property_type', (e) => {
          $('#c-business_company').val($('#c-property_type option:selected').data('businessname'))
        })
        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
