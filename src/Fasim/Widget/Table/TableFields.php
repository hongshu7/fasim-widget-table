<?php
namespace Fasim\Widget\Table;

use Fasim\Facades\Config;
use Fasim\Facades\Input;
use Fasim\Library\Pager;

abstract class Field {
	public $name;
	public $key;
	public $value = '';
	public $width = 0;

	public function __construct($name, $key, $width = 0) {
		$this->name = $name;
		$this->key = $key;
		$this->width = $width;
	}

	public function name($name) {
		$this->name = $name;
		return $this;
	}

	public function key($key) {
		$this->key = $key;
		return $this;
	}

	abstract function render();

}

class TextField extends Field {
	public $textAlign = '';
	public $color = '';
	private $useSwitch = false;
	private $cases = [];
	private $default = '';
	private $callback = null;
	private $vars = [];

	public function textAlign($textAlign) {
		$this->textAlign = $textAlign;
		return $this;
	}

	public function color($color) {
		$this->color = $color;
		return $this;
	}

	public function valueCase($c, $v) {
		$this->useSwitch = true;
		$this->cases[] = [
			'c' => $c,
			'v' => $v
		];
		return $this;
	}

	public function valueMap($map) {
		$this->useSwitch = true;
		foreach ($map as $c => $v) {
			$this->cases[] = [
				'c' => $c,
				'v' => $v
			];
		}
		return $this;
	}

	public function valueDefault($v) {
		$this->useSwitch = true;
		$this->default = $v;
		return $this;
	}

	public function assign($k, $v) {
		$this->vars[$k] = $v;
		return $this;
	}
 
	public function callback($callback) {
		$this->callback = $callback;
		return $this;
	}

	public function render() {
		$value = $this->value;
		if ($this->useSwitch) {
			$value = $this->default;
			foreach ($this->cases as $case) {
				if ($case['c'] === $this->value) {
					$value = $case['v'];
					break;
				}
			}
		}
		if ($this->callback != null && is_callable($this->callback)) {
			$callback = $this->callback;
			$values = is_array($value) ? array_values($value) : [ $value ];
			if (count($this->vars) > 0) {
				$values[] = $this->vars;
			}
			$value = $callback(...$values);
		}
		if ($this->color != '') {
			$value = "<span style=\"color:{$this->color};\">$value</span>";
		}

		return $value;
	}

}

class LinkField extends TextField {
	public $url = '';

	public function __construct($name, $key, $url, $width = 0) {
		$this->name = $name;
		$this->key = $key;
		$this->url = $url;
		$this->width = $width;
	}

	public function url($url) {
		$this->url = $url;
		return $this;
	}
	
	public function render() {
		
	}
}

class ImageField extends TextField {
	public function render() {
		$value = parent::render();
		$url = TableBuilder::getImageUrl($value);
		return "<img src=\"$url\" style=\"width:100%;\" />";
	}
}

abstract class Search {
	public $key;
	public $value;
	public function __construct($key) {
		$this->key = $key;
		return $this;
	}
	public function key($key) {
		$this->key = $key;
		return $this;
	}
	public function value($value) {
		$this->value = $value;
		return $this;
	}
	abstract function render();
}

class TextSearch extends Search {
	public $placeholder;

	public function __construct($key, $placeholder) {
		$this->key = $key;
		$this->placeholder = $placeholder;
	}

	public function placeholder($placeholder) {
		$this->placeholder = $placeholder;
		return $this;
	}

	public function render() {
		$key = 's_'.$this->key;
		return "<input id=\"{$key}\" class=\"form-control\" name=\"{$key}\" value=\"{$this->value}\" type=\"text\" placeholder=\"{$this->placeholder}\" /> \n";
	}
}

class SelectSearch extends Search {
	public $values;
	
	public function __construct($key, $values) {
		$this->key = $key;
		$this->values = $values;
	}

	public function values($values) {
		$this->values = $values;
		return $this;
	}

	public function render() {
		$key = 's_'.$this->key;
		$nl = "\n";
		$html = "<select id=\"{$key}\" class=\"form-control\" name=\"{$key}\"> \n";
		foreach ($this->values as $t => $v) {
			$selected = $this->value === $v ? ' selected="selected"' : '';
			$html .=  "<option value=\"{$v}\"{$selected}>{$t}</option> \n";
		}
		$html .= '</select>'.$nl;
		return $html;
	}
}

class ButtonGroup {
	public $buttons;

	public function __construct($buttons=[]) {
		$this->buttons = $buttons;
	}

	public function add($button) {
		$this->buttons[] = $button;
		return $this;
	}

	public function render() {
		if (count($this->buttons) > 0) {
			$nl = "\n";
			$buttons = '<div class="btn-group">'.$nl;
			foreach ($this->buttons as $button) {
				$buttons .= $button->render();
			}
			$buttons .= '</div>'.$nl;
		}
		return $buttons;
	}
}

class LinkButton {
	public $name;
	public $url;
	public $buttonStyle = 'primary';
	public $iconStyle = 'plus';

	public function __construct($name, $url) {
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

	public function buttonStyle($buttonStyle) {
		$this->buttonStyle = $buttonStyle;
		return $this;
	}

	public function iconStyle($iconStyle) {
		$this->iconStyle = $iconStyle;
		return $this;
	}

	public function render() {
		$buttonStyle =  $this->buttonStyle == '' ? '' : ' btn-'.$this->buttonStyle;
		$icon =  $this->iconStyle == '' ? '' : "<i class=\"fa fa-{$this->iconStyle}\"></i>";
		$url = TableBuilder::getUrl($this->url);
		return "<button class=\"btn{$buttonStyle}\" onclick=\"location.href='{$url}';\">{$icon}{$this->name}</button>";
	}
}

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
