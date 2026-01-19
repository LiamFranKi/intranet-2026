<?php
class Aula_virtualApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => 'index, copy_form, do_copy_form',
			'ALUMNO' => 'index',
			'APODERADO' => 'index',
            'ASISTENCIA' => 'index'
		]);
	}

	function index(){
		if($this->USUARIO->is('ALUMNO'))
			$matricula = $this->USUARIO->alumno->getMatriculaByAnio($this->COLEGIO->anio_activo);

		$this->render(['matricula' => $matricula]);
	}

    function copy_form(){
        $asignatura = $this->asignatura;
        $otherAssignments = Asignatura::all([
            'conditions' => 'asignaturas.id != '.$asignatura->id.' AND curso_id = '.$asignatura->curso_id.' AND grupos.anio = '.$asignatura->grupo->anio.' AND grupos.grado = '.$asignatura->grupo->grado,
            'joins' => ['grupo']
        ]);

        $files = $asignatura->getAllFiles();
        $homeWorks = $asignatura->getAllHomeWorks();
        $tests = $asignatura->getAllTests();
        $videos = $asignatura->getAllVideos();
        $links = $asignatura->getAllLinks();

        $this->render([
            'otherAssignments' => $otherAssignments, 
            'files' => $files, 
            'homeWorks' => $homeWorks,
            'tests' => $tests,
            'videos' => $videos,
            'links' => $links
        ]);
    }

    function do_copy_form(){
        $assignment = Asignatura::find($this->post->assignment_id);

        if(count($this->post->files_id)){
            foreach($this->post->files_id As $file_id){
                $file = Asignatura_Archivo::find($file_id);
                $newFile = Asignatura_Archivo::create([
                    'asignatura_id' => $assignment->id,
                    'trabajador_id' => $file->trabajador_id,
                    'nombre' => $file->nombre,
                    'archivo' => $file->archivo,
                    'fecha_hora' => date('Y-m-d H:i'),
                    'ciclo' => $file->ciclo,
                    'orden' => $file->orden
                ]);
            }
        }

        if(count($this->post->homeworks_id)){
            foreach($this->post->homeworks_id As $homework_id){
                $homework = Asignatura_Tarea::find($homework_id);
                $homeworkFiles = $homework->getArchivos();

                $newHomework = Asignatura_Tarea::create([
                    'titulo' => $homework->titulo,
                    'descripcion' => $homework->descripcion,
                    'fecha_hora' => date('Y-m-d H:i'),
                    'fecha_entrega' => $homework->fecha_entrega,
                    'trabajador_id' => $homework->trabajador_id,
                    'asignatura_id' => $assignment->id,
                    'ciclo' => $homework->ciclo
                ]);

                if(!is_null($homework)){
                    foreach($homeworkFiles As $file){
                        Asignatura_Tarea_Archivo::create([
                            'tarea_id' => $newHomework->id,
                            'nombre' => $file->nombre,
                            'archivo' => $file->archivo
                        ]);
                    }
                }
            }
        }

        if(count($this->post->tests_id)){
            foreach($this->post->tests_id As $test_id){
                $test = Asignatura_Examen::find($test_id);
                $testAttributes = $test->attributes();
                $testAttributes['asignatura_id'] = $assignment->id;
                unset($testAttributes['id']);

                $newTest = Asignatura_Examen::create($testAttributes);

                $testQuestions = $test->getPreguntas();
                foreach($testQuestions As $question){
                    $questionAttributes = $question->attributes();
                    $questionAttributes['examen_id'] = $newTest->id;
                    unset($questionAttributes['id']);
                    $newQuestion = Asignatura_Examen_Pregunta::create($questionAttributes);

                    $questionChoices = $question->getAlternativas();
                    foreach($questionChoices As $choice){
                        $choiceAttributes = $choice->attributes();
                        $choiceAttributes['pregunta_id'] = $newQuestion->id;
                        unset($choiceAttributes['id']);

                        $newChoice = Asignatura_Examen_Pregunta_Alternativa::create($choiceAttributes);
                    }
                }
            }
        }

        if(count($this->post->videos_id)){
            foreach($this->post->videos_id As $video_id){
                $video = Asignatura_Video::find($video_id);

                $videoAttributes = $video->attributes();
                $videoAttributes['asignatura_id'] = $assignment->id;
                unset($videoAttributes['id']);
                $newVideo = Asignatura_Video::create($videoAttributes);

            }
        }

        if(count($this->post->links_id)){
            foreach($this->post->links_id As $link_id){
                $link = Asignatura_Enlace::find($link_id);

                $linkAttributes = $link->attributes();
                $linkAttributes['asignatura_id'] = $assignment->id;
                unset($linkAttributes['id']);
                $newLink = Asignatura_Enlace::create($linkAttributes);

            }
        }


        echo json_encode([1]);
    }

	function __getObjectAndForm(){
		$this->asignatura = !empty($this->params->id) ? Asignatura::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura();
		$this->context('asignatura', $this->asignatura); // set to template
	}
}
