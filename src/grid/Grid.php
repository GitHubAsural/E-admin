<?php


namespace Eadmin\grid;


use Eadmin\Admin;
use Eadmin\component\basic\Button;
use Eadmin\component\basic\Html;
use Eadmin\component\basic\Image;
use Eadmin\component\basic\Router;
use Eadmin\component\Component;
use Eadmin\component\grid\Column;
use Eadmin\component\grid\Pagination;
use Eadmin\component\layout\Content;
use Eadmin\contract\GridInterface;
use Eadmin\detail\Detail;
use Eadmin\form\Form;
use Eadmin\grid\excel\Csv;
use Eadmin\grid\excel\Excel;
use Eadmin\traits\CallProvide;
use think\db\Query;
use think\facade\Request;
use think\helper\Arr;
use think\helper\Str;
use think\Model;

/**
 * 表格
 * Class Grid
 * @package Eadmin\grid
 * @method $this size(string $size) Radio的尺寸，仅在border为真时有效 medium / small / mini
 * @method $this scroll(array $height) { x: number | true, y: number }
 * @method $this stripe(bool $bool = true) 是否为斑马纹
 * @method $this bordered(bool $bool = true) 是否展示外边框和列边框
 * @method $this fit(bool $bool) 列的宽度是否自撑开
 * @method $this quickSearch(bool $bool = true) 快捷搜索
 * @method $this quickSearchText(string $string) 快捷提示文本内容
 * @method $this hideDeleteButton(bool $bool = true) 隐藏删除按钮
 * @method $this hideTrashed(bool $bool = true) 隐藏回收站
 * @method $this hideTools(bool $bool = true) 隐藏工具栏
 * @method $this hideSelection(bool $bool = true) 隐藏选择框
 * @method $this hideDeleteSelection(bool $bool = true) 隐藏删除选中按钮
 * @method $this expandFilter(bool $bool = true) 展开筛选
 * @method $this defaultExpandAllRows(bool $bool) 是否默认展开所有行
 * @method $this defer(bool $bool) 延迟渲染表格
 * @method $this expandRowByClick(bool $bool) 通过点击行来展开子行
 * @method $this showHeader(bool $bool = true) 是否显示表头
 * @method $this loadDataUrl(string $value) 设置加载数据url
 * @method $this params(array $value) 加载数据附加参数
 * @property Filter $filter
 */
class Grid extends Component
{
    use CallProvide;

    protected $name = 'EadminGrid';

    protected $column = [];

    protected $pagination;
    //是否隐藏分页
    protected $hidePage = false;
    //操作列
    protected $actionColumn;
    //是否隐藏操作列
    protected $hideAction = false;
    //是否隐藏添加按钮
    protected $hideAddButton = false;
    //查询过滤
    protected $filter = null;

    //是否开启树形表格
    protected $isTree = false;

    //树形上级id
    protected $treeParent = 'pid';
    protected $treeId = 'id';

    protected $drive;

    //导出文件名
    protected $exportFileName = null;

    protected $formAction = null;

    protected $detailAction = null;
    //删除前回调
    protected $beforeDel = null;
    //更新前回调
    protected $beforeUpdate = null;
    //工具栏
    protected $tools = [];
    //展开行
    protected $expandRow = null;
    //初始化
    protected static $init = null;

    public function __construct($data)
    {
        if ($data instanceof Model) {
            $this->drive = new \Eadmin\grid\drive\Model($data);
        } elseif ($data instanceof GridInterface) {
            $this->drive = $data;
        } else {
            $this->drive = new \Eadmin\grid\drive\Arrays($data);
        }


        $this->hideTrashed(!$this->drive->trashed());
        //分页初始化
        $this->pagination = new Pagination();
        $this->pagination->pageSize(20)->background();
        //操作列
        $this->actionColumn = new Actions($this);
        $this->bindAttValue('modelValue', false, true);
        $this->attr('eadmin_grid', $this->bindAttr('modelValue'));
        $this->scroll(['x' => true]);
        $this->attr('locale', ['emptyText' => '暂无数据']);
        $this->loadDataUrl('eadmin.rest');
        $this->getCallMethod();
        $this->params($this->getCallParams());
        $this->bind('eadmin_description', '列表');
        if (!is_null(self::$init)) {
            call_user_func(self::$init, $this);
        }
    }

    /**
     * 设置标题
     * @param string $title
     * @return string
     */
    public function title(string $title)
    {
        return $this->bind('eadmin_title', $title);
    }
    /**
     * 头部
     * @param $header
     */
    public function header($header){
        if (is_string($header)) {
            $header = Html::create()->content($header);
        } elseif (is_array($header)) {
            $html = Html::create();
            foreach ($header as $item) {
                $html->content($item);
            }
            $header = $html;
        }
        //头部
        $this->attr('header', $header);
    }
    /**
     * 表格模式
     */
    public function tableMode(){
        $this->hideTools();
        $this->hideAction();
        $this->hidePage();
        $this->hideSelection();
    }
    /**
     * 展开行
     * @param \Closure $closure
     * @param bool $defaultExpandAllRow 默认是否展开
     */
    public function expandRow(\Closure $closure, bool $defaultExpandAllRow = false)
    {
        $this->expandRow = $closure;
        $this->attr('expandedRow', true);
        $this->attr('defaultExpandAllRows', $defaultExpandAllRow);
    }

    /**
     * 头像昵称咧
     * @param string $avatar 头像
     * @param string $nickname 昵称
     * @param string $label 标签
     * @return Column
     */
    public function userInfo($avatar = 'headimg', $nickname = 'nickname', $label = '会员信息')
    {
        $column = $this->column($nickname, $label);
        return $column->display(function ($val, $data) use ($column, $avatar) {
            $avatarValue = Arr::get($data,$avatar);
            $image = Image::create()
                ->fit('cover')
                ->attr('style', ['width' => '80px', 'height' => '80px', "borderRadius" => '50%'])
                ->previewSrcList([$avatarValue])->src($avatarValue);
            return Html::create()->content($image)->content("<br>{$val}");
        })->align('center');
    }

    public function formAction()
    {
        return $this->formAction;
    }

    public function detailAction()
    {
        return $this->detailAction;
    }

    /**
     * 设置from表单
     * @param Form $form
     */
    public function setForm(Form $form)
    {
        $this->formAction = new ActionMode();
        $this->formAction->form($form);
        return $this->formAction;
    }

    /**
     * 设置detail详情
     * @param Detail $detail
     */
    public function setDetail($detail)
    {
        $this->detailAction = new ActionMode();
        $this->detailAction->detail($detail);
        return $this->detailAction;
    }

    public function drive()
    {
        return $this->drive;
    }

    /**
     * 获取当前模型
     * @return \think\Db
     */
    public function model()
    {
        return $this->drive->db();
    }

    /**
     * 查询过滤
     * @param mixed $callback
     */
    public function filter($callback)
    {
        if ($callback instanceof \Closure) {
            call_user_func($callback, $this->getFilter());
        }
    }

    public function getFilter()
    {
        if (is_null($this->filter)) {
            $this->filter = new Filter($this->drive->db());
        }
        return $this->filter;
    }

    //更新前回调
    public function updateing(\Closure $closure)
    {
        $this->beforeUpdate = $closure;
    }

    //删除前回调
    public function deling(\Closure $closure)
    {
        $this->beforeDel = $closure;
    }

	/**
	 * 删除
	 * @param int $id 删除的id
	 * @return bool|int
	 */
	public function destroy($id)
	{
		$trueDelete = Request::delete('trueDelete');
		if (!is_null($this->beforeDel)) {
			call_user_func($this->beforeDel, $id, $trueDelete);
		}
		return $this->drive->destroy($id);
	}

    /**
     * 更新
     * @param array $ids 更新的id
     * @param array $data 更新的数组
     * @return mixed
     */
    public function update($ids, $data)
    {
        if (!is_null($this->beforeUpdate)) {
            call_user_func($this->beforeUpdate, $ids, $data);
        }
        return $this->drive->update($ids, $data);
    }

    /**
     * 开启导出
     * @param string $fileName 导出文件名
     */
    public function export($fileName = '')
    {
        $this->attr('export', true);
        $this->attr('Authorization', rawurlencode(Admin::token()->get()));
        $this->exportFileName = empty($fileName) ? date('YmdHis') : $fileName;
    }

    /**
     * 拖拽排序
     * @param string $field 排序字段
     * @param string $label 标题
     */
    public function sortDrag($field = 'sort', $label = '排序')
    {
        $this->drive->sortField($field);
        $column = $this->column($field, $label)
            ->attr('slots', ['title' => $field, 'customRender' => 'sortDrag'])
            ->width(50)->align('center');
        return $column;
    }

    /**
     * 输入框排序
     * @param string $field 排序字段
     * @param string $label 标题
     * @return Column
     */
    public function sortInput($field = 'sort', $label = '排序')
    {
        $this->drive->sortField($field);
        $column = $this->column($field, $label)
            ->attr('slots', ['title' => $field, 'customRender' => 'sortInput'])
            ->width(100)->align('center');
        return $column;
    }

    /**
     * 开启树形表格
     * @param string $pidField 父级字段
	 * @param string $idField 下级字段
     * @param bool $expand 是否展开
     */
    public function treeTable($pidField = 'pid', $idField = 'id', $expand = true)
    {
        $this->treeParent = $pidField;
        $this->treeId = $idField;
        $this->isTree = true;
        $this->hidePage();
        $this->defaultExpandAllRows($expand);
    }

    /**
     * 操作列定义
     * @param \Closure $closure
     */
    public function actions(\Closure $closure)
    {
        $this->actionColumn->setClosure($closure);
    }

    /**
     * 隐藏添加按钮
     * @param bool $bool
     */
    public function hideAddButton(bool $bool = true)
    {
        $this->hideAddButton = $bool;
    }

    /**
     * 隐藏操作列
     * @param bool $bool
     */
    public function hideAction(bool $bool = true)
    {
        $this->hideAction = $bool;
    }

    public function tools($tools)
    {
        if (is_string($tools)) {
            $this->tools[] = Html::create()->content($tools);
        } elseif (is_array($tools)) {
            foreach ($tools as $tool) {
                $this->tools($tool);
            }
        } elseif ($tools instanceof Component) {
            $this->tools[] = $tools;
        }
        return $this;
    }

    /**
     * 关闭分页
     * @param bool $bool
     */
    public function hidePage(bool $bool = true)
    {
        $this->hidePage = $bool;
    }

    /**
     * 分页组件
     * @return Pagination
     */
    public function pagination()
    {
        return $this->pagination;
    }

    /**
     * 设置分页每页限制
     * @Author: rocky
     * 2019/11/6 14:01
     * @param int $limit
     */
    public function setPageLimit(int $limit)
    {
        $this->pagination->pageSize($limit);
    }

    /**
     * 初始化
     * @param \Closure $closure
     */
    public static function init(\Closure $closure)
    {
        self::$init = $closure;
    }

    /**
     * 添加表格列
     * @param string $field 字段
     * @param string $label 显示的标题
     * @return Column
     */
    public function column(string $field = '', string $label = '')
    {
        $column = new Column($field, $label, $this);
        $this->column[] = $column;
        $this->drive->realiton($field);
        return $column;
    }

    /**
     * 解析列返回表格数据
     * @param array $datas 数据源
     * @param bool $export
     * @return array
     */
    protected function parseColumn($datas, $export = false)
    {

        //添加操作列
        if (!$this->hideAction) {
            $this->column[] = $this->actionColumn->column();
        }
        $tableData = [];
        //解析行数据
        foreach ($datas as $data) {
            //主键
            $row = ['id' => $data[$this->drive->getPk()]];
            //树形父级pid
            if ($this->isTree) {
                $row[$this->treeId] = $data[$this->treeId];
                $row[$this->treeParent] = $data[$this->treeParent];
            }
            foreach ($this->column as $column) {
                $field = $column->attr('prop');
                $row[$field] = $column->row($data);
                if ($export) {
                    $row[$field] = $column->getExportData();
                }
            }
            if (!$this->hideAction && !$export) {
                $actionColumn = clone $this->actionColumn;
                $actionColumn->row($data);
                $row['EadminAction'] = $actionColumn;
            }
            if (!is_null($this->expandRow)) {
                $expandRow = call_user_func($this->expandRow, $data);
                $row['EadminExpandRow'] = Html::create($expandRow);
            }
            $tableData[] = $row;
        }
        $isTotal  = false;
        $row = ['id'=>-1];
        foreach ($this->column as $column) {
            $total = $column->getTotal();
            $field = $column->attr('prop');
            if($total === false){
                $row[$field] = '';
            }else{
                $isTotal = true;
                $row[$field] = Html::create($total);
            }
        }
        if($isTotal && !$export){
            $tableData[] = $row;
        }
        return $tableData;
    }

    /**
     * 导出数据
     */
    public function exportData()
    {
        //快捷搜索
        $keyword = Request::get('quickSearch', '', ['trim']);
        $this->drive->quickFilter($keyword, $this->column);

        foreach ($this->column as $column) {
            $field = $column->attr('prop');
            $label = $column->attr('label');
            if (!$column->attr('closeExport')) {
                $columnTitle[$field] = $label;
            }
        }
        if (is_callable($this->exportFileName)) {
            $excel = new Excel();
            $excel->file(date('YmdHis'));
            $excel->callback($this->exportFileName);
        } else {
            $excel = new Csv();
            $excel->file($this->exportFileName);
        }
        $excel->columns($columnTitle);
        if (Request::get('export_type') == 'all') {
            set_time_limit(0);
            if ($excel instanceof Excel) {
                $data = $this->drive->db()->select();
                $exportData = $this->parseColumn($data, true);
                $excel->rows($exportData)->export();
            } else {
                $this->drive->db()->chunk(500, function ($datas) use ($excel) {
                    $exportData = $this->parseColumn($datas, true);
                    $excel->rows($exportData)->export();
                    $this->exportData = [];
                });
                exit;
            }
        } elseif (Request::get('export_type') == 'select') {
            $data = $this->drive->model()->whereIn($this->drive->getPk(), Request::get('eadmin_ids'))->select();
        } else {
            $page = Request::get('page', 1);
            $size = Request::get('size', (int)$this->pagination->attr('pageSize'));
            $data = $this->drive->getData($this->hidePage, $page, $size);
        }
        $exportData = $this->parseColumn($data, true);
        $excel->rows($exportData)->export();
        exit;
    }

    public function jsonSerialize()
    {
        //添加按钮
        if (!$this->hideAddButton && !is_null($this->formAction)) {
            $form = $this->formAction->form();
			$callMethod = $form->getCallMethod() + $form->getCallParams();
            $form->eventSuccess([$this->bindAttr('modelValue') => true, $form->bindAttr('model') => $callMethod]);
            $button = Button::create('添加')
                ->type('primary')
                ->size('small')
                ->icon('el-icon-plus');
            $action = clone $this->formAction->component();
            if ($action instanceof Html) {
                $button = $action->content($button)->redirect("eadmin/create.rest", ['eadmin_description' => '添加'] + $callMethod);
            } else {
                $button = $action->bindValue(false)->reference($button)->title('添加')->form($form);
            }
            //添加权限
            $action->auth($callMethod['eadmin_class'],$callMethod['eadmin_function'],'post');
            $this->attr('addButton', $button);
        }
        //删除权限
        if(!Admin::check($this->callClass,$this->callFunction,'delete')){
           $this->hideDeleteButton();
           $this->hideDeleteSelection();
        }
        //工具栏
        $this->attr('tools', $this->tools);
        //快捷搜索
        $keyword = Request::get('quickSearch', '', ['trim']);
        $this->drive->quickFilter($keyword, $this->column);
        //查询视图
        if (!is_null($this->filter)) {
            $form = $this->filter->render();
            $form->eventSuccess([$this->bindAttr('modelValue') => true]);
            //排除筛选多余字段
            $this->attr('filterExceptField', $form->attr('exceptField'));
            $this->attr('filter', $form);
            $this->attr('filterField', $form->bindAttr('model'));
        }
        //总条数
        $this->pagination->total($this->drive->getTotal());
        //是否分页
        $page = Request::get('page', 1);
        $size = Request::get('size', $this->pagination->attr('pageSize'));
        if (!$this->hidePage) {
            $this->attr('pagination', $this->pagination->attribute);
        }
        //排序
        if (Request::has('eadmin_sort_field')) {
            $this->drive->db()->removeOption('order')->order(Request::get('eadmin_sort_field'), Request::get('eadmin_sort_by'));
        }


        $data = $this->drive->getData($this->hidePage, $page, $size);
        //解析列
        $data = $this->parseColumn($data);

        //树形
        if ($this->isTree) {
            $data = Admin::tree($data, $this->treeId, $this->treeParent);
        }
        $this->bindAttValue('data', $data);
        if (request()->has('ajax_request_data')) {
            return [
                'code' => 200,
                'data' => $data,
                'header'=> $this->attr('header'),
                'tools'=> $this->attr('tools'),
                'columns' => array_column($this->column, 'attribute'),
                'total' => $this->pagination->attr('total')
            ];
        } else {
            $params = (array)$this->attr('params');
            $this->params(array_merge($params, $this->getCallMethod()));
            $this->attr('columns', array_column($this->column, 'attribute'));
            return parent::jsonSerialize(); // TODO: Change the autogenerated stub
        }
    }
}
