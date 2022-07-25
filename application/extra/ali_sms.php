<?php

return [
    'access_id'		=> 'LTAI5tEQMqjWdznkB44Rxs7G',			//阿里云短信 keyId
    'access_secret'	=> 'XVXVgEMITgqo0C4nd1YR1YYhMTQuTe',	//阿里云短信 keysecret
    'endpoint'      => 'dysmsapi.aliyuncs.com',                  //地址
    'scheme'        => 'http',
    'product'       => '佰赢控股',
    'actions'       => [
        'admin_login'        => [
            'sign_name'      => '佰赢控股',
            'template_code'  => 'SMS_240361391',
            'template_param' => [
                'code'    => '',
            ],
        ],
    ]
];