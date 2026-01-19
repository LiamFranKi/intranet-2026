<?php
class Asignatura_Examen_Pregunta extends ActiveRecord\Model {

    static $pk = 'id';
    static $table_name = 'asignaturas_examenes_preguntas';
    static $connection = '';

    static $belongs_to = array(
        array(
            'Examen',
            'class_name' => 'Asignatura_Examen',
            'foreign_key' => 'examen_id',
        ),
    );
    static $has_many = array(
        array(
            'Alternativas',
            'class_name' => 'Asignatura_Examen_Pregunta_Alternativa',
            'foreign_key' => 'pregunta_id',
        ),
    );
    static $has_one = array();

    static $validates_presence_of = array();
    static $validates_size_of = array();
    static $validates_length_of = array();
    static $validates_inclusion_of = array();
    static $validates_exclusion_of = array();
    static $validates_format_of = array();
    static $validates_numericality_of = array();
    static $validates_uniqueness_of = array();


    function getAlternativas() {
        $alternativas = Asignatura_Examen_Pregunta_Alternativa::find_all_by_pregunta_id($this->id, array(
            'order' => 'id ASC'
        ));
        return $alternativas;
    }

    function getFormCompletar($respuestas = []) {
        $string = $this->descripcion;
        $pattern = '/\[\[(.*?)\]\]/';
        $id = $this->id;


        $i = 0;
        $result = preg_replace_callback(
            $pattern,
            function ($matches) use (&$respuestas, &$i, $id) {

                // Obtenemos el valor correspondiente del array de nuevos valores.
                // Usamos htmlspecialchars para seguridad y evitar problemas si el valor contiene comillas.
                $valorActual = isset($respuestas[$i]) ? htmlspecialchars($respuestas[$i], ENT_QUOTES, 'UTF-8') : '';

                // Incrementamos el contador para la próxima coincidencia.
                $i++;

                // Devolvemos el HTML del input con el nuevo valor.
                return '<input type="text" value="' . $valorActual . '" name="respuestas[' . $id . '][]" class="respuesta-completar">';
            },
            $string
        );

        return $result;
    }

    function getRespuestasCompletar() {
        $pattern = '/\[\[(.*?)\]\]/';

        // Declaramos el array donde se guardarán los resultados.
        $matches = [];

        // Ejecutamos preg_match_all.
        preg_match_all($pattern, $this->descripcion, $matches);

        // Los valores que queremos (el texto capturado) estarán en el índice 1 del array $matches.
        return $matches[1];
    }

    function getTextoRespuestaCompletar() {
        $string = $this->descripcion;

        $pattern = '/\[\[(.*?)\]\]/';

        $replacement = '<b>$1</b>';

        $result = preg_replace($pattern, $replacement, $string);

        return $result;
    }

    function checkRespuesta($respuestas) {
        $respuestasPregunta = $this->getRespuestasCompletar();
        $inputsOk = 0;
        if (isset($respuestas) && is_array($respuestas)) {
            foreach ($respuestasPregunta as $keyRespuestaPregunta => $respuestaPregunta) {
                if (mb_strtolower(trim($respuestas[$keyRespuestaPregunta]), 'utf-8') == mb_strtolower(trim($respuestaPregunta), 'utf-8')) {
                    $inputsOk++;
                }
            }
        }
        return $inputsOk == count($respuestasPregunta);
    }
}
