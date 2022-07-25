/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-06-29 09:49:57
 * @LastEditTime: 2022-07-18 14:31:29
 * @FilePath: /baiying/public/assets/js/backend/finance/settlement.js
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
        settlement_detail_out: function () {
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
        settlement_detail_in: function () {
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
        add_settlement_record: function () {
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
        // settlement_record: function () {
        //   Table.api.init();
        //   Controller.api.bindevent();
        //   //绑定事件
        //   $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        //     var panel = $($(this).attr("href"));
        //     if (panel.size() > 0) {
        //       Controller.table[panel.attr("id")] && Controller.table[panel.attr("id")].call(this);
        //       $(this).on('click', function (e) {
        //         $($(this).attr("href")).find(".btn-refresh").trigger("click");
        //       });
        //     }
        //     //移除绑定的事件
        //     $(this).unbind('shown.bs.tab');
        //   });

        //   //必须默认触发shown.bs.tab事件
        //   $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        // },
        table: {
            first: function () {
                var output = $("#output");
                output.bootstrapTable({
                    url: 'finance/settlement/Output',
                    toolbar: '#toolbar',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    search: false,
                    extend: {
                        del_url: 'finance/settlement/output_del',
                        table: 'settlement_tab',
                    },
                    // search: false,
                    columns: [
                        [
                            // { checkbox: true },  
                            { field: 'settlement_no', title: '结算单号' },
                            { field: 'from_name', title: '付款方' },
                            { field: 'to_name', title: '收款方' },
                            { field: 'amount', title: '应结算金额', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'finished_amount', title: '累计已回款', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            // { field: 'last_at', title: '上次录入时间', operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            {
                                field: 'status', title: '状态', formatter: (i) => {
                                    switch (i) {
                                        case 1:
                                            return '待回款';
                                        case 2:
                                            return '部分回款';
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
                                    name: 'detail-out',
                                    title: '查看',
                                    classname: 'btn btn-xs btn-warning btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'finance/settlement/settlement_detail_out'
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
                    url: 'finance/settlement/Income',
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    commonSearch: false,
                    search: false,
                    extend: {
                        // del_url: 'finance/settlement/income_del',
                        // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
                        // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
                        table: 'settlement_tab',
                    },
                    // search: false,
                    columns: [
                        [
                            // { checkbox: true },
                            { field: 'settlement_no', title: '结算单号' },
                            { field: 'from_name', title: '付款方' },
                            { field: 'to_name', title: '收款方' },
                            { field: 'amount', title: '应结算金额', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'finished_amount', title: '累计已回款', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            // { field: 'last_at', title: '上次录入时间', operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            {
                                field: 'status', title: '状态', formatter: (i) => {
                                    switch (i) {
                                        case 1:
                                            return '待回款';
                                        case 2:
                                            return '部分回款';
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
                                    name: 'detail-in',
                                    title: '查看',
                                    classname: 'btn btn-xs btn-warning btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'finance/settlement/settlement_detail_in'
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
                    url: 'finance/settlement/outSubOrder/ids/' + Fast.api.query("ids"),
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    pagination: false,
                    commonSearch: false,
                    search: false,
                    extend: {
                        // del_url: 'finance/settlement/income_del',
                        // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
                        // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
                        table: 'settlement_detail_out',
                    },
                    // search: false,
                    columns: [
                        [

                            // { checkbox: true },
                            { field: 'sub_order_no', title: '订单编号' },
                            { field: 'project_name', title: '项目' },
                            {
                                field: 'purchaser_name', title: '采购', formatter: (i, c) => {
                                    // 订单金额 * 结算比例 得到应开票金额0
                                    return c.platform_company_name + '-' + i + '-' + c.category_name
                                }
                            },
                            {
                                field: 'order_amount', title: '应回款金额', searchable: false, formatter: (i, c) => {
                                    // 订单金额 * 结算比例 得到应开票金额
                                    return (i * (100 - c.settlement_ratio) / 100).toFixed(2)
                                }
                            },
                            { field: 'settlement_finished_amount', title: '累计已回款金额', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            // { field: 'settlement_last_amount', title: '上次开票', searchable: false },

                            { field: 'remark', title: '备注', searchable: false },
                        ]
                    ]
                });


                Table.api.bindevent(outSubOrder);

                $(document).on("click", ".btn-settlement", function (e) {
                    var url = $(this).attr('data-url');

                    Fast.api.open('finance/settlement/add_settlement_record/ids/' + Fast.api.query("ids"), "录入回款", {
                        shadeClose: false,
                        area: ['85%', '80%'],
                        callback: function (value) {
                            //在回调函数里可以调用你的业务代码实现前端的各种逻辑和效果
                            // console.log(value, 'ccc')
                        }
                    });

                });
            },

            fourth: function () {
                var settlementRecord = $('#settlementRecord');
                settlementRecord.bootstrapTable({
                    url: 'finance/settlement/settlementRecord/settlementId/' + Fast.api.query("ids"),
                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    pagination: false,
                    commonSearch: false,
                    search: false,
                    extend: {
                        // index_url: 'finance/settlement/settlementRecord/ids/' + Fast.api.query("ids"),
                        del_url: 'finance/settlement/settlement_record_del',
                        // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
                        // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
                        table: 'settlement_detail',
                    },
                    // search: false,
                    columns: [
                        [
                            // { checkbox: true },
                            // { field: 'id', title: 'ID' },
                            { field: 'amount', title: '发票金额', formatter: (e) => Number(e).toFixed(2) },
                            { field: 'pay_bank', title: '支付银行' },
                            { field: 'pay_info', title: '流水号' },
                            { field: 'payback_at', title: '日期', },
                            { field: 'remark', title: '备注' },

                            {
                                field: 'operate', title: __('Operate'),
                                buttons: [{
                                    name: 'dialog',
                                    title: '查看',
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'finance/settlement/settlement_record'
                                }],
                                table: settlementRecord,
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
                settlementRecord.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-delone").data("success", function () {
                        location.reload();
                        return false;
                    });
                });
                Table.api.bindevent(settlementRecord);
            },

            sixth: function () {
                var inSubOrder = $('#inSubOrder');
                inSubOrder.bootstrapTable({
                    url: 'finance/settlement/inSubOrder/ids/' + Fast.api.query("ids"),
                    toolbar: '#toolbar6',
                    pk: 'id',
                    sortName: 'id',
                    pagination: false,
                    commonSearch: false,
                    search: false,
                    extend: {
                        // del_url: 'finance/settlement/income_del',
                        // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
                        // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
                        table: 'settlement_detail_in',
                    },
                    // search: false,
                    columns: [
                        [


                            // { checkbox: true },
                            { field: 'sub_order_no', title: '订单编号' },
                            { field: 'project_name', title: '项目' },
                            {
                                field: 'purchaser_name', title: '采购', formatter: (i, c) => {
                                    // 订单金额 * 结算比例 得到应开票金额
                                    return c.platform_company_name + '-' + i + '-' + c.category_name
                                }
                            },
                            {
                                field: 'order_amount', title: '应回款金额', searchable: false, formatter: (i, c) => {
                                    // 订单金额 * 结算比例 得到应开票金额
                                    return (i * (100 - c.settlement_ratio) / 100).toFixed(2)
                                }
                            },
                            { field: 'settlement_finished_amount', title: '累计已回款金额', searchable: false, formatter: (e) => Number(e).toFixed(2) },
                            // { field: 'settlement_last_amount', title: '上次开票', searchable: false },

                            { field: 'remark', title: '备注', searchable: false },
                        ]
                    ]
                });


                Table.api.bindevent(inSubOrder);

                // $(document).on("click", ".btn-settlement", function (e) {
                //     var url = $(this).attr('data-url');

                //     $.post("/VaLvWHNYjO.php/finance/settlement/check", { settlement_id: Fast.api.query('ids') }, function (response) {
                //         if (response.code == 1) {
                //             Fast.api.open('finance/settlement/add_settlement_record/settlement_type/' + response.data + '/ids/' + Fast.api.query("ids"), "录入发票", {
                //                 shadeClose: false,
                //                 area: ['85%', '80%'],
                //                 callback: function (value) {
                //                     //在回调函数里可以调用你的业务代码实现前端的各种逻辑和效果
                //                     // console.log(value, 'ccc')
                //                 }
                //             });
                //         } else {
                //             url += '/settlement_id/' + Fast.api.query('ids');
                //             Fast.api.open(url, "选择发票类型", {
                //                 shadeClose: false,
                //                 area: ['600px', '250px'],
                //                 callback: function (value) {
                //                     console.log(value, 'value')
                //                     Fast.api.open('finance/settlement/add_settlement_record/settlement_type/' + value + '/ids/' + Fast.api.query("ids"), "录入发票", {
                //                         shadeClose: false,
                //                         area: ['90%', '80%'],
                //                         callback: function (value) {
                //                             //在回调函数里可以调用你的业务代码实现前端的各种逻辑和效果
                //                             // console.log(value, 'ccc')
                //                         }
                //                     });
                //                 }
                //             });
                //         }
                //     })

                // });
            },

            fifth: function () {
                var settlementRecordOrder = $('#settlementRecordOrder');
                settlementRecordOrder.bootstrapTable({
                    url: 'finance/settlement/settlementRecordOrder/ids/' + Fast.api.query("ids"),
                    toolbar: '#toolbar5',
                    pk: 'id',
                    sortName: 'id',
                    pagination: false,
                    commonSearch: false,
                    search: false,
                    extend: {
                        // del_url: 'finance/settlement/income_del',
                        // edit_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_edit/business_id/' + Fast.api.query('ids'),
                        // del_url: 'system/businessconfig/by_business_company/b_s_settlement_ratio_del/business_id/' + Fast.api.query('ids'),
                        table: 'add_settlement_record',
                    },
                    columns: [
                        [
                            // { checkbox: true },
                            { field: 'sub_order_no', title: '订单编号' },
                            {
                                field: 'order_amount', title: '剩余开票金额', formatter: function (i, c) {
                                    return (i * (100 - c.settlement_ratio) / 100).toFixed(2) - c.amount;
                                }
                            },
                            { field: 'this_amount', title: '本次金额', formatter: Controller.api.formatter.amount },
                            { field: 'remark', title: '备注', formatter: Controller.api.formatter.remark },
                        ]
                    ]
                });
                Table.api.bindevent(settlementRecordOrder);
            },
            seventh: function () {
                var inSettlementRecord = $('#inSettlementRecord');
                inSettlementRecord.bootstrapTable({
                    url: 'finance/settlement/settlementRecord/settlementId/' + Fast.api.query("ids"),
                    toolbar: '#toolbar7',
                    pk: 'id',
                    sortName: 'id',
                    pagination: false,
                    commonSearch: false,
                    search: false,
                    columns: [
                        [
                            // { checkbox: true },
                            // { field: 'id', title: 'ID' },
                            { field: 'amount', title: '发票金额', formatter: (e) => Number(e).toFixed(2) },
                            { field: 'pay_bank', title: '支付银行' },
                            { field: 'pay_info', title: '流水号' },
                            { field: 'payback_at', title: '日期', },
                            { field: 'remark', title: '备注' },

                            {
                                field: 'operate', title: __('Operate'),
                                buttons: [{
                                    name: 'dialog',
                                    title: '查看',
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'finance/settlement/settlement_record'
                                }],
                                table: inSettlementRecord,
                                events: Table.api.events.operate, formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                Table.api.bindevent(inSettlementRecord);
            },
        },
        // add: function () {
        //   Controller.api.bindevent();
        // },
        // edit: function () {
        //   Controller.api.bindevent();
        // },
        settlement_type: function () {
            Controller.api.bindevent();
        },
        // settlement_record_del: function () {
        //   Controller.api.bindevent();
        // },
        arrvied: function () {
            Controller.api.bindevent();
        },
        settlement_amount: function () {
            Controller.api.bindevent();
        },
        arrived: function () {
            Controller.api.bindevent();
        },
        settlement_record: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), function (data, ret) {
                    //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                    // console.log(data);
                    // console.log(ret);
                    const url = ret.url;
                    if (url.indexOf('add_settlement_record') > -1) {
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
                // settlementType: function (value, row, index) {
                //   return '<div><div>专票(3%)</div><div>专票(13%)</div><div>普票</div></div>'
                // },
                // accumulative: function (value, row, index) {
                //   return '<div>test</div>'
                // }
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
