<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('distributors', 'DistributorController::index');
$routes->get('distributors/create', 'DistributorController::create');
$routes->post('distributors/store', 'DistributorController::store');
$routes->get('distributors/edit/(:segment)', 'DistributorController::edit/$1');
$routes->match(['POST', 'PUT'], 'distributors/update/(:segment)', 'DistributorController::update/$1');
$routes->delete('distributors/delete/(:segment)', 'DistributorController::delete/$1'); 
$routes->get('/distributors/detail/(:any)', 'DistributorController::detail/$1');
$routes->get('distributors/export/excel', 'DistributorController::exportExcel');
$routes->get('distributors/data', 'DistributorController::data');
$routes->get('distributors/count', 'DistributorController::count');
