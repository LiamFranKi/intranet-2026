<?php
use Core\CrystalTools;
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

require 'Settings.php';
require 'Core/Autoloader.php';
require 'Core/InstanceHandler.php';
require 'Core/Core.php';
require 'Core/CrystalTools.php';
require 'Core/Http/BasicResponse.php';

$crystalTools = new CrystalTools();
$crystalTools->startExceptionHandler();
$crystalTools->setComposerAutoload();
$crystalTools->runApplication();
