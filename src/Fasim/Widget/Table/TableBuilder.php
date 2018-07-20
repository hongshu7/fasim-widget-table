<?php
namespace Fasim\Widget\Table;

use Fasim\Facades\Config;
use Fasim\Facades\Input;
use Fasim\Library\Pager;

class TableBuilder {
	private $fields = [];
	private $data = [];
	private $operations = [];
	private $searchs = [];
	private $buttons = [];
	private $actions = [];
	
	public $sortBy = '';
	public $sortOrder = 'ASC';

	private $querys = [];

	private $baseUrl = '';
	private $imageUrl = '';
	private $actionUrl = '/action';
	private $pager = null;

	private $idKey = 'id';
	private $formClass = 'search form-inline right';
	private $tableClass = 'table table-striped table-bordered table-hover';
	private $defaultAlign = 'left';
	private $titleAlign = '';
	private $operationAlign = '';

	private static $instance = null;
	
	public function __construct() {
		$page = Input::request('page')->intval();

		$this->pager = new Pager();
		$this->pager->pageSize = 20;
		$this->pager->style = Pager::Bootstrap;
		$this->pager->url = '{querys}&page={page}';
		foreach ($_GET as $k => $v) {
			$this->querys[$k] = $v;
		}
		if (isset($this->querys['page'])) {
			$this->pager->page = intval($this->querys['page']);
		}
		if (isset($this->querys['sortby'])) {
			$this->sortBy = trim($this->querys['sortby']);
		}
		if (isset($this->querys['sortorder'])) {
			$this->sortOrder = strtoupper(trim($this->querys['sortorder']));
			if ($this->sortOrder != 'DESC') {
				$this->sortOrder = 'ASC';
			}
		}

		$this->baseUrl = Config::baseUrl();
		$this->imageUrl = $this->setImageUrl(Config::get('url.cdn'));

		if (self::$instance == null) {
			self::$instance = $this;
		}
	}

	public function setBaseUrl($url) {
		return $this->baseUrl($url);
	}

	public function setImageUrl($url) {
		return $this->imageUrl($url);
	}

	public function baseUrl($url) {
		$this->baseUrl = $url;
		return $this;
	}

	public function imageUrl($url) {
		$this->imageUrl = $url;
		if ($this->imageUrl == '' || substr($this->imageUrl, -1) != '/') {
			$this->imageUrl .= '/';
		}
		return $this;
	}

	public function idKey($idKey) {
		$this->idKey = $idKey;
		return $this;
	}

	public function actionUrl($actionUrl) {
		$this->actionUrl = $actionUrl;
		return $this;
	}

	public function sort($by, $order='ASC') {
		if ($this->sortBy == '') {
			$this->sortBy = $by;
			$this->sortOrder = strtoupper($order);
			if ($this->sortOrder != 'DESC') {
				$this->sortOrder = 'ASC';
			}
		}
		return $this;
	}

	public function tableClass($tableClass) {
		$this->tableClass = $tableClass;
	}

	public function formClass($formClass) {
		$this->formClass = $formClass;
	}

	public function defaultAlign($defaultAlign) {
		$this->defaultAlign = $defaultAlign;
		return $this;
	}

	public function operationAlign($operationAlign) {
		$this->operationAlign = $operationAlign;
		return $this;
	}

	public function addField($field) {
		$this->fields[] = $field;
		return $this;
	}

	public function addSearch($search) {
		$this->searchs[] = $search;
		return $this;
	}
	public function addButton($button) {
		$this->buttons[] = $button;
		return $this;
	}

	public function addOperation($operation) {
		$this->operations[] = $operation;
		return $this;
	}

	public function addAction($action) {
		$this->actions[] = $action;
		return $this;
	}

	public function data($data) {
		$this->data = $data;
		return $this;
	}

	public function query($key = '') {
		if ($key != '') {
			return $this->querys[$key];
		}
		return $this->querys;
	}

	public function page() {
		return $this->pager->page;
	}

	public function totalCount($totalCount = -1) {
		if ($totalCount == -1) {
			return $this->pager->totalCount;
		}
		$this->pager->totalCount = $totalCount;
		return $this;
	}

	public function pageUrl($url) {
		$this->pager->url = $this->getAdminUrl($url);
		return $this;
	}

	public function pageSize($pageSize = -1) {
		if ($pageSize == -1) {
			return $this->pager->pageSize;
		}
		$this->pager->pageSize = $pageSize;
		return $this;
	}


	public function build() {
		$nl = " \n";

		//search
		$search = '';
		$querys = [];
		if (count($this->searchs) > 0) {
			$search = '<form class="'.$this->formClass.'">'.$nl;
			foreach ($this->searchs as $item) {
				$sk = $item->key;
				if (isset($this->querys[$sk])) {
					$item->value($this->querys[$sk]);
					$querys[$sk] = $this->querys[$sk];
				}
				$search .= $item->render();
			}
			$search .= '<input type="hidden" name="pageSize" value="'.$this->pager->pageSize.'" />'.$nl;
			$search .= '<button type="submit" class="btn btn-default">搜索</button>';
			$search .= '</form>'.$nl;

			$querys['pageSize'] = $this->pager->pageSize;
		}

		//buttons
		$buttons = '';
		if (count($this->buttons) > 0) {
			$buttons = '<div class="btn-toolbar">'.$nl;
			foreach ($this->buttons as $button) {
				$buttons .= $button->render();
			}
			$buttons .= '</div>'.$nl;
		}
		
		$requestUrl = $_SERVER['REQUEST_URI'];
		$sortUrl = preg_replace('/[\?&](page|sortby|sortorder)=\w*/i', '', $requestUrl);
		$sortUrl .= (strpos($sortUrl, '?') === false ? '?' : '&') . 'sortby=';

		$hasActions = count($this->actions) > 0;
		if ($hasActions) {
			$actionUrl = TableBuilder::getUrl($this->actionUrl);
			//if ($actionUrl)
			$list .= '<form action="'.$actionUrl.'" method="post">'.$nl;
		}
		$list .= '<table class="'.$this->tableClass.'">'.$nl;
		$list .= '<thead>'.$nl;
		$list .= '<tr>'.$nl;
		if ($hasActions) {
			$list .= '<th width="30" style="text-align:center;"><input type="checkbox" id="checkall" value="" /></th>'.$nl;
		}
		foreach ($this->fields as $field) {
			$widthAttr = $field->width == 0 ? '' : ' width="' . $field->width .'"';
			$textAlign = $this->defaultAlign;
			if ($field->textAlign != '') {
				$textAlign = $field->textAlign;
			}
			$alignStyle = ' style="text-align:' . $textAlign . '"';
			
			$nameHtml = $field->name;
			if ($field->sortable) {
				$url = $sortUrl.$field->key;
				$order = 'ASC';
				$arrow = '';
				if ($this->sortBy == $field->key) {
					$arrow = $this->sortOrder == 'ASC' ? '↓' : '↑';
					if ($this->sortOrder == 'ASC') {
						$order = 'DESC';
					}
				}
				$url .= '&sortorder='.$order;
				$nameHtml = "<a href=\"{$url}\">".$field->name.$arrow.'</a>';
			}
			$list .= "<th{$widthAttr}{$alignStyle}>{$nameHtml}</th> \n";
		}
		if (count($this->operations) > 0) {
			$textAlign = $this->defaultAlign;
			if ($this->operationAlign != '') {
				$textAlign = $this->operationAlign;
			}
			$list .= "<th width=\"*\" style=\"text-align:{$textAlign};\">操作</th> \n";
		}
		$list .= '</tr>'.$nl;
		$list .= '</thead>'.$nl;
		$list .= '<tbody>'.$nl;
		foreach ($this->data as $row) {
			$list .= '<tr>'.$nl;
			if ($hasActions) {
				$k = $this->idKey;
				$cval = '';
				if (is_object($row) && isset($row->$k)) {
					$cval = $row->$k;
				} else if (is_array($row) && isset($row[$k])) {
					$cval = $row[$k];
				}
				$list .= '<td style="text-align:center;"><input type="checkbox" name="ids[]" value="'.$cval.'" /></td>'.$nl;
			}
			foreach ($this->fields as $field) {
				$keys = is_array($field->key) ? $field->key : [$field->key];
				$values = [];
				foreach ($keys as $key) {
					$ks = explode('.', $key);
					$v = $row;
					while (count($ks) > 0) {
						$k = array_shift($ks);
						if (is_object($v) && isset($v->$k)) {
							$v = $v->$k;
						} else if (is_array($row) && isset($v[$k])) {
							$v = $v[$k];
						} else {
							$v = '';
						}
					}
					$values[$key] = $v;
				}
				$field->value = is_array($field->key) ? $values : $values[$field->key];
				$textAlign = $this->defaultAlign;
				if ($field->textAlign != '') {
					$textAlign = $field->textAlign;
				}
				$alignStyle = ' style="text-align:' . $textAlign . '"';
				$list .= "<td{$alignStyle}> \n" . $field->render() . " \n </td>".$nl;
			}
			if (count($this->operations) > 0) {
				$textAlign = $this->defaultAlign;
				if ($this->operationAlign != '') {
					$textAlign = $this->operationAlign;
				}
				$list .= "<td style=\"text-align:{$textAlign};\"> \n";
				for ($oi = 0; $oi < count($this->operations); $oi++) {
					$opt = $this->operations[$oi];
					$opt->data = $row;
					
					if ($oi > 0) {
						$list .= " &nbsp;|&nbsp; ";
					}
					$list .= $opt->render().$nl;;
				}
				$list .= "</td> \n";
			}
			$list .= '</tr>'.$nl;
		}
		$list .= '</tbody>'.$nl;
		$list .= '</table>'.$nl;
		if ($hasActions) {
			$list .= '<div class="form-group form-actions">'.$nl;
			$list .= '批量操作： ';
			foreach ($this->actions as $action) {
				$list .= $action->render().$nl;
			}
			$list .= '</div>'.$nl;
			$list .= <<<EOT
</form>
<script>
function whenReady(callback) {
	if (typeof $ != 'undefined') {
		$(callback);
	} else {
		setTimeout(function(){ whenReady(callback); }, 10);
	}
}
whenReady(function() { 
	$('.form-actions button').click(function() {
		var count = $('input:checkbox[name^=ids]').filter(':checked').length;
		if (count == 0) {
			alert('没有选任何帖子。');
			return false;
		}
		var actionName = $(this).text().replace(/\s+/g, '');
		var result = confirm('确定要对' + count + '个帖子执行“'+actionName+'”操作吗？');
		if (result && $(this).attr('jscallback') != '') {
			var ids = [];
			$('input:checkbox[name^=ids]').filter(':checked').each(function() {
				ids.push($(this).val());
			});
			var jscallback = $(this).attr('jscallback');
			//console.log($(this).val(), ids);
			var fn = window[jscallback];
			if (typeof fn === 'function') {
				fn($(this).val(), ids);
			}
			return false;
		}
		return result;
	});
	$('#checkall').click(function() {
		var checked = $(this).prop('checked');
		$('input:checkbox[name^=ids]').prop('checked', checked);
	});
});
</script>
EOT;
		}

		//pagination
		$pageUrl = $this->pager->url;
		$queryStr = '';
		if (count($querys) > 0) {
			$queryStr = http_build_query($querys);
		}
		$pageUrl = str_replace('&{querys}', '{querys}', $pageUrl);
		$pageUrl = str_replace('?{querys}', '{querys}', $pageUrl);
		$pageUrl = str_replace('{querys}', $queryStr, $pageUrl);
		
		if ($this->sortBy != '') {
			$pageUrl .= "&sortby={$this->sortBy}&sortorder={$this->sortOrder}";
		}
		if ($pageUrl{0} == '&') {
			$pageUrl = '?'.substr($pageUrl, 1);
		}
		if ($pageUrl{0} != '?') {
			$pageUrl = '?'.$pageUrl;
		}
		$this->pager->url = $pageUrl;
		// if (isset($this->querys['page'])) {
		// 	$this->pager->page = intval($this->querys['page']);
		// }
		$pageSizeUrl = preg_replace('/[\?&]+page={page}/i', '', $pageUrl);
		$pageSizeUrl = preg_replace('/[\?&]+pageSize=\d*/i', '', $pageSizeUrl).'&page=1&pageSize=';
		if ($pageSizeUrl{0} == '&') {
			$pageSizeUrl = '?'.substr($pageSizeUrl, 1);
		}
		//echo $pageSizeUrl;exit();
		$psSelected = ' selected="selected"';
		$pagination = '<nav>'.$nl;
		$pagination .= '<ul class="pagination"><li>'.$nl;
		$pagination .= "<span>共 <i style=\"color:red;font-style:normal;\">{$this->pager->totalCount}</i> 条记录</span>".$nl;
		$pagination .= '</li></ul>'.$nl;
		$pagination .= $this->pager->pagecute().$nl;
		$pagination .= '<ul class="pagination"><li>'.$nl;
		$pagination .= '<div class="form-inline" style="float:left;margin-left:5px;">每页显示 <select name="pageSize" class="form-control" onchange="location.href=\''.$pageSizeUrl.'\' + this.value;">';
		$pagination .= '<option value="10"'.($this->pager->pageSize == 10 ? $psSelected : '').'>10条</option>';
		$pagination .= '<option value="20"'.($this->pager->pageSize == 20 ? $psSelected : '').'>20条</option>';
		$pagination .= '<option value="50"'.($this->pager->pageSize == 50 ? $psSelected : '').'>50条</option>';
		$pagination .= '<option value="100"'.($this->pager->pageSize == 100 ? $psSelected : '').'>100条</option>';
		$pagination .= '<option value="200"'.($this->pager->pageSize == 200 ? $psSelected : '').'>200条</option>';
		$pagination .= '</select></div>'.$nl;
		$pagination .= '</li></ul>'.$nl;
		$pagination .= '</nav>'.$nl;
		return [
			'search' => $search,
			'buttons' => $buttons,
			'list' => $list,
			'pagination' => $pagination
 		];
	}


	public static function newTextField($name, $key, $width=0) {
		return new TextField($name, $key, $width);
	}

	public static function newLinkField($name, $key, $url, $width=0) {
		return new LinkField($name, $key, $url, $width);
	}

	public static function newImageField($name, $key, $width=0) {
		return new ImageField($name, $key, $width);
	}

	public static function newHiddenSearch($key, $value) {
		return new HiddenSearch($key, $value);
	}

	public static function newTextSearch($key, $placeholder, $value ='') {
		return new TextSearch($key, $placeholder, $value);
	}

	public static function newSelectSearch($key, $placeholder, $options) {
		return new SelectSearch($key, $placeholder, $options);
	}

	public static function newDateSearch($key, $placeholder, $value ='') {
		return new DateSearch($key, $placeholder, $value);
	}

	public static function newLinkButton($name, $url) {
		return new LinkButton($name, $url);
	}

	public static function newButtonGroup($buttons=[]) {
		return new ButtonGroup($buttons);
	}

	public static function newLinkOperation($name='', $url='') {
		return new LinkOperation($name, $url);
	}

	public static function newAction($key='', $name='') {
		return new Action($key, $name);
	}

	public static function getUrl($url) {
		if ($url{0} != '#' && (strlen($url) < 4 || substr($url, 0, 4) != 'http')) {
			if ($url{0} == '/') {
				$url = substr($url, 1);
			}
			$url = self::$instance->baseUrl.$url;
		}
		return $url;
	}

	public static function getImageUrl($url, $format='') {
		if (strlen($url) < 4 || substr($url, 0, 4) != 'http') {
			if ($url{0} == '/') {
				$url = substr($url, 1);
			}
			$url = self::$instance->imageUrl.$url;
		}
		if ($format != '') {
			$url .= '-'.$format.'.jpg';
		}
		return $url;
	}

}
