<?php
class form {
	var $data, $view, $errors;

	public function __construct(){
	}

	public function addData($data=array()){
		$this->data = $data;
	}

	public function asTable($rows=''){
		foreach($this->data as $label => $attributes){
			$input = $this->buildInput($label, $attributes);
			$row   = $this->buildRow(array($label, $input));
			$row   = "<tr><td>$label</td><td>$input</td></tr>";
			$rows .= $row;
		}
		$this->view = $rows;
	}
	public function show(){
		echo $this->view;
	}

	protected function buildInput($label, $attributes){
		$input = "<input name='$label'";
		foreach ($attributes as $k => $v) {
			$input .= " $k='$v'";
		}
		$input .= '/>';
		return $input;
	}
	
	protected function buildRow($values, $tds=''){
		foreach($values as $v){
			$tds .= '<td>'.$v.'</td>';
		}
		return '<tr>'.$tds.'</tr>';
	}
}

