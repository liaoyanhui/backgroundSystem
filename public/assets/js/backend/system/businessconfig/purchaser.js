/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-13 11:24:00
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-14 17:32:55
 * @FilePath: /baiying/public/assets/js/backend/system/businessconfig/purchaser.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * 
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    let firstTable = Object.assign({ ...Table });
    let secondTable = Object.assign({ ...Table });
    var Controller = {
        index: function () {
            // // 初始化表格参数配置
            // firstTable.api.init();

            firstTable.api.init({
                extend: {
                    index_url: 'system/businessconfig/purchaser/index' + location.search,
                    add_url: 'system/businessconfig/purchaser/add',
                    edit_url: 'system/businessconfig/purchaser/edit',
                    del_url: 'system/businessconfig/purchaser/del',
                    multi_url: 'system/businessconfig/purchaser/multi',
                    import_url: 'system/businessconfig/purchaser/import',
                    table: 'table1',
                }
            });
            secondTable.api.init({
                extend: {
                    index_url: 'system/businessconfig/purchaser_level/index' + location.search,
                    add_url: 'system/businessconfig/purchaser_level/add',
                    edit_url: 'system/businessconfig/purchaser_level/edit',
                    del_url: 'system/businessconfig/purchaser_level/del',
                    multi_url: 'system/businessconfig/purchaser_level/multi',
                    import_url: 'system/businessconfig/purchaser_level/import',
                    table: 'table2',
                }
            });



            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
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
                var table1 = $('#table1');
                // 初始化表格
                table1.bootstrapTable({
                    url: 'system/businessconfig/purchaser/table1',
                    pk: 'id',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    search: false,
                    extend: {
                        index_url: 'system/businessconfig/purchaser/index' + location.search,
                        add_url: 'system/businessconfig/purchaser/add',
                        edit_url: 'system/businessconfig/purchaser/edit',
                        del_url: 'system/businessconfig/purchaser/del',
                        multi_url: 'system/businessconfig/purchaser/multi',
                        import_url: 'system/businessconfig/purchaser/import',
                        table: 'table1',
                    },
                    // fixedColumns: true,
                    // fixedRightNumber: 1,
                    // extend: {
                    //     add_url: 'system/businessconfig/purchaser/add',
                    //     edit_url: 'system/businessconfig/purchaser/edit',
                    //     del_url: 'system/businessconfig/purchaser/del',
                    //     multi_url: 'system/businessconfig/purchaser/multi',
                    //     import_url: 'system/businessconfig/purchaser/import',
                    // },
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') },
                            {
                                field: 'name1_id', title: __('Name'), formatter: (data, row) => {
                                    return row.name1_name + (row.name2_name || '') + (row.name3_name || '') + (row.name4_name || '') + (row.name5_name || '')
                                }
                            },
                            // { field: 'eshortname', title: __('Eshortname'), operate: 'LIKE' },
                            // { field: 'name1_name', title: __('Name1_name'), operate: 'LIKE' },
                            // { field: 'name2_id', title: __('Name2_id') },
                            // { field: 'name2_name', title: __('Name2_name'), operate: 'LIKE' },
                            // { field: 'name3_id', title: __('Name3_id') },
                            // { field: 'name3_name', title: __('Name3_name'), operate: 'LIKE' },
                            // { field: 'name4_id', title: __('Name4_id') },
                            // { field: 'name4_name', title: __('Name4_name'), operate: 'LIKE' },
                            // { field: 'name5_id', title: __('Name5_id') },
                            // { field: 'name5_name', title: __('Name5_name'), operate: 'LIKE' },
                            { field: 'addr', title: __('Addr') },
                            { field: 'contact_name', title: __('Contact_name') },
                            // { field: 'contact_duty', title: __('Addr') },
                            { field: 'contact_way', title: __('Contact_way') },
                            // { field: 'deleted_at', title: __('Deleted_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            // { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            // { field: 'updated_at', title: __('Updated_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
                            { field: 'operate', title: __('Operate'), table: table1, events: firstTable.api.events.operate, formatter: firstTable.api.formatter.operate, cellStyle: { css: { 'white-space': 'nowrap' } } }
                        ]
                    ]
                });
                // 为表格1绑定事件
                firstTable.api.bindevent(table1);
            },
            second: function () {
                var table2 = $('#table2');
                // 初始化表格
                table2.bootstrapTable({
                    url: 'system/businessconfig/purchaser/table2',
                    // pk: 'id',
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    search: false,
                    extends: {
                        index_url: 'system/businessconfig/purchaser_level/index' + location.search,
                        add_url: 'system/businessconfig/purchaser_level/add',
                        edit_url: 'system/businessconfig/purchaser_level/edit',
                        del_url: 'system/businessconfig/purchaser_level/del',
                        multi_url: 'system/businessconfig/purchaser_level/multi',
                        import_url: 'system/businessconfig/purchaser_level/import',
                    },
                    // fixedColumns: true,
                    // fixedRightNumber: 1,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') },
                            { field: 'name', title: __('Name'), operate: 'LIKE' },
                            // {field: 'parent_id', title: __('Parent_id')},
                            {
                                field: 'level_text', title: __('Level'), operate: 'LIKE'
                            },
                            { field: 'eshortname', title: __('Eshortname') },
                            // {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                            // {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                            {
                                field: 'operate', title: __('Operate'), table: table2, events: secondTable.api.events.operate, formatter: secondTable.api.formatter.operate, buttons: [
                                    {
                                        name: 'detail',
                                        title: '详情',
                                        classname: 'btn btn-xs btn-warning btn-addtabs',
                                        icon: 'fa fa-folder-o',
                                        url: 'system/businessconfig/purchaser_level/detail'
                                    },
                                ], cellStyle: { css: { 'white-space': 'nowrap' } }
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                secondTable.api.bindevent(table2);

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
