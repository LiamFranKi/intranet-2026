<?php
use Core\Scaffold;

class NiftyScaffold extends Scaffold{
	
	//@override
	function CreateApplicationFile(){
		
	}
	
	//@override
	function CreateTemplates(){
		
		// Creates the index template
		$thead = '';
		foreach($this->getColumns() As $key=>$val){
			$thead .= "\n\t\t\t\t\t\t".'<th>'.$key.'</th>';
		}
		
		$thead .= "\n\t\t\t\t\t\t".'<th></th>';
		$this->context('thead', $thead);
		
		$tbody = '{% for '.strtolower($this->context('Model')).' in '.$this->context('Name').' %}';
		$tbody .= "\n\t\t\t\t\t".'<tr>';
		
		foreach($this->getColumns() As $key=>$val){
			$tbody .= "\n\t\t\t\t\t\t".'<td>{{ '.strtolower($this->context('Model')).'.'.$key.' }}</td>';
		}
		
		$tbody .= "\n\t\t\t\t\t\t".'
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/'.strtolower($this->context('Name')).'/form/{{ sha1('.strtolower($this->context('Model')).'.id) }}">{{ icon(\'register\') }} Editar '.$this->context('Model').'</a></li>
									<li><a href="javascript:;" onclick="borrar_'.strtolower($this->context('Model')).'(\'{{ sha1('.strtolower($this->context('Model')).'.id) }}\')">{{ icon(\'delete\') }} Borrar '.$this->context('Model').'</a></li>
								</ul>
							</div>
						</td>';
		$tbody .= "\n\t\t\t\t\t".'</tr>';
		$tbody .= "\n\t\t\t\t\t".'{% endfor %}';
		$this->context('tbody', $tbody);
		$this->context('columns', $this->getColumns());
		
		$this->render('views/index.php', 'views/index.php');
		$this->render('views/form.php', 'views/form.php');
		$this->render('index.php', 'index.php');
	}
	

}
