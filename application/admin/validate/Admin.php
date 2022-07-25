<?php
/*
 * @Author: chengbao0312 chengbao777@gmail.com
 * @Date: 2022-06-07 16:42:00
 * @LastEditors: chengbao0312 chengbao777@gmail.com
 * @LastEditTime: 2022-07-18 16:27:52
 * @FilePath: /baiying/application/admin/validate/Admin.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|regex:\w{3,30}|unique:admin',
        'nickname' => 'require',
        'password' => 'require|regex:\S{32}',
        'email'    => 'email|unique:admin,email',
        'mobile'   => 'regex:1[3-9]\d{9}|unique:admin,mobile',
    ];

    /**
     * 提示消息
     */
    protected $message = [];

    /**
     * 字段描述
     */
    protected $field = [];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'email', 'nickname', 'password', 'mobile'],
        'edit' => ['username', 'email', 'nickname', 'password', 'mobile'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => __('Username'),
            'nickname' => __('Nickname'),
            'password' => __('Password'),
            'email'    => __('Email'),
            'mobile'   => __('Mobile'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password')
        ]);
        parent::__construct($rules, $message, $field);
    }
}
