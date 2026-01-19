<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

abstract class Initializer extends Middleware{
	/**
	 * 	If initializer is defined, this method is defined
	 */ 
	abstract function initialize();
}
