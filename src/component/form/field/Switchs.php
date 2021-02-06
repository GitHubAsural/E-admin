<?php


namespace Eadmin\component\form\field;


use Eadmin\component\form\Field;

/**
 * 开关
 * Class Switchs
 * @link https://element-plus.gitee.io/#/zh-CN/component/switch
 * @method $this checkedChildren(string $text) switch 打开时的文字描述
 * @method $this unCheckedChildren(string $text) switch 关闭时的文字描述
 * @method $this activeValue($value) switch 打开时的值
 * @method $this inactiveValue($value) switch 关闭时的值
 * @method $this url(string $value) 请求url
 * @method $this params(array $value)  请求数据
 * @method $this field(string $value)  请求值字段名称
 * @package Eadmin\component\form\field
 */
class Switchs extends Field
{
    protected $name = 'EadminSwitch';
    public function __construct($field = null, $value = '')
    {
        parent::__construct($field, $value);
        $this->state([1 => '开启'], [0 => '关闭']);
    }

    /**
     * 设置状态
     * @param array $active 开启状态 [1=>'开启']
     * @param array $inactive 关闭状态 [0=>'关闭]
     * @return $this
     */
    public function state(array $active, array $inactive)
    {
        $this->checkedChildren(current($active));
        $this->activeValue(key($active));
        $this->unCheckedChildren(current($inactive));
        $this->inactiveValue(key($inactive));
        return $this;
    }
}
