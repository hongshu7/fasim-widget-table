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

	private $querys = [];

	private $baseUrl = '';
	private $imageUrl = '';
	private $pager = null;

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

	public function addOperation($operations) {
		$this->operations[] = $operations;
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
			$search .= '<button type="submit" class="btn btn-default">搜索</button>';
			$search .= '</form>'.$nl;
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
	

		//list
		$list = '<table class="'.$this->tableClass.'">'.$nl;
		$list .= '<thead>'.$nl;
		$list .= '<tr>'.$nl;
		foreach ($this->fields as $field) {
			$widthAttr = $field->width == 0 ? '' : ' width="' . $field->width .'"';
			$textAlign = $this->defaultAlign;
			if ($field->textAlign != '') {
				$textAlign = $field->textAlign;
			}
			$alignStyle = ' style="text-align:' . $textAlign . '"';
			$list .= "<th{$widthAttr}{$alignStyle}>{$field->name}</th> \n";
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

		//pagination
		$pageUrl = $this->pager->url;
		$queryStr = '';
		if (count($querys) > 0) {
			$queryStr = http_build_query($querys);
		}
		$pageUrl = str_replace('&{querys}', '{querys}', $pageUrl);
		$pageUrl = str_replace('?{querys}', '{querys}', $pageUrl);
		$pageUrl = str_replace('{querys}', $queryStr, $pageUrl);
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
		$pagination = '<nav>'.$nl;
		$pagination .= '<ul class="pagination"><li>'.$nl;
		$pagination .= "<span>共 <i style=\"color:red;font-style:normal;\">{$this->pager->totalCount}</i> 条记录</span>".$nl;
		$pagination .= '</li></ul>'.$nl;
		$pagination .= $this->pager->pagecute().$nl;
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
