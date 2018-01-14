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

	private $pager = null;
	
	public function __construct() {
		$page = Input::request('page')->intval();
		$this->pager = new Pager();
		$this->pager->pageSize = 20;
		$this->pager->style = Pager::Bootstrap;
		$this->pager->url = '?page={page}';
		foreach ($_GET as $k => $v) {
			if ($k === 'page') {
				$this->pager->page = intval($v);
			} else if (strlen($k) > 2 && substr($k, 0, 2) == 's_') {
				$qk = substr($k, 2);
				$this->querys[$qk] = $v;
			}
		}
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

	public function newTextField($name, $key, $width=0) {
		return new TextField($name, $key, $width);
	}

	public function newLinkField($name, $key, $url, $width=0) {
		return new LinkField($name, $key, $url, $width);
	}

	public function newImageField($name, $key, $width=0) {
		return new ImageField($name, $key, $width);
	}

	public function newSelectSearch($key, $values) {
		return new SelectSearch($key, $values);
	}

	public function newTextSearch($key, $placeholder) {
		return new TextSearch($key, $placeholder);
	}

	public function newLinkButton($name, $url) {
		return new LinkButton($name, $url);
	}

	public function newButtonGroup($buttons=[]) {
		return new ButtonGroup($buttons);
	}

	public function addOperation($operations) {
		$this->operations[] = $operations;
		return $this;
	}

	public function newLinkOperation($name='', $url='') {
		return new LinkOperation($name, $url);
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
		if (count($this->searchs) > 0) {
			$search = '<form class="search form-inline right">'.$nl;
			foreach ($this->searchs as $item) {
				$sk = $item->key;
				if (isset($this->querys[$sk])) {
					$item->value($this->querys[$sk]);
				}
				$search .= $item->render();
			}
			$search .= '<button type="submit" class="btn">搜索</button>';
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
		$list = '<table class="table table-striped table-bordered table-hover">'.$nl;
		$list .= '<thead>'.$nl;
		$list .= '<tr class="table-heading">'.$nl;
		foreach ($this->fields as $field) {
			$widthAttr = $field->width == 0 ? '' : ' width="' . $field->width .'"';
			$list .= "<th{$widthAttr}>{$field->name}</th> \n";
		}
		if (count($this->operations) > 0) {
			$list .= "<th width=\"*\">操作</th> \n";
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
				$alignStyle = '';
				if ($field->textAlign != '') {
					$alignStyle = ' style="text-align:' . $field->textAlign . '"';
				}
				$list .= "<td{$alignStyle}> \n" . $field->render() . " \n </td>".$nl;
			}
			if (count($this->operations) > 0) {
				$list .= "<td> \n";
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
		$pagination = '<div class="pagination">'.$nl;
		$pagination .= '<ul><li>'.$nl;
		$pagination .= "<span style=\"width:88px\">共 <span style=\"color:red\">{$this->pager->totalCount}</span> 条记录</span>".$nl;
		$pagination .= '</li></ul>'.$nl;
		$pagination .= $this->pager->pagecute().$nl;
		$pagination .= '</div>'.$nl;
				

		return [
			'search' => $search,
			'buttons' => $buttons,
			'list' => $list,
			'pagination' => $pagination
 		];
	}

	public static function getAdminUrl($url) {
		if ($url{0} != '#' && (strlen($url) < 4 || substr($url, 0, 4) != 'http')) {
			$adminUrl = Config::baseUrl();
			if ($url{0} == '/') {
				$url = substr($url, 0, 1);
			}
			$url = $adminUrl.$url;
		}
		return $url;
	}

	public static function getImageUrl($url, $format='') {
		if (strlen($url) < 4 || substr($url, 0, 4) != 'http') {
			$url = Config::get('url.cdn').$url;
			if ($format != '') {
				$url .= '-'.$format.'.jpg';
			}
		}
		return $url;
	}

}
