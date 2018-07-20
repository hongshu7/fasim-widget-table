<?php
namespace Fasim\Widget\Table;

use Fasim\Facades\Config;
use Fasim\Facades\Input;
use Fasim\Library\Pager;


class Action {
	public $name;
	public $action;
	public $jscallback = '';
	public $classes = [ 'btn', 'btn-default' ];
	public $attrs = [];

	public function __construct($action='', $name='') {
		$this->name = $name;
		$this->action = $action;
	}

	public function name($name) {
		$this->name = $name;
		return $this;
	}

	public function action($action) {
		$this->action = $action;
		return $this;
	}

	public function jscallback($jscallback) {
		$this->jscallback = $jscallback;
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

	public function render() {
		
		$attrs = $this->attrs;
		$attrAppend = '';
		if (count($this->classes) > 0) {
			$old = isset($attrs['class']) ? $attrs['class'].' ' : '';
			$attrs['class'] = $old.implode(' ', $this->classes);
		}
		if (count($attrs) > 0) {
			foreach ($attrs as $an => $av) {
				$attrAppend .= ' ' . $an . '="' . $av . '"';
			}
		}
		
		return " <button type=\"submit\" name=\"action\" value=\"{$this->action}\" jscallback=\"{$this->jscallback}\"{$attrAppend}>{$this->name}</a> ";
	}
}
