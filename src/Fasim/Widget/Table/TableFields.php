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
