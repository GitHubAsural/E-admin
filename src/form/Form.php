<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2021-01-01
 * Time: 09:22
 */

namespace Eadmin\form;


use Eadmin\component\basic\TabPane;
use Eadmin\component\basic\Tabs;
use Eadmin\component\form\Field;
use Eadmin\component\form\field\DatePicker;
use Eadmin\component\form\field\Input;
use Eadmin\component\form\field\Select;
use Eadmin\component\form\field\TimePicker;
use Eadmin\component\form\FormAction;
use Eadmin\component\form\FormItem;
use Eadmin\component\form\FormMany;
use Eadmin\component\layout\Row;
use think\helper\Str;
use think\Model;

/**
 * 表单
 * Class Form
 * @link https://element-plus.gitee.io/#/zh-CN/component/form
 * @package Eadmin\component\form\field
 * @method \Eadmin\component\form\field\Input text($field, $label) 文本输入框
 * @method \Eadmin\component\form\field\Input hidden($field) 隐藏输入框
 * @method \Eadmin\component\form\field\Input textarea($field, $label) 多行文本输入框
 * @method \Eadmin\component\form\field\Input password($field, $label) 密码输入框
 * @method \Eadmin\component\form\field\Number number($field, $label) 数字输入框
 * @method \Eadmin\component\form\field\Select select($field, $label) 下拉选择器
 * @method \Eadmin\component\form\field\RadioGroup radio($field, $label) 单选框
 * @method \Eadmin\component\form\field\CheckboxGroup checkbox($field, $label) 多选框
 * @method \Eadmin\component\form\field\Switchs switch ($field, $label) switch开关
 * @method \Eadmin\component\form\field\DatePicker datetime($field, $label) 日期时间
 * @method \Eadmin\component\form\field\DatePicker datetimeRange($startFiled, $endField, $label) 日期时间范围时间
 * @method \Eadmin\component\form\field\DatePicker dateRange($startFiled, $endField, $label) 日期范围时间
 * @method \Eadmin\component\form\field\DatePicker date($field, $label) 日期
 * @method \Eadmin\component\form\field\DatePicker dates($field, $label) 多选日期
 * @method \Eadmin\component\form\field\DatePicker year($field, $label) 年
 * @method \Eadmin\component\form\field\DatePicker month($field, $label) 月
 * @method \Eadmin\component\form\field\TimePicker timeRange($startFiled, $endField, $label) 日期范围时间
 * @method \Eadmin\component\form\field\TimePicker time($field, $label) 时间
 * @method \Eadmin\component\form\field\Slider slider($field, $label) 滑块
 * @method \Eadmin\component\form\field\Color color($field, $label) 颜色选择器
 * @method \Eadmin\component\form\field\Rate rate($field, $label) 评分组件
 * @method \Eadmin\component\form\field\Upload file($field, $label) 文件上传
 * @method \Eadmin\component\form\field\Upload image($field, $label) 图片上传
 * @method \Eadmin\component\form\field\Editor editor($field, $label) 富文本编辑器
 * @method \Eadmin\component\form\field\Tree tree($field, $label) 树形
 * @method \Eadmin\component\form\field\Cascader cascader(...$field, $label) 级联选择器
 * @method \Eadmin\component\form\field\Transfer transfer($field, $label) 穿梭框
 * @method \Eadmin\component\form\field\Icon icon($field, $label) 图标选择器
 * @method \Eadmin\component\form\field\IframeTag iframeTag($field, $label) 弹窗选择框
 * @method \Eadmin\component\form\field\Map map($lng, $lat, $address, $label) 高德地图
 * @method \Eadmin\component\form\field\TableText tableText($field, $label) 表格编辑
 */
class Form extends Field
{
    use FormModel;

    protected $name = 'EadminForm';
    protected $actions;
    protected $tab;

    //用于控制回调布局是否追加到from元素
    protected $itemBool = true;

    protected $itemComponent = [];
    protected $formItem = [];

    //是否编辑表单
    protected $isEdit = false;

    public function __construct($data)
    {
        if ($data instanceof Model) {
            $this->setModel($data);
        } else {
            $this->data = $data;
        }
        $field = Str::random(15, 3);
        $this->bindAttr('model', $field);
        $this->actions = new FormAction($this);
        $this->labelWidth('100px');
    }

    /**
     * 表单验证规则
     * @param array $value
     * @return $this
     */
    public function rules(array $value)
    {
        $this->attr(__FUNCTION__, $bool);
        return $this;
    }

    /**
     * 是否在输入框中显示校验结果反馈图标
     * @param bool $value
     * @return $this
     */
    public function statusIcon(bool $value = true)
    {
        $this->attr(__FUNCTION__, $bool);
        return $this;
    }

    /**
     * 是否显示必填字段的标签旁边的红色星号
     * @param bool $value
     * @return $this
     */
    public function hideRequiredAsterisk(bool $value = true)
    {
        $this->attr(__FUNCTION__, $bool);
        return $this;
    }

    /**
     * 行内表单模式
     * @param bool $bool
     * @return $this
     */
    public function inline(bool $bool = true)
    {
        $this->attr(__FUNCTION__, $bool);
        return $this;
    }

    /**
     * 表单域标签的后缀
     * @param string $value
     * @return $this
     */
    public function labelSuffix(string $value)
    {
        $this->attr(__FUNCTION__, $value);
        return $this;
    }

    /**
     * 表单域标签的宽度
     * @param string $value 例如 '50px'
     * @return $this
     */
    public function labelWidth(string $value)
    {
        $this->attr(__FUNCTION__, $value);
        return $this;
    }

    /**
     * 表单域标签的宽度
     * @return $this right/left/top
     */
    public function labelPosition()
    {
        $this->attr(__FUNCTION__, $value);
        return $this;
    }

    /**
     * 尺寸
     * @param string $value medium / small / mini
     * @return $this
     */
    public function size(string $value)
    {
        $this->attr(__FUNCTION__, $value);
        return $this;
    }

    /**
     * 设置提交url
     * @param string $sumbitUrl 提交url
     * @return $this
     */
    public function submitUrl(string $sumbitUrl)
    {
        $this->attr(__FUNCTION__, $sumbitUrl);
        return $this;
    }

    /**
     * 添加内容
     * @param mixed $content
     * @param string $name 插槽名称默认即可default
     * @return static
     */
    public function content($content, $name = 'default')
    {
        if ($content instanceof Field && $content->bindAttr('modelValue')) {
            $this->valueModel($content);
        }
        return parent::content($content, $name);
    }

    /**
     * 绑定值到form
     * @param $component 组件
     * @param null $data
     */
    private function valueModel($component, $data = null)
    {
        foreach ($component->bindAttribute as $attr => $field) {
            $value = $this->getData($field, $data);
            if (!empty($value)) {
                $component->bind($field, $value);
            }
        }
        foreach ($component->bindAttribute as $attr => $field) {
            $value = $this->getData($field, $data);
            if(is_null($value) && ($component instanceof DatePicker || $component instanceof TimePicker) && $startField = $component->bindAttr('startField')){
                $value = [];
            }
            $defaultValue = $component->getDefault();
            $componentValue = $component->getValue();
            //设置default缺省值
            if (empty($value) && $value !== 0 && !is_null($defaultValue)) {
                $value = $defaultValue;
                $value = $this->getPickerValue($component, $field, $value);
            }
            //value固定值
            if (!is_null($componentValue)) {
                $value = $componentValue;
                $value = $this->getPickerValue($component, $field, $value);
            }
            $this->setData($field, $value);
            if (is_null($data)) {
                $component->bindAttr($attr, $this->bindAttr('model') . '.' . $field);
            }
            $component->removeBind($field);
        }
    }

    /**
     * 获取时间value特殊处理
     * @param $component 组件
     * @param $field 字段
     * @param $componentValue 值
     * @return mixed
     */
    private function getPickerValue($component, $field, $componentValue)
    {
        $value = $componentValue;
        if ($component instanceof DatePicker || $component instanceof TimePicker) {
            $startField = $component->bindAttr('startField');
            $endField = $component->bindAttr('endField');
            if ($field == $startField && isset($componentValue[0])) {
                $value = $componentValue[0];
            }
            if ($field == $endField && isset($componentValue[1])) {
                $value = $componentValue[1];
            }
        }
        return $value;
    }

    /**
     * 选项卡布局
     * @param $title 标题
     * @param \Closure $closure
     * @return $this
     */
    public function tab($title, \Closure $closure)
    {
        if (!$this->tab) {
            $this->tab = Tabs::create();
            $this->content($this->tab);
        }
        $this->formItem = [];
        $this->itemBool = false;
        call_user_func_array($closure, [$this, $this->tab]);
        $this->itemBool = true;
        $tabPane = new TabPane();
        $tabPane->label($title);
        foreach ($this->formItem as $item) {
            $tabPane->content($item);
        }
        $this->tab->content($tabPane);
        return $this;
    }

    /**
     * 添加item
     * @param string $prop 字段
     * @param string $label 标签
     * @return FormItem
     */
    public function item($prop = '', $label = '')
    {
        $item = FormItem::create($prop, $label, $this);
        if ($this->itemBool) {
            $this->content($item);
        }
        $this->formItem[] = $item;
        return $item;
    }

    /**
     * 列布局
     * @param int $span 栅格占据的列数,占满一行24
     * @param \Closure $closure
     * @return $this
     */
    public function column(int $span, \Closure $closure)
    {
        $this->formItem = [];
        $row = new Row();
        $this->itemBool = false;
        call_user_func($closure, $this);
        $this->itemBool = true;
        foreach ($this->formItem as $item) {
            $row->column($item, $span);
        }
        $this->content($row);
        return $this;
    }

    /**
     * 添加一行布局
     * @param string $title
     * @param \Closure $closure
     * @return $this
     */
    public function row(string $title, \Closure $closure)
    {
        $this->formItem = [];
        $row = new Row();
        $this->itemBool = false;
        call_user_func($closure, $this);
        $this->itemBool = true;
        $row->content("<h4 style='font-size:16px;color: #666666'>{$title}</h4>");
        foreach ($this->formItem as $item) {
            $row->column($item);
        }
        $this->content($row);
        return $this;
    }

    /**
     * 编辑
     * @param $id 主键id数据
     */
    public function edit($id)
    {
        if ($this->model) {
            $this->data = $this->model->where($this->pkField, $id)->find();
            $this->editData[$this->pkField] = $this->data[$this->pkField];
        }
        $this->isEdit = true;
    }

    /**
     * 一对多添加
     * @param $realtion 关联方法|字段
     * @param $title 标题
     * @param \Closure $closure
     */
    public function hasMany($realtion, $title, \Closure $closure)
    {
        $this->formItem = [];
        $manyItem = FormMany::create($realtion, []);
        $originItemComponent = $this->itemComponent;
        $this->itemComponent = [];
        $this->itemBool = false;
        call_user_func_array($closure, [$this]);
        $this->itemBool = true;
        $itemComponent = $this->itemComponent;
        $datas = $this->getData($realtion);
        $manyData = [];
        foreach ($itemComponent as $component) {
            $componentClone = clone $component;
            $this->valueModel($componentClone, []);
        }
        if (!$this->isEdit && empty($datas)) {
            //添加模式默认添加一条
            $datas[] = $this->editData;
        }
        $this->editData = [];
        $manyItem->attr('manyData', $this->editData);
        $manyItem->attr('field', $realtion);
        $manyItem->attr('title', $title);
        foreach ($datas as $key => $data) {
            foreach ($itemComponent as $component) {
                if (count($datas) - 1 == $key) {
                    $componentClone = $component;
                } else {
                    $componentClone = clone $component;
                }
                $this->valueModel($componentClone, $data);
            }
            $manyData[] = $this->editData;
            $this->editData = [];
        }
        $manyItem->value($manyData);
        $this->itemComponent = $originItemComponent;
        foreach ($this->formItem as $item) {
            $manyItem->content($item);
        }
        $this->content($manyItem);
        return $manyItem;
    }

    protected function formItem($name, $field, $arguments)
    {
        $label = array_pop($arguments);
        $label = $label ?? '';
        $class = "Eadmin\\component\\form\\field\\";
        $inputs = [
            'text',
            'textarea',
            'password',
            'hidden',
        ];
        $dates = [
            'date',
            'dates',
            'year',
            'month',
            'datetime',
            'datetimeRange',
            'dateRange',
        ];
        $times = [
            'time',
            'timeRange',
        ];
        $uploads = [
            'file',
            'image',
        ];
        if (in_array($name, $inputs)) {
            $class .= 'Input';
        } elseif (in_array($name, $dates)) {
            $class .= 'DatePicker';
        } elseif (in_array($name, $times)) {
            $class .= 'TimePicker';
        } elseif (in_array($name, $uploads)) {
            $class .= 'Upload';
        } elseif ($name == 'radio') {
            $class .= 'RadioGroup';
        } elseif ($name == 'checkbox') {
            $class .= 'CheckboxGroup';
        } elseif ($name == 'switch') {
            $class .= 'Switchs';
        } else {
            $class .= ucfirst($name);
        }
        $prop = $field;
        $component = $class::create($field);
        $compenentArr = array_merge($inputs, $dates, $times);
        if ($name == 'image') {
            //图片组件
            $component->displayType('image')->imageExt()->size(120, 120)->isUniqidmd5();
        }
        if (in_array($name, $compenentArr)) {
            //由于element时间范围字段返回是一个数组,这里特殊绑定处理成2个字段
            if ($name == 'dateRange' || $name == 'datetimeRange' || $name == 'timeRange') {
                $component = $class::create();
                $component->rangeField($field, $arguments[0]);
                $component->startPlaceholder('请选择开始' . $label . '时间');
                $component->endPlaceholder('请选择结束' . $label . '时间');
                $prop = $component->bindAttr('modelValue');
            }
            $component->type($name);
        }
        if ($name == 'hidden') {
            //隐藏域
            $this->content($component);
        } else {
            if ($component instanceof Input) {
                $component->placeholder('请输入' . $label);
            } elseif ($component instanceof Select) {
                $component->placeholder('请选择' . $label);
            }

            $this->item($prop, $label)->content($component);
        }
        return $component;
    }

    /**
     * 表单操作定义
     * @param \Closure $closure
     */
    public function actions(\Closure $closure)
    {
        call_user_func_array($closure, [$this->actions]);
    }

    public function setItemComponent($component)
    {
        $this->itemComponent[] = $component;
    }

    /**
     * 提交成功事件
     * @param array $value
     * @return $this
     */
    public function eventSuccess(array $value)
    {
        $this->event('success', $value);
        return $this;
    }

    public function __call($name, $arguments)
    {
        return $this->formItem($name, $arguments[0], array_slice($arguments, 1));
    }
    public function popItem(){
        $item =  array_pop($this->content['default']);
        return $item;
    }
    /**
     * 解析组件
     */
    protected function parseComponent()
    {
        foreach ($this->itemComponent as $component) {
            //各个组件绑定值赋值
            $this->valueModel($component);
        }
        $field = $this->bindAttr('model');
        //将值绑定到form
        $this->bind($field, $this->editData);
    }

    public function jsonSerialize()
    {
        $this->parseComponent();
        $this->actions->render();
        return parent::jsonSerialize(); // TODO: Change the autogenerated stub
    }
}
