<?php
class TestApplication extends Core\Application{
	
	function index($r){
		$this->render();
	}

	function email_matricula(){
		$matricula = Matricula::first();
		enviarEmailMatricula($matricula->id);
	}

    function email(){
        sendEmail(['zarkiel@gmail.com'], 'Mensaje de Prueba REMOTO', "Este es un mensaje de prueba por Zoho!!!");
    }

    function fix_costos(){
        $costo = Costo::find(9);
        $matriculas = Matricula::find_all_by_costo_id($costo->id);
        foreach($matriculas as $matricula){
   

            $new_costo = Costo::create([
                'colegio_id' => $this->COLEGIO->id,
                'descripcion' => 'Costo Personalizado - '.$matricula->id,
                'matricula' => $costo->matricula,
                'pension' => $costo->pension,
                'agenda' => $costo->agenda,
                'tipo' => 'PERSONAL'
            ]);

            $matricula->costo_id = $new_costo->id;
            $matricula->save();
        }
    }
}
