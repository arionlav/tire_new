<?php
use config\App;
use config\Config;
use core\Route;
use core\SessionManager;

require_once 'core/Autoloader.php';

App::$app = Config::getInstance();

SessionManager::startSession('TIRESESSION');

Route::start();