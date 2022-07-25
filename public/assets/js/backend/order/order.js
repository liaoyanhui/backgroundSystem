/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-21 10:06:58
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-18 14:12:15
 * @FilePath: /baiying/public/assets/js/backend/order/order.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
  let auditTable = Object.assign({ ...Table });
  let sendTable = Object.assign({ ...Table });
  let sentTable = Object.assign({ ...Table });
  let completeTable = Object.assign({ ...Table });
  let Controller = {
    index: function () {
      auditTable.api.init({
        extend: {
          index_url: 'order/order/index/ids/1' + location.search,
          add_url: 'order/order/add',
          edit_url: 'order/order/edit',
          del_url: 'order/order/del',
          multi_url: 'order/order/multi',
          import_url: 'order/order/import',
          table: 'audit',
        }
      });
      sendTable.api.init({
        extend: {
          index_url: 'order/order/index/ids/2' + location.search,
          multi_url: 'order/order/multi',
          import_url: 'order/order/import',
          table: 'send',
        }
      });
      sentTable.api.init({
        extend: {
          index_url: 'order/order/index/ids/3' + location.search,
          multi_url: 'order/order/multi',
          import_url: 'order/order/import',
          table: 'sent',
        }
      });
      completeTable.api.init({
        extend: {
          index_url: 'order/order/index/ids/4' + location.search,
          multi_url: 'order/order/multi',
          import_url: 'order/order/import',
          table: 'complete',
        }
      });

      //绑定事件
      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let panel = $($(this).attr("href"));
        if (panel.size() > 0) {
          Controller.table[panel.attr("id")].call(this);
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
      first: function () {

        let table = $("#audit");

        // 初始化表格
        table.bootstrapTable({
          url: 'order/order/index/ids/1' + location.search,
          pk: 'id',
          sortName: 'id',
          fixedColumns: true,
          fixedRightNumber: 1,
          toolbar: '#toolbar1',
          extend: {
            index_url: 'order/order/index/ids/1' + location.search,
            add_url: 'order/order/add',
            edit_url: 'order/order/edit',
            del_url: 'order/order/del',
            multi_url: 'order/order/multi',
            import_url: 'order/order/import',
            table: 'audit',
          },
          columns: [
            [
              { checkbox: true },
              { field: 'order_no', title: __('Order_no'), operate: 'LIKE' },
              { field: 'purchaser_name', title: __('Purchaser_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_province', title: __('Project_province'), cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'category_name', title: __('Category_name') },
              { field: 'platform_company_name', title: __('Platform_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'contracting_company_name', title: __('Contracting_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'business_company_name', title: __('Business_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'salesman_name', title: __('Salesman_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'deliver_way', title: __('Deliver_way'), formatter: () => {
                  return '货运'
                }
              },
              { field: 'amount', title: __('Amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
              { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'audit_at', title: __('Audit_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'deliver_at', title: __('Deliver_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'arrived_at', title: __('Arrived_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'operate', title: __('Operate'), table: table, events: auditTable.api.events.operate, formatter: auditTable.api.formatter.operate, buttons: [
                  // {
                  //     name: 'addtabs',
                  //     title: '详情',
                  //     classname: 'btn btn-xs btn-warning btn-addtabs',
                  //     icon: 'fa fa-folder-o',
                  //     url: 'order/order/detail'
                  // },
                  {
                    name: 'audit',
                    title: __('审核'),
                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                    icon: 'fa fa-magic',
                    confirm: '采购订单信息是否确认无误！',
                    url: 'order/order/audit',
                    success: function () {
                      table.bootstrapTable('refresh', {});
                    },
                    error: function () { }
                  },
                ], cellStyle: { css: { 'white-space': 'nowrap' } }
              }
            ]
          ]
        });

        // 为表格绑定事件
        auditTable.api.bindevent(table);
      },
      second: function () {
        let table = $("#send");

        // 初始化表格
        table.bootstrapTable({
          url: 'order/order/index/ids/2' + location.search,
          pk: 'id',
          sortName: 'id',
          fixedColumns: true,
          fixedRightNumber: 1,
          toolbar: '#toolbar2',
          columns: [
            [
              { checkbox: true },
              { field: 'order_no', title: __('Order_no'), operate: 'LIKE' },
              { field: 'purchaser_name', title: __('Purchaser_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_province', title: __('Project_province'), cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'category_name', title: __('Category_name') },
              { field: 'platform_company_name', title: __('Platform_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'contracting_company_name', title: __('Contracting_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'business_company_name', title: __('Business_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'salesman_name', title: __('Salesman_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'deliver_way', title: __('Deliver_way'), formatter: () => {
                  return '货运'
                }
              },
              { field: 'amount', title: __('Amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
              { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'audit_at', title: __('Audit_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'deliver_at', title: __('Deliver_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'arrived_at', title: __('Arrived_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'operate', title: __('Operate'), table: table, events: auditTable.api.events.operate, formatter: auditTable.api.formatter.operate, buttons: [
                  // {
                  //     name: 'addtabs',
                  //     title: '详情',
                  //     classname: 'btn btn-xs btn-warning btn-addtabs',
                  //     icon: 'fa fa-folder-o',
                  //     url: 'order/order/detail'
                  // },
                  {
                    name: 'send',
                    title: __('确认发货'),
                    classname: 'btn btn-xs btn-primary btn-dialog',
                    icon: 'fa fa-magic',
                    url: 'order/order/send',
                    callback: function (data) {
                      Layer.alert("接收到回传数据：" + JSON.stringify(data), { title: "回传数据" });
                    }
                  },
                ]
              }
            ]
          ]
        });

        // 为表格绑定事件
        sendTable.api.bindevent(table);
      },
      three: function () {


        let table = $("#sent");

        // 初始化表格
        table.bootstrapTable({
          url: 'order/order/index/ids/3' + location.search,
          pk: 'id',
          sortName: 'id',
          fixedColumns: true,
          fixedRightNumber: 1,
          toolbar: '#toolbar3',
          columns: [
            [
              { checkbox: true },
              { field: 'order_no', title: __('Order_no'), operate: 'LIKE' },
              { field: 'purchaser_name', title: __('Purchaser_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_province', title: __('Project_province'), cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'category_name', title: __('Category_name') },
              { field: 'platform_company_name', title: __('Platform_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'contracting_company_name', title: __('Contracting_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'business_company_name', title: __('Business_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'salesman_name', title: __('Salesman_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'deliver_way_text', title: __('Deliver_way'), operate: 'LIKE'
              },
              { field: 'amount', title: __('Amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
              { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'audit_at', title: __('Audit_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'deliver_at', title: __('Deliver_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'arrived_at', title: __('Arrived_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'operate', title: __('Operate'), table: table, events: auditTable.api.events.operate, formatter: auditTable.api.formatter.operate, buttons: [
                  // {
                  //     name: 'addtabs',
                  //     title: '详情',
                  //     classname: 'btn btn-xs btn-warning btn-addtabs',
                  //     icon: 'fa fa-folder-o',
                  //     url: 'order/order/detail'
                  // },
                  {
                    name: 'arrived',
                    title: __('确认收货'),
                    classname: 'btn btn-xs btn-primary btn-dialog',
                    icon: 'fa fa-magic',
                    url: 'order/order/arrived',
                    callback: function (data) {
                      Layer.alert("接收到回传数据：" + JSON.stringify(data), { title: "回传数据" });
                    }
                  },
                ]
              }
            ]
          ]
        });

        // 为表格绑定事件
        sentTable.api.bindevent(table);
      },
      four: function () {
        let table = $("#complete");

        // 初始化表格
        table.bootstrapTable({
          url: 'order/order/index/ids/4' + location.search,
          pk: 'id',
          sortName: 'id',
          fixedColumns: true,
          fixedRightNumber: 1,
          toolbar: '#toolbar4',
          columns: [
            [
              { checkbox: true },
              { field: 'order_no', title: __('Order_no'), operate: 'LIKE' },
              { field: 'purchaser_name', title: __('Purchaser_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'project_province', title: __('Project_province'), cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'category_name', title: __('Category_name') },
              { field: 'platform_company_name', title: __('Platform_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'contracting_company_name', title: __('Contracting_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'business_company_name', title: __('Business_company_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'salesman_name', title: __('Salesman_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'deliver_way', title: __('Deliver_way'), formatter: () => {
                  return '货运'
                }
              },
              { field: 'amount', title: __('Amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
              { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'audit_at', title: __('Audit_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'deliver_at', title: __('Deliver_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              { field: 'arrived_at', title: __('Arrived_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, cellStyle: { css: { 'white-space': 'nowrap' } } },
              {
                field: 'operate', title: __('Operate'), table: table, events: auditTable.api.events.operate, formatter: auditTable.api.formatter.operate, buttons: [
                  // {
                  //   name: 'addtabs',
                  //   title: '详情',
                  //   classname: 'btn btn-xs btn-warning btn-addtabs',
                  //   icon: 'fa fa-folder-o',
                  //   url: 'order/order/detail'
                  // },
                ]
              }
            ]
          ]
        });

        // 为表格绑定事件
        completeTable.api.bindevent(table);
      }
    },
    add: function () {
      Controller.api.bindevent();
    },
    edit: function () {
      Controller.api.bindevent();
    },
    audit: function () {
      Controller.api.bindevent();
    },
    send: function () {
      Controller.api.bindevent();
    },
    arrived: function () {
      // 获取上传类别
      $("#faupload-third,#faupload-third-chunking").data("category", function (file) {
        return $("#category-third").val();
      });
      // 获取上传类别
      $("#faupload-local,#faupload-local-chunking").data("category", function (file) {
        return $("#category-local").val();
      });
      Controller.api.bindevent();
    },
    api: {
      bindevent: function () {
        let province_id = null;
        let related_id = null;
        let platform_id = null;
        let contracting_related_id = null
        let business_related_id = null;
        let category_id = null;
        $("#c-project_id").data("eSelect", function (e) {
          $('#c-purchaser_id').val(e.purchaser_fullname)
          province_id = e.province_id;

          $("#c-platform_company_id").selectPageClear();
          $("#c-contracting_company_id").selectPageClear('');
          $("#c-business_company_id").selectPageClear('');
          $("#c-supplier_id").selectPageClear('');
        });

        $("#c-platform_company_id").data("eSelect", function (e) {
          related_id = e.id;
          platform_id = e.id;

          $("#c-contracting_company_id").selectPageClear('');
          $("#c-business_company_id").selectPageClear('');
          $("#c-supplier_id").selectPageClear('');
        });

        $("#c-contracting_company_id").data("eSelect", function (e) {
          contracting_related_id = e.id;
          $("#c-business_company_id").selectPageClear('');
          $("#c-supplier_id").selectPageClear('');
        });

        $("#c-business_company_id").data("eSelect", function (e) {
          business_related_id = e.id;
          $("#c-supplier_id").selectPageClear('');
        });

        $("#c-category_id").data("eSelect", function (e) {
          category_id = e.id;
          $("#c-supplier_id").selectPageClear('');
        });

        $("#c-contracting_company_id").data("params", function () {
          const edit_province_id = $('#c-contracting_company_id_text').attr('data-province-id');
          const edit_related_id = $('#c-contracting_company_id_text').attr('data-related-id');
          return { custom: province_id ? { province_id, related_id } : { province_id: edit_province_id, related_id: edit_related_id } };;
        });

        $("#c-business_company_id").data("params", function () {
          const edit_platform_id = $('#c-business_company_id_text').attr('data-platform-id');
          const edit_related_id = $('#c-business_company_id_text').attr('data-related-id');
          return { custom: platform_id ? { platform_id, related_id: contracting_related_id } : { platform_id: edit_platform_id, related_id: edit_related_id } };;
        });

        $("#c-supplier_id").data("params", function () {
          const edit_platform_id = $('#c-supplier_id_text').attr('data-platform-id');
          const edit_related_id = $('#c-supplier_id_text').attr('data-related-id');
          const edit_category_id = $('#c-supplier_id_text').attr('data-category-id');
          return { custom: platform_id ? { platform_id, related_id: business_related_id, category_id } : { platform_id: edit_platform_id, related_id: edit_related_id, category_id: edit_category_id } };;
        });






        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
