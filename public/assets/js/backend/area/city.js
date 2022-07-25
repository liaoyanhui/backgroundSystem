define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'area/city/index' + location.search,
                    add_url: 'area/city/add',
                    edit_url: 'area/city/edit',
                    del_url: 'area/city/del',
                    multi_url: 'area/city/multi',
                    import_url: 'area/city/import',
                    table: 'city',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'city_id',
                sortName: 'city_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'city_id', title: __('City_id')},
                        {field: 'province_id', title: __('Province_id')},
                        {field: 'city_cname', title: __('City_cname')},
                        {field: 'city_cshortname', title: __('City_cshortname')},
                        {field: 'city_ename', title: __('City_ename')},
                        {field: 'city_eshortname', title: __('City_eshortname')},
                        {field: 'city_aleph', title: __('City_aleph')},
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
