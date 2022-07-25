define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'area/district/index' + location.search,
                    add_url: 'area/district/add',
                    edit_url: 'area/district/edit',
                    del_url: 'area/district/del',
                    multi_url: 'area/district/multi',
                    import_url: 'area/district/import',
                    table: 'district',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'district_id',
                sortName: 'district_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'district_id', title: __('District_id')},
                        {field: 'province_id', title: __('Province_id')},
                        {field: 'city_id', title: __('City_id')},
                        {field: 'district_cname', title: __('District_cname')},
                        {field: 'district_cshortname', title: __('District_cshortname')},
                        {field: 'district_ename', title: __('District_ename')},
                        {field: 'district_eshortname', title: __('District_eshortname')},
                        {field: 'district_aleph', title: __('District_aleph')},
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
