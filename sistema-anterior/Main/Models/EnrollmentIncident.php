<?php
class EnrollmentIncident extends TraitConstants{
//use Constants;

	static $pk = 'id';
	static $table_name = 'enrollment_incidents';
	static $connection = '';

	static $belongs_to = [
        array(
			'worker',
			'class_name' => 'Personal',
            'foreign_key' => 'worker_id'
		),
    ];
	static $has_many = [];
	static $has_one = [];

	static $validates_presence_of = [

	];
	static $validates_size_of = [];
	static $validates_length_of = [];
	static $validates_inclusion_of = [];
	static $validates_exclusion_of = [];
	static $validates_format_of = [];
	static $validates_numericality_of = [];
	static $validates_uniqueness_of = [];


    function after_create(){
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }
}
