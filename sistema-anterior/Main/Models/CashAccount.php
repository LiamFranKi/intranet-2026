<?php
class CashAccount extends TraitConstants{
//use Constants;

	static $pk = 'id';
	static $table_name = 'cash_accounts';
	static $connection = '';

	static $belongs_to = [
        ['currency', 'class_name' => 'CashCurrency', 'foreign_key' => 'cash_currency_id'],
        ['type', 'class_name' => 'CashAccountType', 'foreign_key' => 'cash_account_type_id'],
    ];
	static $has_many = [];
	static $has_one = [];

	static $validates_presence_of = [
		[
			'name',
		],
	];
	static $validates_size_of = [];
	static $validates_length_of = [];
	static $validates_inclusion_of = [];
	static $validates_exclusion_of = [];
	static $validates_format_of = [];
	static $validates_numericality_of = [];
	static $validates_uniqueness_of = [];

    function getBalance(){
        $incomes = CashAccountFlow::find([
            'select' => 'SUM(amount) as total',
            'conditions' => ['cash_account_id = ? AND type = 1', $this->id]
        ]);

        $outcomes = CashAccountFlow::find([
            'select' => 'SUM(amount) as total',
            'conditions' => ['cash_account_id = ? AND type = 2', $this->id]
        ]);

        return ($incomes->total - $outcomes->total);
    }
}
