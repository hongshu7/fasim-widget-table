<?php
namespace Fasim\Widget\Table;

use Fasim\Facades\Config;
use Fasim\Facades\Input;
use Fasim\Library\Pager;


class LinkOperation {
	public $name;
	public $url;
	public $data;
	public $classes = [];
	public $attrs = [];
	private $callback = null;

	public function __construct($name='', $url='') {
		$this->name = $name;
		$this->url = $url;
	}

	public function name($name) {
		$this->name = $name;
		return $this;
	}

	public function url($url) {
		$this->url = $url;
		return $this;
	}

	public function callback($callback) {
		$this->callback = $callback;
		return $this;
	}

	public function confirm($confirm) {
		return $this->className('confirm-link')->attr('data-confirm', $confirm);
	}

	public function className($name) {
		$this->classes[] = $name;
		return $this;
	}

	public function attr($name, $value) {
		$this->attrs = [$name => $value];
		return $this;
	}

	public function getData($key) {
		if ($this->data == null) {
			return '';
		}
		$keys = explode('.', $key);
		$v = $this->data;
		while (count($keys) > 0) {
			$k = array_shift($keys);
			if (is_object($v) && isset($v->$k)) {
				return $v->$k;
			} else if (is_array($v) && isset($v[$k])) {
				return $v[$k];
			} else {
				$v = '';
			}
		}
		return $v;
	}

	public function filterValue($v) {
		return preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return $this->getData($matches[1]);
		}, $v);
	}

	public function render() {
		if ($this->callback != null && is_callable($this->callback)) {
			$callback = $this->callback;
			$result = $callback($this->data);
			if (is_array($result)) {
				list($this->name, $this->url, $this->attrs) = $result;
				if (empty($this->attrs)) {
					$this->attrs = [];
				}
			} else if (is_string($result)) {
				$this->name = $result;
			}
		}
		$url = TableBuilder::getUrl($this->url);
		$url = $this->filterValue($url);
		
		$attrs = $this->attrs;
		$attrAppend = '';
		if (count($this->classes) > 0) {
			$old = isset($attrs['class']) ? $attrs['class'].' ' : '';
			$attrs['class'] = $old.implode(' ', $this->classes);
		}
		if (count($attrs) > 0) {
			foreach ($attrs as $an => $av) {
				$attrAppend .= ' ' . $an . '="' . $this->filterValue($av) . '"';
			}
		}
		
		
		return " <a href=\"{$url}\"{$attrAppend}>{$this->name}</a> ";
	}
}
