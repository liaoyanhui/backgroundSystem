/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-23 10:06:59
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-11 17:33:21
 * @FilePath: /baiying/public/assets/js/backend/project/project.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

  var Controller = {
    index: function () {
      // 初始化表格参数配置
      Table.api.init({
        extend: {
          index_url: 'project/project/index' + location.search,
          add_url: 'project/project/add',
          edit_url: 'project/project/edit',
          del_url: 'project/project/del',
          multi_url: 'project/project/multi',
          import_url: 'project/project/import',
          table: 'project',
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
            // { field: 'id', title: __('Id') },
            { field: 'project_no', title: __('Project_no'), operate: 'LIKE' },
            { field: 'name', title: __('Name'), operate: 'LIKE' },
            // { field: 'purchaser_id', title: __('Purchaser_id') },
            { field: 'purchaser_fullname', title: __('Purchaser_fullname'), operate: 'LIKE' },
            // { field: 'province_id', title: __('Province_id') },
            {
              field: 'province_cname', title: __('Province_cname'), formatter: (row, data) => {
                return data.province_cname + data.city_cname + data.district_cname + data.addr
              }
            },
            // { field: 'city_id', title: __('City_id') },
            // { field: 'city_cname', title: __('City_cname') },
            // { field: 'district_id', title: __('District_id') },
            // { field: 'district_cname', title: __('District_cname') },
            // { field: 'addr', title: __('Addr') },
            // { field: 'manager', title: __('Manager'), operate: 'LIKE' },
            // { field: 'contact_info', title: __('Contact_info'), operate: 'LIKE' },
            { field: 'remark', title: __('remark') },
            // { field: 'deleted_at', title: __('Deleted_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            // { field: 'created_at', title: __('Created_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            // { field: 'updated_at', title: __('Updated_at'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false },
            {
              field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [
                // {
                //   name: 'addtabs',
                //   title: '详情',
                //   classname: 'btn btn-xs btn-warning btn-addtabs',
                //   icon: 'fa fa-folder-o',
                //   url: 'project/project/detail'
                // }
              ]
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
    edit: function () {
      Controller.api.bindevent();
    },
    api: {
      bindevent: function () {
        $("#c-province_id").data("eSelect", () => {
          $("#c-city_id").selectPageClear();
          $("#c-district_id").selectPageClear();
        })

        $("#c-city_id").data("eSelect", () => {
          $("#c-district_id").selectPageClear();
        })
        $("#c-city_id").data("params", function (obj) {
          return { province_id: $("#c-province_id").val() };
        });

        $("#c-district_id").data("params", function (obj) {
          return { city_id: $("#c-city_id").val() };
        });


        Form.api.bindevent($("form[role=form]"));
      }
    }
  };
  return Controller;
});
