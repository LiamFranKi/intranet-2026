<?php
class AvatarShopSale extends TraitConstants{
//use Constants;

	static $pk = 'id';
	static $table_name = 'avatar_shop_sales';
	static $connection = '';

	static $belongs_to = [
        array(
			'item',
			'class_name' => 'AvatarShopItem',
			'foreign_key' => 'item_id',
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

    
}
