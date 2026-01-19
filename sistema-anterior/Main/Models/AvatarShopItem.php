<?php
class AvatarShopItem extends TraitConstants{
//use Constants;

	static $pk = 'id';
	static $table_name = 'avatar_shop_items';
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
}
