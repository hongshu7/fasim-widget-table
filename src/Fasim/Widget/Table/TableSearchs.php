<?php
namespace Fasim\Widget\Table;

abstract class Search {
	public $key;
	public $value;
	public $readonly = false;
	public $styles = [];

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
	public function readonly($readonly=true) {
		$this->readonly = $readonly;
		return $this;
	}
	public function width($width) {
		$this->styles['width'] = $width . 'px';
		return $this;
	}
	public function height($height) {
		$this->styles['height'] = $height . 'px';
		return $this;
	}
	public function style($name, $value) {
		$this->styles[$name] = $value;
		return $this;
	}
	public function getStyle() {
		$style = '';
		if (!empty($this->styles)) {
			$style = ' style="';
			foreach ($this->styles as $k => $v) {
				$style .= "$k:$v;";
			}
			$style .= '"';
		}
		return $style;
	}
	abstract function render();
}

class HiddenSearch extends Search {
	public $placeholder;

	public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
	}

	public function render() {
        $key = $this->key;
		return "<input id=\"ts_{$key}\" name=\"{$key}\" value=\"{$this->value}\" type=\"hidden\" /> \n";
	}
}

class TextSearch extends Search {
	public $placeholder = '';
	public $value = '';

	public function __construct($key, $placeholder, $value='') {
		$this->key = $key;
		$this->placeholder = $placeholder;
		$this->value = $value;
	}

	public function placeholder($placeholder) {
		$this->placeholder = $placeholder;
		return $this;
	}

	public function render() {
		$key = $this->key;
		$style = $this->getStyle();
		$readonly = $this->readonly ? ' readonly="readonly"' : '';
		return "<input id=\"ts_{$key}\" class=\"form-control\" name=\"{$key}\" value=\"{$this->value}\" type=\"text\" placeholder=\"{$this->placeholder}\"{$style}{$readonly} /> \n";
	}
}

class SelectSearch extends TextSearch {
	public $placeholder;
	public $options;
	
	public function __construct($key, $placeholder, $options) {
		$this->key = $key;
		$this->placeholder = $placeholder;
		$this->options($options);
	}

	public function options($options) {
		if (is_array($options)) {
			foreach ($options as $ok => $ov) {
				if (is_array($ov) && isset($ov['value'])) {
					//fixed
					if (isset($ov['key'])) {
						$ov['name'] = $ov['key'];
					}
					if (isset($ov['name'])) {
						$this->options[] = [
							'name' => $ov['name'],
							'value' => $ov['value']
						];
					}
				} else if (is_string($ov)) {
					$this->options[] = [
						'name' => $ov,
						'value' => $ok.''
					];
				}
			}
		}
		return $this;
	}

	public function render() {
		$key = $this->key;
		$style = $this->getStyle();
		$html = "<select id=\"ts_{$key}\" class=\"form-control\" name=\"{$key}\"{$style}> \n";
		if ($this->placeholder != '') {
			$html .= "<option value=\"\">{$this->placeholder}</option>\n";
		}
		foreach ($this->options as $option) {
			$selected = $this->value == $option['value'] ? ' selected="selected"' : '';
			$html .= "<option value=\"{$option['value']}\"{$selected}>{$option['name']}</option>\n";
		}
		$html .= '</select>'."\n";
		return $html;
	}
}

class DateSearch extends TextSearch {
	public $dateFormat = 'yyyy-mm-dd';
	public $dateStyle = 'date';

	public $options = [
		'format' => 'yyyy-mm-dd',
		'autoclose' => 'true'
	];

	public function __construct($key, $placeholder, $value='') {
		$this->key = $key;
		$this->placeholder = $placeholder;
		$this->value = $value;
	}

	public function dateStyle($format='') {
		$this->options['format'] = $format;
		return $this;
	}

	public function dateOptions($options) {
		$this->options = array_merge($this->options, $options);
		return $this;
	}

	public function autoclose($autoclose) {
		$this->autoclose = $autoclose ? 'true' : 'false';
		return $this;
	}

	public function render() {
		$style = $this->getStyle();
		$readonly = $this->readonly ? ' readonly="readonly"' : '';
		$html = '<div class="input-group date">'."\n";
		$html .= "<input id=\"ts_{$this->key}\" type=\"text\" name=\"{$this->key}\" placeholder=\"{$this->placeholder}\" value=\"{$this->value}\" class=\"form-control datepicker\"{$style}{$readonly} ";
		foreach ($this->options as $ok => $ov) {
			$html .= " data-date-{$ok}=\"{$ov}\"";
		}
		$html .= "/> \n";
		$html .= '<span class="input-group-addon"><i class="fa fa-calendar"></i></span>'."\n";
		$html .= "</div>\n";

		return $html;
	}
}

