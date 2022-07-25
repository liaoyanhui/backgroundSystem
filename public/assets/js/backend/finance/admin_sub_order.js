/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-30 09:54:57
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-18 14:14:11
 * @FilePath: /baiying/public/assets/js/backend/finance/admin_sub_order.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
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
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/admin_sub_order/index' + location.search,
                    // add_url: 'finance/admin_sub_order/add',
                    // edit_url: 'finance/admin_sub_order/edit',
                    // del_url: 'finance/admin_sub_order/del',
                    // multi_url: 'finance/admin_sub_order/multi',
                    // import_url: 'finance/admin_sub_order/import',
                    table: 'order',
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
                columns: [
                    [
                        { checkbox: true },
                        // {field: 'id', title: __('Id')},
                        { field: 'order_no', title: __('Order_no'), operate: 'LIKE' },
                        // {field: 'project_id', title: __('Project_id')},
                        { field: 'project_name', title: __('Project_name'), operate: 'LIKE' },
                        // {field: 'project_province', title: __('Project_province')},
                        // {field: 'purchaser_id', title: __('Purchaser_id')},
                        {
                            field: 'purchaser_name', title: __('Purchaser_name'), operate: 'LIKE', formatter: (e, row) => {
                                return row.platform_company_name + '-' + e + '-' + row.category_name
                            }
                        },
                        { field: 'amount', title: __('Amount'), operate: 'BETWEEN', formatter: (e) => Number(e).toFixed(2) },
                        // { field: 'settlement_type3', title: __('Settlement_type1'), formatter: (e) => getSettlementType(e) },
                        { field: 'settlement_ratio3', title: __('Settlement_ratio1'), operate: 'BETWEEN' },
                        {
                            field: 'amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                return Math.floor(e * (100 - row.settlement_ratio3)) / 100
                            }
                        },
                        { field: 'contracting_company_name', title: __('Contracting_company_name'), operate: 'LIKE' },

                        { field: 'invoice_finished_amount3', title: '销项发票金额', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'invoice_last_at3', title: '时间' },
                        {
                            field: 'invoice_finished_amount3', title: '应缴税费', formatter: (e, row) => {
                                const invoice_type = row.invoice_type3;
                                const order_amount = row.amount;
                                return returnInvoiceTax(invoice_type, order_amount * (100 - row.settlement_ratio3) / 100, order_amount * (100 - row.settlement_ratio2) / 100);
                            }
                        },
                        { field: 'invoice_amount3', title: '实缴税费', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'settlement_finished_amount3', title: '回款金额', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'settlement_last_at3', title: '回款时间' },
                        // { field: 'platform_company_id', title: __('Platform_company_id') },
                        // { field: 'platform_company_name', title: __('Platform_company_name'), operate: 'LIKE' },
                        // { field: 'contracting_company_id', title: __('Contracting_company_id') },
                        // { field: 'settlement_type2', title: __('Settlement_type2'), formatter: (e) => getSettlementType(e) },
                        { field: 'business_company_name', title: __('Business_company_name'), operate: 'LIKE' },

                        { field: 'settlement_ratio2', title: __('Settlement_ratio2'), operate: 'BETWEEN' },
                        {
                            field: 'amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                return Math.floor(e * (100 - row.settlement_ratio2)) / 100
                            }
                        },

                        { field: 'invoice_finished_amount2', title: '销项发票金额', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'invoice_last_at2', title: '时间' },
                        {
                            field: 'invoice_finished_amount2', title: '应缴税费', formatter: (e, row) => {
                                const invoice_type = row.invoice_type2;
                                const order_amount = row.amount;
                                return returnInvoiceTax(invoice_type, order_amount * (100 - row.settlement_ratio2) / 100, order_amount * (100 - row.settlement_ratio1) / 100);
                            }
                        },
                        { field: 'invoice_amount2', title: '实缴税费', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'settlement_finished_amount2', title: '回款金额', formatter: (e) => Number(e).toFixed(2) },
                        { field: 'settlement_last_at2', title: '回款时间' },
                        // { field: 'business_company_id', title: __('Business_company_id') },
                        // { field: 'salesman_id', title: __('Salesman_id') },
                        // { field: 'salesman_name', title: __('Salesman_name'), operate: 'LIKE' },
                        // { field: 'supplier_id', title: __('Supplier_id') },
                        { field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE' },

                        // { field: 'settlement_type1', title: __('Settlement_type3'), formatter: (e) => getSettlementType(e) },
                        { field: 'settlement_ratio1', title: __('Settlement_ratio3'), operate: 'BETWEEN' },
                        {
                            field: 'amount', title: '应结算金额', operate: 'BETWEEN', formatter: (e, row) => {
                                return Math.floor(e * (100 - row.settlement_ratio1)) / 100
                            }
                        },

                        { field: 'invoice_finished_amount1', title: '销项发票金额' },
                        { field: 'invoice_last_at1', title: '时间' },
                        // {
                        //     field: 'invoice_finished_amount1', title: '应缴税费', formatter: (e, row) => {
                        //         const invoice_type = row.invoice_type1;
                        //         return invoice_type === 1 ? 0 : invoice_type === 2 ? invoice_type === Math.floor(e * 0.03 * 100) / 100 : Math.floor(e * 0.13 * 100) / 100
                        //     }
                        // },
                        // { field: 'invoice_amount1', title: '实缴税费' },
                        { field: 'settlement_finished_amount1', title: '已结算金额' },
                        { field: 'settlement_last_at1', title: '结算时间' },
                        // { field: 'deliver_way', title: __('Deliver_way') },
                        // { field: 'certificate', title: __('Certificate'), operate: 'LIKE' },
                        // { field: 'status', title: __('Status'), searchList: { "5) unsigne": __('5) unsigne') }, formatter: Table.api.formatter.status },
                        // { field: 'audit_at', title: __('Audit_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'deliver_at', title: __('Deliver_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'arrived_at', title: __('Arrived_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'finished_at', title: __('Finished_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'deleted_at', title: __('Deleted_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        // { field: 'updated_at', title: __('Updated_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
