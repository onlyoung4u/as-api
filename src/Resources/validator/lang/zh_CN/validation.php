<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => '必须接受 :attribute',
    'accepted_if' => '当 :other 为 :value 时，必须接受 :attribute',
    'active_url' => ':attribute 不是一个有效的网址',
    'after' => ':attribute 必须是 :date 之后的日期',
    'after_or_equal' => ':attribute 必须是 :date 之后的日期或者等于 :date',
    'alpha' => ':attribute 只能由字母组成',
    'alpha_dash' => ':attribute 只能由字母、数字、短划线和下划线组成',
    'alpha_num' => ':attribute 只能由字母和数字组成',
    'array' => ':attribute 必须是数组类型',
    'before' => ':attribute 必须是 :date 之前的日期',
    'before_or_equal' => ':attribute 必须是 :date 之前的日期或者等于 :date',
    'between' => [
        'numeric' => ':attribute 必须介于 :min - :max 之间',
        'file' => ':attribute 必须介于 :min - :max KB 之间',
        'string' => ':attribute 必须介于 :min - :max 个字符之间',
        'array' => ':attribute 必须要有 :min - :max 个元素',
    ],
    'boolean' => ':attribute 必须为布尔类型',
    'confirmed' => ':attribute 两次输入不一致',
    'current_password' => '密码错误',
    'date' => ':attribute 必须是日期类型',
    'date_equals' => ':attribute 必须要等于 :date.',
    'date_format' => ':attribute 的格式必须为 :format.',
    'declined' => '必须拒绝 :attribute',
    'declined_if' => '当 :other 为 :value 时，必须拒绝 :attribute',
    'different' => ':attribute 必须和 :other 不同',
    'digits' => ':attribute 必须是 :digits 位数字',
    'digits_between' => ':attribute 必须是介于 :min 和 :max 位的数字',
    'dimensions' => ':attribute 尺寸不符合规则',
    'distinct' => ':attribute 必须唯一',
    'email' => ':attribute 必须是合法的邮箱类型',
    'ends_with' => ':attribute 必须以 :values 为结尾',
    'enum' => ':attribute 错误',
    'exists' => ':attribute 不存在',
    'file' => ':attribute 必须是文件',
    'filled' => ':attribute 不能为空',
    'gt' => [
        'numeric' => ':attribute 必须大于 :value',
        'file' => ':attribute 必须大于 :value KB',
        'string' => ':attribute 必须多于 :value 个字符',
        'array' => ':attribute 必须多于 :value 个元素',
    ],
    'gte' => [
        'numeric' => ':attribute 必须大于等于 :value',
        'file' => ':attribute 必须大于等于 :value KB',
        'string' => ':attribute 必须多于或等于 :value 个字符',
        'array' => ':attribute 必须多于或等于 :value 个元素',
    ],
    'image' => ':attribute 必须是图片类型',
    'in' => ':attribute 错误',
    'in_array' => ':attribute 必须在 :other 中',
    'integer' => ':attribute 必须是整数',
    'ip' => ':attribute 必须是有效的 IP 地址',
    'ipv4' => ':attribute 必须是有效的 IPV4 地址',
    'ipv6' => ':attribute 必须是有效的 IPV6 地址',
    'json' => ':attribute 必须是正确的 JSON 格式',
    'lt' => [
        'numeric' => ':attribute 必须小于 :value',
        'file' => ':attribute 必须小于 :value KB',
        'string' => ':attribute 必须少于 :value 个字符',
        'array' => ':attribute 必须少于 :value 个元素',
    ],
    'lte' => [
        'numeric' => ':attribute 必须小于等于 :value',
        'file' => ':attribute 必须小于等于 :value KB',
        'string' => ':attribute 必须少于或等于 :value 个字符',
        'array' => ':attribute 必须少于或等于 :value 个元素',
    ],
    'mac_address' => ':attribute 必须是一个有效的 MAC 地址',
    'max' => [
        'numeric' => ':attribute 不能大于 :max',
        'file' => ':attribute 不能大于 :max KB',
        'string' => ':attribute 不能多于 :max 个字符',
        'array' => ':attribute 最多只能有 :max 个元素',
    ],
    'mimes' => ':attribute 必须是一个 :values 类型的文件',
    'mimetypes' => ':attribute 必须是一个 :values 类型的文件',
    'min' => [
        'numeric' => ':attribute 必须大于等于 :min',
        'file' => ':attribute 不能小于 :min KB',
        'string' => ':attribute 至少要有 :min 个字符',
        'array' => ':attribute 至少要有 :min 个元素',
    ],
    'multiple_of' => ':attribute 必须是 :value 的倍数',
    'not_in' => ':attribute 错误',
    'not_regex' => ':attribute 格式错误',
    'numeric' => ':attribute 必须是数字类型',
    'password' => '密码错误',
    'present' => ':attribute 必须存在',
    'prohibited' => ':attribute 必须为空',
    'prohibited_if' => '当 :other 为 :value 时，:attribute 必须为空',
    'prohibited_unless' => '除非 :other 在 :values 中，否则 :attribute 必须为空',
    'prohibits' => '鉴于 :attribute 不为空，:other 必须为空',
    'regex' => ':attribute 格式错误',
    'required' => ':attribute 不能为空',
    'required_array_keys' => ':attribute 必须包含指定的键：:values',
    'required_if' => '当 :other 为 :value 时 :attribute 不能为空',
    'required_unless' => '当 :other 不为 :values 时 :attribute 不能为空',
    'required_with' => '当 :values 存在时 :attribute 不能为空',
    'required_with_all' => '当 :values 存在时 :attribute 不能为空',
    'required_without' => '当 :values 不存在时 :attribute 不能为空',
    'required_without_all' => '当 :values 都不存在时 :attribute 不能为空',
    'same' => ':attribute 和 :other 必须相同',
    'size' => [
        'numeric' => ':attribute 必须等于 :size',
        'file' => ':attribute 大小必须为 :size KB',
        'string' => ':attribute 必须是 :size 个字符',
        'array' => ':attribute 必须正好有 :size 个元素',
    ],
    'starts_with' => ':attribute 必须以 :values 为开头',
    'string' => ':attribute 必须是字符串类型',
    'timezone' => ':attribute 必须是合法的时区类型',
    'unique' => ':attribute 必须唯一',
    'uploaded' => ':attribute 上传失败',
    'url' => ':attribute 必须是一个网址',
    'uuid' => ':attribute 必须是有效的 UUID',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
