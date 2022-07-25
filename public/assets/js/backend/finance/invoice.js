/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-29 09:49:57
 * @LastEditTime: 2022-07-18 15:00:32
 * @FilePath: /baiying/public/assets/js/backend/finance/invoice.js
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {

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
    invoice_detail_out: function () {
      Table.api.init();
      Controller.api.bindevent();
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
    invoice_detail_in: function () {
      Table.api.init();
      Controller.api.bindevent();
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
    add_invoice_record: function () {
      Table.api.init();
      Controller.api.bindevent();
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
      first: function () {
        var output = $("#output");
        output.bootstrapTable({
          url: 'finance/invoice/Output/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar',
          pk: 'id',
          sortName: 'id',
          commonSearch: false,
          search: false,
          extend: {
            del_url: 'finance/invoice/output_del',
            table: 'invoice_tab',
          },
          // search: false,
          columns: [
            [
              // { checkbox: true },  
              { field: 'invoice_no', title: '发票单' },
              { field: 'from_name', title: '付款方' },
              {
                field: 'amount', title: '应开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              {
                field: 'finished_amount', title: '已开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              // { field: 'last_at', title: '上次录入时间', operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
              {
                field: 'status', title: '状态', formatter: (i) => {
                  switch (i) {
                    case 1:
                      return '待开票';
                    case 2:
                      return '部分开票';
                    case 3:
                      return '已完成';
                    default:
                      return '--'
                  }
                }
              },
              {
                field: 'operate', title: __('Operate'),
                buttons: [{
                  name: 'check',
                  title: '查看',
                  classname: 'btn btn-xs btn-warning btn-addtabs',
                  icon: 'fa fa-list',
                  url: 'finance/invoice/invoice_detail_out'
                }], table: output,
                events: Table.api.events.operate, formatter: function (value, row, index) {
                  var that = $.extend({}, this);
                  var table = $(that.table).clone(true);
                  if (row.status != 1) {
                    $(table).data("operate-del", null);
                  }
                  that.table = table;
                  return Table.api.formatter.operate.call(that, value, row, index);
                }
              }
            ]
          ]
        });
        Table.api.bindevent(output);
      },
      second: function () {
        var income = $('#income');
        income.bootstrapTable({
          url: 'finance/invoice/Income/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar2',
          pk: 'id',
          sortName: 'id',
          commonSearch: false,
          search: false,
          extend: {
            // del_url: 'finance/invoice/income_del',
            // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            table: 'invoice_tab',
          },
          // search: false,
          columns: [
            [
              // { checkbox: true },
              { field: 'invoice_no', title: '发票单' },
              { field: 'to_name', title: '收款方' },
              {
                field: 'amount', title: '应开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              {
                field: 'finished_amount', title: '已开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              // { field: 'last_at', title: '上次录入时间', operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
              {
                field: 'status', title: '状态', formatter: (i) => {
                  switch (i) {
                    case 1:
                      return '待开票';
                    case 2:
                      return '部分开票';
                    case 3:
                      return '已完成';
                    default:
                      return '--'
                  }
                }
              },
              {
                field: 'operate', title: __('Operate'),
                buttons: [{
                  name: 'addtabs',
                  title: '查看',
                  classname: 'btn btn-xs btn-warning btn-addtabs',
                  icon: 'fa fa-list',
                  url: 'finance/invoice/invoice_detail_in'
                }],
                table: income,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });
        Table.api.bindevent(income);
      },
      third: function () {
        var outSubOrder = $('#outSubOrder');
        outSubOrder.bootstrapTable({
          url: 'finance/invoice/outSubOrder/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar3',
          pk: 'id',
          sortName: 'id',
          pagination: false,
          commonSearch: false,
          search: false,
          extend: {
            // del_url: 'finance/invoice/income_del',
            // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            table: 'invoice_detail_out',
          },
          // search: false,
          columns: [
            [

              // { checkbox: true },
              { field: 'sub_order_no', title: '订单编号' },
              { field: 'project_name', title: '项目' },
              {
                field: 'purchaser_name', title: '采购', formatter: (i, c) => {
                  return i + c.platform_company_name + c.category_name
                }
              },
              {
                field: 'order_amount', title: '应开票金额', searchable: false, formatter: (i, c) => {
                  // 订单金额 * 结算比例 得到应开票金额
                  return (i * (100 - c.settlement_ratio) / 100).toFixed(2)
                }
              },
              {
                field: 'invoice_finished_amount', title: '累计已开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              // { field: 'invoice_last_amount', title: '上次开票', searchable: false },
              {
                field: 'settlement_ratio_2', title: '应缴税费', searchable: false, formatter: function (i, c) {
                  if (i) {
                    if (c.invoice_type) {
                      const Amount = c.order_amount * (100 - c.settlement_ratio) / 100
                      if (c.invoice_type == 3) {
                        return ((Amount * (i - c.settlement_ratio) / 100) / 1.13 * 0.13 * 1.15).toFixed(2);
                      } else if (c.invoice_type == 1) {
                        return (Amount * (100 - c.settlement_ratio) / 100 / 1.13 * 0.13 * 1.15).toFixed(2);
                      }
                    } else {
                      return '-'
                    }
                  } else {
                    return '-'
                  }
                }
              },
              {
                field: 'invoice_amount', title: '实缴税费', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              { field: 'remark', title: '备注', searchable: false },
              {
                field: 'operate', title: __('Operate'),
                buttons: [{
                  name: 'dialog',
                  title: '实缴税费',
                  text: '实缴税费',
                  extend: 'data-area=["600px","200px"]',
                  classname: 'btn btn-xs btn-warning btn-dialog',
                  // icon: 'fa fa-list',
                  url: 'finance/invoice/invoice_amount'
                }],
                table: outSubOrder,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });


        Table.api.bindevent(outSubOrder);

        $(document).on("click", ".btn-invoice", function (e) {
          var url = $(this).attr('data-url');

          $.post("/VaLvWHNYjO.php/finance/invoice/check", { invoice_id: Fast.api.query('ids') }, function (response) {
            if (response.code == 1) {
              Fast.api.open('finance/invoice/add_invoice_record/invoice_type/' + response.data + '/ids/' + Fast.api.query("ids"), "录入发票", {
                shadeClose: false,
                area: ['85%', '80%'],
                callback: function (value) {
                  //在回调函数里可以调用你的业务代码实现前端的各种逻辑和效果
                }
              });
            } else {
              url += '/invoice_id/' + Fast.api.query('ids');
              Fast.api.open(url, "选择发票类型", {
                shadeClose: false,
                area: ['600px', '250px'],
                callback: function (value) {
                  // window.parent.location.reload();

                  // window.location.href = window.location.href;
                  Fast.api.open('finance/invoice/add_invoice_record/invoice_type/' + value + '/ids/' + Fast.api.query("ids"), "录入发票", {
                    shadeClose: false,
                    area: ['90%', '80%'],
                    callback: function (value) {
                      console.log('sss')
                      //在回调函数里可以调用你的业务代码实现前端的各种逻辑和效果
                    }
                  });
                }
              });
            }
          })

        });
      },
      fourth: function () {
        var OutInvoiceRecord = $('#OutInvoiceRecord');
        OutInvoiceRecord.bootstrapTable({
          url: 'finance/invoice/OutInvoiceRecord/invoiceId/' + Fast.api.query("ids"),
          toolbar: '#toolbar4',
          pk: 'id',
          sortName: 'id',
          // pagination: false,
          commonSearch: false,
          search: false,
          extend: {
            // index_url: 'finance/invoice/OutInvoiceRecord/ids/' + Fast.api.query("ids"),
            del_url: 'finance/invoice/invoice_record_del',
            // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            table: 'invoice_detail',
          },
          // search: false,
          columns: [
            [
              // { checkbox: true },
              { field: 'id', title: 'ID' },
              {
                field: 'amount', title: '发票金额', formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              { field: 'invoice_record_no', title: '发票号' },
              { field: 'invoice_at', title: '开票日期', align: 'right' },
              { field: 'receive_at', title: '收票日期' },
              {
                field: 'type', title: '发票类型', formatter: (i) => {
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
                field: 'operate', title: __('Operate'),
                buttons: [{
                  name: 'dialog',
                  title: '查看',
                  classname: 'btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-list',
                  url: 'finance/invoice/invoice_record'
                }],
                table: OutInvoiceRecord,
                events: Table.api.events.operate, formatter: function (value, row, index) {
                  var that = $.extend({}, this);
                  var table = $(that.table).clone(true);
                  if (row.status == 3) {
                    $(table).data("operate-del", null);
                  }

                  that.table = table;
                  return Table.api.formatter.operate.call(that, value, row, index);
                }
              }
            ]
          ]
        });

        OutInvoiceRecord.on('post-body.bs.table', function (e, settings, json, xhr) {
          $(".btn-delone").data("success", function () {
            location.reload();
            return false;
          });
        });

        Table.api.bindevent(OutInvoiceRecord);
      },
      sixth: function () {
        var inSubOrder = $('#inSubOrder');
        inSubOrder.bootstrapTable({
          url: 'finance/invoice/inSubOrder/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar6',
          pk: 'id',
          sortName: 'id',
          pagination: false,
          commonSearch: false,
          search: false,
          extend: {
            // del_url: 'finance/invoice/income_del',
            // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            table: 'invoice_detail_in',
          },
          // search: false,
          columns: [
            [

              // { checkbox: true },
              { field: 'sub_order_no', title: '订单编号' },
              { field: 'project_name', title: '项目' },
              { field: 'purchaser_name', title: '采购' },
              {
                field: 'order_amount', title: '应开票金额', searchable: false, formatter: (i, c) => {
                  // 订单金额 * 结算比例 得到应开票金额
                  return (i * (100 - c.settlement_ratio) / 100).toFixed(2)
                }
              },
              {
                field: 'invoice_finished_amount', title: '累计已开票金额', searchable: false, formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              // { field: 'invoice_last_amount', title: '上次开票', searchable: false },
              {
                field: 'settlement_ratio_2', title: '应缴税费', searchable: false, formatter: function (i, c) {
                  if (i) {
                    if (c.invoice_type) {
                      const Amount = c.order_amount * (100 - c.settlement_ratio) / 100
                      if (c.invoice_type == 3) {
                        return ((Amount * (c.settlement_ratio - i) / 100) / 1.13 * 0.13 * 1.15).toFixed(2);
                      } else if (c.invoice_type == 1) {
                        return (Amount * (100 - c.settlement_ratio) / 100 / 1.13 * 0.13 * 1.15).toFixed(2);
                      }
                    } else {
                      return '-'
                    }
                  } else {
                    return '-'
                  }
                }
              },
              { field: 'invoice_amount', title: '实缴税费', searchable: false },
              { field: 'remark', title: '备注', searchable: false },
              // {
              //   field: 'operate', title: __('Operate'),
              //   buttons: [{
              //     name: 'addtabs',
              //     title: '查看',
              //     text: '实缴税费',
              //     classname: 'btn btn-xs btn-warning btn-dialog',
              //     // icon: 'fa fa-list',
              //     url: 'finance/invoice/invoice_amount'
              //   }],
              //   table: inSubOrder,
              //   events: Table.api.events.operate, formatter: Table.api.formatter.operate
              // }
            ]
          ]
        });


        Table.api.bindevent(inSubOrder);
      },
      fifth: function () {
        var invoiceRecordOrder = $('#invoiceRecordOrder');
        invoiceRecordOrder.bootstrapTable({
          url: 'finance/invoice/InvoiceRecordOrder/ids/' + Fast.api.query("ids"),
          toolbar: '#toolbar5',
          pk: 'id',
          sortName: 'id',
          pagination: false,
          commonSearch: false,
          search: false,
          extend: {
            // del_url: 'finance/invoice/income_del',
            // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
            // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
            table: 'add_invoice_record',
          },
          columns: [
            [
              // { checkbox: true },
              { field: 'sub_order_no', title: '订单编号' },
              {
                field: 'order_amount', title: '剩余开票金额', formatter: function (i, c) {
                  return (i * (100 - c.settlement_ratio) / 100 - c.amount).toFixed(2);
                }
              },
              { field: 'this_amount', title: '本次金额', formatter: Controller.api.formatter.amount },
              { field: 'remark', title: '备注', formatter: Controller.api.formatter.remark },
            ]
          ]
        });
        Table.api.bindevent(invoiceRecordOrder);
      },
      seventh: function () {
        var InInvoiceRecord = $('#InInvoiceRecord');
        InInvoiceRecord.bootstrapTable({
          url: 'finance/invoice/InInvoiceRecord/invoiceId/' + Fast.api.query("ids"),
          toolbar: '#toolbar7',
          pk: 'id',
          sortName: 'id',
          // pagination: false,
          commonSearch: false,
          search: false,
          extend: {
            table: 'invoice_detail',
          },
          // search: false,
          columns: [
            [
              // { checkbox: true },
              { field: 'id', title: 'ID' },
              {
                field: 'amount', title: '发票金额', formatter: function (i) {
                  return Number(i).toFixed(2);
                }
              },
              { field: 'invoice_record_no', title: '发票号' },
              { field: 'invoice_at', title: '开票日期', align: 'right' },
              { field: 'receive_at', title: '收票日期' },
              {
                field: 'type', title: '发票类型', formatter: (i) => {
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
                field: 'operate', title: __('Operate'),
                buttons: [{
                  name: 'dialog',
                  title: '查看',
                  classname: 'btn btn-xs btn-warning btn-dialog',
                  icon: 'fa fa-list',
                  url: 'finance/invoice/invoice_record'
                }],
                table: InInvoiceRecord,
                events: Table.api.events.operate, formatter: Table.api.formatter.operate
              }
            ]
          ]
        });

        Table.api.bindevent(InInvoiceRecord);
      },
    },
    invoice_type: function () {
      Controller.api.bindevent();
    },

    arrvied: function () {
      Controller.api.bindevent();
    },
    invoice_amount: function () {
      Controller.api.bindevent();
    },
    arrived: function () {
      Controller.api.bindevent();
    },
    invoice_record: function () {
      Controller.api.bindevent();
    },
    api: {
      bindevent: function () {
        Form.api.bindevent($("form[role=form]"), function (data, ret) {
          //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
          const url = ret.url;
          // console.log(url);
          // || url.indexOf('invoice_type') > -1
          if (url.indexOf('add_invoice_record') > -1) {
            window.parent.location.reload();
          }
          Fast.api.close(data);

          //这里是关闭弹窗后传递 Fast.api.open中的callback:function
          // 具体可以看下老大写的close方法
        }, function (data, ret) {
          // Toastr.success("生成URL失败");
        });

      },
      formatter: {
        amount: function (value, row, index) {
          const last_amount = row.order_amount * (100 - row.settlement_ratio) / 100 - row.amount;
          return '<div>'
            + '<div class="input-group input-group-sm hidden" style="width:250px; margin:0 auto "><input type="text" class="form-control input-sm" name="subOrder[' + index + '][sub_order_no]" value="' + row.sub_order_no + '"></div>'
            + '<div class="input-group input-group-sm hidden" style="width:250px; margin:0 auto "><input type="text" class="form-control input-sm" name="subOrder[' + index + '][last_amount]" value="' + last_amount + '"></div>'
            + '<div class="input-group input-group-sm hidden" style="width:250px; margin:0 auto "><input type="text" class="form-control input-sm" name="subOrder[' + index + '][sub_order_id]" value="' + row.id + '"></div>'
            + '<div class="input-group input-group-sm" style="width:250px; margin:0 auto "><input type="text" class="form-control input-sm" name="subOrder[' + index + '][amount]" value="" type="number"></div>' + '</div>';
        },
        remark: function (value, row, index) {
          return '<div class="input-group input-group-sm" style="width:250px; margin:0 auto"><textarea class="form-control" rows="5" name="subOrder[' + index + '][remark]" cols="50" value="" /></div>';
        }
      }
    }
  };
  return Controller;
});
