<?php
use Core\Scaffold;

class DefaultScaffold extends Scaffold{
	
	//@override
	function CreateApplicationFile(){
		$this->render('index.php', 'index.php');
	}
	
	//@override
	function CreateTemplates(){
		
		// Creates the index template
		$thead = '';
		foreach($this->getColumns() As $key=>$val){
			$thead .= "\n\t\t\t".'<th>'.$key.'</th>';
		}
		
		$thead .= "\n\t\t\t".'<th></th>';
		$this->context('thead', $thead);
		
		$tbody = '{% for '.strtolower($this->context('Model')).' in '.$this->context('Name').' %}'."\n\t\t";
		$tbody .= '<tr>';
		
		foreach($this->getColumns() As $key=>$val){
			$tbody .= "\n\t\t\t".'<td>{{ '.strtolower($this->context('Model')).'.'.$key.' }}</td>';
		}
		
		$tbody .= "\n\t\t\t".'<td><a href="/'.$this->context('Name').'/edit/{{ '.strtolower($this->context('Model')).'.id }}">[ Edit ]</a> - <a onclick="return confirm(\'Are you sure to delete this record?\')" href="/'.$this->context('Name').'/delete/{{ '.strtolower($this->context('Model')).'.id }}">[ Delete ]</a></td>';
		$tbody .= "\n\t\t".'</tr>';
		$tbody .= "\n\t".'{% endfor %}';
		$this->context('tbody', $tbody);

		$this->render('views/index.php', 'views/index.php');
		$this->render('views/add.php', 'views/add.php');
		$this->render('views/edit.php', 'views/edit.php');
		$this->render('views/delete.php', 'views/delete.php');

	}
}
