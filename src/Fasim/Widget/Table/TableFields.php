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
	public $sortable = false;

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

	public function width($width) {
		$this->width = $width;
		return $this;
	}

	public function sortable($sortable) {
		$this->sortable = $sortable;
		return $this;
	}

	abstract function render();

}

class TextField extends Field {
	public $textAlign = '';
	
	private $useSwitch = false;
	private $cases = [];
	private $default = '';
	private $callback = null;
	private $vars = [];
	public $color = '';
	public $bold = false;
	public $italic = false;
	public $styleCases = [];

	public function textAlign($textAlign) {
		$this->textAlign = $textAlign;
		return $this;
	}

	public function color($color) {
		$this->color = $color;
		return $this;
	}

	public function bold($bold) {
		$this->bold = $bold;
		return $this;
	}

	public function italic($italic) {
		$this->italic = $italic;
		return $this;
	}

	public function styleCase($c, $s=[]) {
		$this->styleCases[] = [
			'c' => $c,
			's' => $s
		];
		return $this;
	}

	public function valueCase($k, $v, $c=null, $b=null, $i=null) {
		$this->useSwitch = true;
		$case = [
			'k' => $k,
			'v' => $v,
		];
		if ($c !== null) {
			$case['c'] = $c;
		}
		if ($b !== null) {
			$case['b'] = !!$b;
		}
		if ($i !== null) {
			$case['i'] = !!$i;
		}
		$this->cases[] = $case;
		return $this;
	}

	public function valueMap($map) {
		foreach ($map as $k => $v) {
			$c = null;
			$b = null;
			$i = null;
			if (is_array($v)) {
				if (isset($v['color'])) {
					$case['c'] = $v['color'];
				}
				if (isset($v['bold'])) {
					$case['b'] = !!$v['bold'];
				}
				if (isset($v['italic'])) {
					$case['i'] = !!$v['italic'];
				}
				$v = isset($v['text']) ? $v['text'] : '';
			}
			$this->valueCase($k, $v, $c, $b, $i);
		}
		return $this;
	}

	public function valueDefault($v, $c=null, $b=null, $i=null) {
		$this->useSwitch = true;
		$this->default = $v;
		if ($c !== null) {
			$this->color = $c;
		}
		if ($b !== null) {
			$this->bold = !!$b;
		}
		if ($i !== null) {
			$this->italic = !!$i;
		}
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
		$styles = [];
		if ($this->color) {
			$styles['color'] = $this->color;
		}
		if ($this->bold) {
			$styles['bold'] = true;
		}
		if ($this->italic) {
			$styles['italic'] = $this->true;
		}
		
		if ($this->useSwitch) {
			$value = $this->default;
			foreach ($this->cases as $case) {
				if ($case['k'] === $this->value) {
					$value = $case['v'];
					if (isset($case['c']) && $case['c'] != '') {
						$styles['color'] = $case['c'];
					}
					if (isset($case['b']) && $case['b']) {
						$styles['bold'] = true;
					}
					if (isset($case['i']) && $case['i']) {
						$styles['italic'] = true;
					}
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
		
		if (!empty($styles)) {
			$style = '';
			foreach ($styles as $sk => $sv) {
				if ($sk == 'color') {
					$style .= "color:$sv;";
				} else if ($sk == 'bold') {
					$style .= "font-weight:bold;";
				} else if ($sk == 'italic') {
					$style .= "font-style:italic;";
				}
			}
			$value = "<span style=\"$style\">$value</span>";
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
