<?php
class CashAccountFlow extends TraitConstants{
//use Constants;
    public const TYPES = [1 => 'INGRESO', 2 => 'EGRESO'];

	static $pk = 'id';
	static $table_name = 'cash_account_flows';
	static $connection = '';

	static $belongs_to = [];
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


    function getType(){
        return self::TYPES[$this->type];
    }
}
