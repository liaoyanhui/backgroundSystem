define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    let getSettlementType = (type) => {
        switch (type) {
            case 1:
                return '订单结算';
            case 2:
                return '月结算';
            case 3:
                return '分红结算';
            case 4:
                return '背靠背结算';
            default:
                return '--';
        }
    }

    let returnInvoiceTax = (type, amount1, amount2) => {
        switch (type) {
            case 1:
                return (amount1 / 1.13 * 0.13 * 1.15).toFixed(2);
            case 2:
                return 0;
            case 3:
                return ((amount1 - amount2) / 1.13 * 0.13 * 1.15).toFixed(2);

            default:
                return '--';
        }
    }

    let status1Table = Object.assign({ ...Table });
    let status2Table = Object.assign({ ...Table });
    let status3Table = Object.assign({ ...Table });
    let status4Table = Object.assign({ ...Table });
    let status5Table = Object.assign({ ...Table });
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            status1Table.api.init({
                extend: {
                    index_url: 'finance/business_sub_order/index/ids/1' + location.search,
                    add_url: 'finance/business_sub_order/add',
                    edit_url: 'finance/business_sub_order/edit',
                    del_url: 'finance/business_sub_order/del',
                    multi_url: 'finance/business_sub_order/multi',
                    import_url: 'finance/business_sub_order/import',
                    table: 'sub1_order',
                }
            });
            status2Table.api.init({
                extend: {
                    index_url: 'finance/business_sub_order/index/ids/2' + location.search,
                    table: 'sub2_order',
                }
            });
            status3Table.api.init({
                extend: {
                    index_url: 'finance/business_sub_order/index/ids/3' + location.search,
                    add_url: 'finance/business_sub_order/add',
                    edit_url: 'finance/business_sub_order/edit',
                    del_url: 'finance/business_sub_order/del',
                    multi_url: 'finance/business_sub_order/multi',
                    import_url: 'finance/business_sub_order/import',
                    table: 'sub3_order',
                }
            });
            status4Table.api.init({
                extend: {
                    index_url: 'finance/business_sub_order/index/ids/4' + location.search,
                    table: 'sub4_order',
                }
            });
            status5Table.api.init({
                extend: {
                    index_url: 'finance/business_sub_order/index/ids/5' + location.search,
                    table: 'sub5_order',
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
            first: () => {
                var table = $("#status1");

                // 初始化表格
                table.bootstrapTable({
                    url: 'finance/business_sub_order/index/ids/1' + location.search,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: '#toolbar1',
                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'sub_order_no', title: __('Sub_order_no'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'purchaser_name', title: '采购', operate: 'LIKE', formatter: (e, row) => {
                                    return row.platform_company_name + '-' + e + '-' + row.category_name
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'order_amount', title: __('Order_amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'from_name', title: '签约公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'to_name', title: '业务公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type', title: __('settlement_type'), operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio', title: __('Settlement_ratio'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount', title: '销项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'invoice_finished_amount', title: '应缴税费', formatter: (e, row) => {
                                    const invoice_type = row.invoice_type;
                                    const order_amount = row.order_amount;
                                    return returnInvoiceTax(invoice_type, order_amount * (100 - row.settlement_ratio) / 100, order_amount * (100 - row.settlement_ratio2) / 100)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_amount', title: '实缴税费', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_finished_amount', title: '回款金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at', title: '回款时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'supplier_company_name', title: '供应商', operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type2', title: '结算方式', operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio2', title: '结算比例', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio2) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount2', title: '进项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at2', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_finished_amount2', title: '已结算金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at2', title: '结算时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格绑定事件
                status1Table.api.bindevent(table);

                // 获取选中项
                $(document).on("click", ".btn-invoice", function () {
                    const data = Table.api.selecteddata(table);
                    const result = data.every(item => item.from_id === data[0].from_id);
                    if (!result) {
                        layer.alert('生成发票单的签约公司得一致！');
                        return;
                    }
                    var ids = Table.api.selectedids(table);
                    Table.api.multi("changestatus", ids.join(","), table, this);
                });
            },
            second: () => {
                var table = $("#status2");

                // 初始化表格
                table.bootstrapTable({
                    url: 'finance/business_sub_order/index/ids/2' + location.search,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: '#toolbar2',
                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'sub_order_no', title: __('Sub_order_no'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'purchaser_name', title: '采购', operate: 'LIKE', formatter: (e, row) => {
                                    return row.platform_company_name + '-' + e + '-' + row.category_name
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'order_amount', title: __('Order_amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'from_name', title: '签约公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'to_name', title: '业务公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type', title: __('settlement_type'), operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio', title: __('Settlement_ratio'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount', title: '销项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'invoice_finished_amount', title: '应缴税费', formatter: (e, row) => {
                                    const invoice_type = row.invoice_type;
                                    return invoice_type === 1 ? 0 : invoice_type === 2 ? invoice_type === Math.floor(e * 0.03 * 100) / 100 : Math.floor(e * 0.13 * 100) / 100
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_amount', title: '实缴税费', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_finished_amount', title: '回款金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at', title: '回款时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'supplier_company_name', title: '供应商', operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type2', title: '结算方式', operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio2', title: '结算比例', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio2) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount2', title: '进项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at2', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_finished_amount2', title: '已结算金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at2', title: '结算时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格绑定事件
                status2Table.api.bindevent(table);
            },
            three: () => {
                var table = $("#status3");

                // 初始化表格
                table.bootstrapTable({
                    url: 'finance/business_sub_order/index/ids/3' + location.search,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: '#toolbar3',
                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'sub_order_no', title: __('Sub_order_no'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'purchaser_name', title: '采购', operate: 'LIKE', formatter: (e, row) => {
                                    return row.platform_company_name + '-' + e + '-' + row.category_name
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'order_amount', title: __('Order_amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'from_name', title: '签约公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'to_name', title: '业务公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type', title: __('settlement_type'), operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio', title: __('Settlement_ratio'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount', title: '销项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'invoice_finished_amount', title: '应缴税费', formatter: (e, row) => {
                                    const invoice_type = row.invoice_type;
                                    return invoice_type === 1 ? 0 : invoice_type === 2 ? invoice_type === Math.floor(e * 0.03 * 100) / 100 : Math.floor(e * 0.13 * 100) / 100
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_amount', title: '实缴税费', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_finished_amount', title: '回款金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at', title: '回款时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'supplier_company_name', title: '供应商', operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type2', title: '结算方式', operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio2', title: '结算比例', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio2) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount2', title: '进项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at2', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_finished_amount2', title: '已结算金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at2', title: '结算时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格绑定事件
                status3Table.api.bindevent(table);
                // 获取选中项
                $(document).on("click", ".btn-settlement", function () {
                    const data = Table.api.selecteddata(table);
                    const result = data.every(item => item.from_id === data[0].from_id);
                    if (!result) {
                        layer.alert('生成结算单的业务公司得一致！');
                        return;
                    }
                    var ids = Table.api.selectedids(table);
                    Table.api.multi("changestatus", ids.join(","), table, this);
                });
            },
            four: () => {
                var table = $("#status4");

                // 初始化表格
                table.bootstrapTable({
                    url: 'finance/business_sub_order/index/ids/4' + location.search,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: '#toolbar4',
                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'sub_order_no', title: __('Sub_order_no'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'purchaser_name', title: '采购', operate: 'LIKE', formatter: (e, row) => {
                                    return row.platform_company_name + '-' + e + '-' + row.category_name
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'order_amount', title: __('Order_amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'from_name', title: '签约公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'to_name', title: '业务公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type', title: __('settlement_type'), operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio', title: __('Settlement_ratio'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount', title: '销项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'invoice_finished_amount', title: '应缴税费', formatter: (e, row) => {
                                    const invoice_type = row.invoice_type;
                                    return invoice_type === 1 ? 0 : invoice_type === 2 ? invoice_type === Math.floor(e * 0.03 * 100) / 100 : Math.floor(e * 0.13 * 100) / 100
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_amount', title: '实缴税费', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_finished_amount', title: '回款金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at', title: '回款时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'supplier_company_name', title: '供应商', operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type2', title: '结算方式', operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio2', title: '结算比例', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio2) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount2', title: '进项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at2', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_finished_amount2', title: '已结算金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at2', title: '结算时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格绑定事件
                status4Table.api.bindevent(table);
            },
            five: () => {
                var table = $("#status5");

                // 初始化表格
                table.bootstrapTable({
                    url: 'finance/business_sub_order/index/ids/5' + location.search,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: '#toolbar5',
                    fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'sub_order_no', title: __('Sub_order_no'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'project_name', title: __('Project_name'), operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'purchaser_name', title: '采购', operate: 'LIKE', formatter: (e, row) => {
                                    return row.platform_company_name + '-' + e + '-' + row.category_name
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'order_amount', title: __('Order_amount'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'from_name', title: '签约公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'to_name', title: '业务公司', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type', title: __('settlement_type'), operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio', title: __('Settlement_ratio'), operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount', title: '销项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'invoice_finished_amount', title: '应缴税费', formatter: (e, row) => {
                                    const invoice_type = row.invoice_type;
                                    return invoice_type === 1 ? 0 : invoice_type === 2 ? invoice_type === Math.floor(e * 0.03 * 100) / 100 : Math.floor(e * 0.13 * 100) / 100
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_amount', title: '实缴税费', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_finished_amount', title: '回款金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at', title: '回款时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'supplier_company_name', title: '供应商', operate: 'LIKE', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_type2', title: '结算方式', operate: 'BETWEEN', formatter: (e) => getSettlementType(e), cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_ratio2', title: '结算比例', operate: 'BETWEEN', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            {
                                field: 'order_amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                    return (e * (100 - row.settlement_ratio2) / 100).toFixed(2)
                                }, cellStyle: { css: { 'white-space': 'nowrap' } }
                            },
                            { field: 'invoice_finished_amount2', title: '进项发票金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'invoice_last_at2', title: '时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'settlement_finished_amount2', title: '已结算金额', cellStyle: { css: { 'white-space': 'nowrap' } }, formatter: (e) => Number(e).toFixed(2) },
                            { field: 'settlement_last_at2', title: '结算时间', cellStyle: { css: { 'white-space': 'nowrap' } } },
                            { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                        ]
                    ]
                });

                // 为表格绑定事件
                status5Table.api.bindevent(table);
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
