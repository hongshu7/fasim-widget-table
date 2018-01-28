<?php
namespace Fasim\Widget\Table;

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

class HiddenSearch extends Search {
	public $placeholder;

	public function __construct($key) {
		$this->key = $key;
	}

	public function render() {
		$key = 's_'.$this->key;
		return "<input id=\"{$key}\" name=\"{$key}\" value=\"{$this->value}\" type=\"hidden\" /> \n";
	}
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

