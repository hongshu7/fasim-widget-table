<?php
namespace Fasim\Widget\Table;


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