define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'area/province/index' + location.search,
                    add_url: 'area/province/add',
                    edit_url: 'area/province/edit',
                    del_url: 'area/province/del',
                    multi_url: 'area/province/multi',
                    import_url: 'area/province/import',
                    table: 'province',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'province_id',
                sortName: 'province_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'province_id', title: __('Province_id')},
                        {field: 'province_cname', title: __('Province_cname')},
                        {field: 'province_cshortname', title: __('Province_cshortname')},
                        {field: 'province_ename', title: __('Province_ename')},
                        {field: 'province_eshortname', title: __('Province_eshortname')},
                        {field: 'province_aleph', title: __('Province_aleph')},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
