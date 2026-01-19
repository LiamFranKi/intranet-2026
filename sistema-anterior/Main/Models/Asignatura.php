<?php
class Asignatura extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'asignaturas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'curso',
			'class_name' => 'Curso',
		),
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
		array(
			'personal',
			'class_name' => 'Personal',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'curso_id',
		),
		array(
			'personal_id',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getCriterios($ciclo = null){
        $conditions = 'colegio_id="'.$this->colegio_id.'" AND asignatura_id="'.$this->id.'"';
        if(isset($ciclo)) $conditions .= ' AND (ciclo="'.$ciclo.'" OR ciclo="0")';
        
        $criterios = Asignatura_Criterio::all(array(
            'conditions' => $conditions,
            'order' => 'orden ASC'
        ));
        
        return $criterios;
    }

    function getExamenes($ciclo){
        return Asignatura_Examen::find_all_by_asignatura_id_and_ciclo($this->id, $ciclo);
    }

    function getAllTests(){
        return Asignatura_Examen::find_all_by_asignatura_id($this->id);
    }

    function getArchivos($ciclo){
    	return Asignatura_Archivo::find_all_by_asignatura_id_and_ciclo($this->id, $ciclo, [
    		'order' => 'orden ASC'
    	]);
    }

    function getAllFiles(){
    	return Asignatura_Archivo::find_all_by_asignatura_id($this->id, [
    		'order' => 'ciclo ASC, orden ASC'
    	]);
    }

    function getVideos($ciclo){
    	return Asignatura_Video::find_all_by_asignatura_id_and_ciclo($this->id, $ciclo, [
    		'order' => 'fecha_hora DESC'
    	]);
    }

    function getAllVideos(){
    	return Asignatura_Video::find_all_by_asignatura_id($this->id, [
    		'order' => 'fecha_hora DESC'
    	]);
    }

    function getEnlaces($ciclo){
    	return Asignatura_Enlace::find_all_by_asignatura_id_and_ciclo($this->id, $ciclo, [
    		'order' => 'fecha_hora DESC'
    	]);
    }

    function getAllLinks(){
    	return Asignatura_Enlace::find_all_by_asignatura_id($this->id, [
    		'order' => 'fecha_hora DESC'
    	]);
    }

    function getTareas($ciclo){
    	return Asignatura_Tarea::find_all_by_asignatura_id_and_ciclo($this->id, $ciclo, [
    		'order' => 'fecha_entrega DESC'
    	]);
    }

    function getAllHomeWorks(){
    	return Asignatura_Tarea::find_all_by_asignatura_id($this->id, [
    		'order' => 'fecha_entrega DESC'
    	]);
    }

    public function after_create(){
        $this->loadCriterios();
    }

    function loadCriterios(){
        if($this->grupo->nivel_id > 1){

            $data = array(
                array(
                    'descripcion' => 'EBES',
                    'peso' => 20,
                    'cuadros' => 4
                ),
                array(
                    'descripcion' => 'TAREA',
                    'peso' => 15,
                    'cuadros' => 4
                ),
                array(
                    'descripcion' => 'CUADERNO',
                    'peso' => 15,
                    'cuadros' => 0
                ),
                array(
                    'descripcion' => 'EXAMEN BIMESTRAL',
                    'peso' => 30,
                    'cuadros' => 0
                ),
            );

            foreach($data As $key_order => $d){
                $criterio = Asignatura_Criterio::create(array(
                    'colegio_id' => $this->colegio_id,
                    'descripcion' => $d['descripcion'],
                    'asignatura_id' => $this->id,
                    'ciclo' => 0,
                    'abreviatura' => '',
                    'orden' => $key_order,
                    'peso' => $d['peso'],
                ));

                if($criterio->save() && $d['cuadros'] > 0){
                    Asignatura_Indicador::table()->delete(array(
                        'criterio_id' => $criterio->id
                    ));
                    $indicador = new Asignatura_Indicador(array(
                        'criterio_id' => $criterio->id,
                        'descripcion' => 'GENERAL',
                        'cuadros' => $d['cuadros']
                    ));
                    $indicador->save();
                }
            }
        }
    }

    function getHorarioFechas(){

    	$ciclosNotas = $this->colegio->getRangosCiclosNotas();
    	$primerCiclo = $ciclosNotas[1];
    	$ultimoCiclo = end($ciclosNotas);
    	$firstDate = date('Y-m-d', strtotime($primerCiclo['inicio'].'-'.$this->grupo->anio));
    	// agregamos un día
    	$lastDate = date('Y-m-d', strtotime($ultimoCiclo['final'].'-'.$this->grupo->anio) + 60*60*24);

    	$dStart = new DateTime($firstDate);
		$dEnd  = new DateTime($lastDate);
		$dDiff = $dStart->diff($dEnd);
		$dias = $dDiff->days;
    	$fechas = array();
    	
    	//do{
    	for($i=1; $i<=$dias;++$i){
    		$dayIndex = date('N', strtotime($firstDate)) - 1;
    		//echo $firstDate.'<br />';
    		if($dayIndex < 5){
    			$horario = Grupo_Horario::find(array(
    				'conditions' => 'asignatura_id="'.$this->id.'" AND dia="'.$dayIndex.'"'
    			));

    			if($horario){
    				$mes = date('m', strtotime($firstDate)) - 1;
    				if(is_null($fechas[$mes])) $fechas[$mes] = array();
    				$tema = Asignatura_Tema::find_by_asignatura_id_and_Fecha($this->id, $firstDate);
    				$fechas[$mes][] = array(
    					'fecha' => $firstDate,
    					'weekDay' => $this->colegio->DIAS[$dayIndex],
    					'tema' => $tema
    				);
    				//echo $firstDate." - ".$this->COLEGIO->DIAS[$dayIndex]."<br />";
    			}
    			
    		}

    		//$firstDate = date('Y-m-d', strtotime($firstDate) + (60*60*24));
    		$firstDate = date('Y-m-d', strtotime($firstDate. ' + 1 days'));
    	}
    	//}while($firstDate != $lastDate);
    	//}while(strtotime($firstDate) <= strtotime($lastDate));
    	
    	//echo $dias.'<br />';
    	//echo $firstDate.' - '.$lastDate;
		//$firstDate = date('Y-m-d', strtotime($firstDate) + (60*60*24));
		//echo $firstDate;
    	return $fechas;
    }
}
