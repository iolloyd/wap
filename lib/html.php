<?php
class html {
	public function displayTable($base, $table_name, array $data){
		$head = array();
		$rows = '';
		foreach ($data as $k => $entry) {
			$row = '';
			foreach ($entry as $k => $v) {
				$head[$k] = 1;
				$row .= self::wrap('td', $v);
			}
			$rows .= self::wrap('tr', $row . 
			         self::wrap('td', self::editLink($base, $table_name, $entry)));
		}
		$head = self::wrap('tr', implode('</td><td>', array_keys($head)));
		return self::wrap('table', $head.$rows);
	}

	public function displayEntry($base, $table_name, array $data){
		$head = array();
		$rows = '';
		$out = '';
		foreach ($data as $k => $entry) {
			foreach ($entry as $k => $v) {
				$out .= html::wrap('span', $k) . 
				        html::wrap('input', '', array('value' => $v));
			}
		}
		return html::wrap('form', $out, array(
			'name'   => 'some',
			'action' => '#',
			'method' => 'post')
		);
	}

	public function editLink($base, $table, array $entry){
		$id = $entry['id'];
		return "<a href='/$base/$table/$id'>Edit</a>";
	}

	public function form(array $attributes, array $members){
		$atts = Helpers::pairs2EqStrings($attributes);
		$tag  = '<form '.implode(' ', $atts).'>';
	}

	public function wrap($element, $data, $attributes=false) {
		$atts = '';
		if ($attributes) {
			foreach ($attributes as $k => $v) {
				$atts .= "$k='$v' ";
			}
		}
		return "<$element $atts>$data</$element>";
	}
}

