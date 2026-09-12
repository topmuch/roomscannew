<?php
// =============================================
// RoomScan — Routeur Frontal (Front Controller)
// =============================================

require_once __DIR__ . '/config/config.php';

// Parse URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$segments = $uri ? explode('/', $uri) : [];

// Determine controller + action
$controllerName = $segments[0] ?? 'landing';
$action = $segments[1] ?? 'index';
$param = $segments[2] ?? null;

// Public routes (no auth needed)
$publicRoutes = ['landing', 'login', 'register', 'forgot-password', 'reset-password', 'room', 'clean', 'found', 'api'];

// If not logged in and not a public route → redirect to login
if (!is_logged_in() && !in_array($controllerName, $publicRoutes)) {
  redirect('/login');
}

// Route aliases: map URL controller names to actual controller files
$controllerAliases = [
  'clean' => 'room',   // /clean/{token} → RoomController::clean($token)
  'found' => 'room',   // /found/{token} → RoomController::found($token)
];
$actualControllerName = $controllerAliases[$controllerName] ?? $controllerName;

// Route to controller
$controllerFile = __DIR__ . "/controllers/" . ucfirst($actualControllerName) . "Controller.php";

if (file_exists($controllerFile)) {
  require_once $controllerFile;
  $className = ucfirst($actualControllerName) . 'Controller';
  $controller = new $className($pdo);

  if (method_exists($controller, $action)) {
    // Pass remaining URL segments as method args, matching the method signature.
    // Allows nested resources like /roomservice/menu/edit/{id} to dispatch to
    // RoomServiceController::menu('edit', '5') while remaining fully backward
    // compatible with single-param actions like RoomController::index($token).
    $args = array_slice($segments, 2);
    $ref = new ReflectionMethod($controller, $action);
    $maxParams = $ref->getNumberOfParameters();
    $args = array_slice($args, 0, $maxParams);
    while (count($args) < $maxParams) {
      $args[] = null;
    }
    call_user_func_array([$controller, $action], $args);
  } else {
    // Action doesn't exist → 404
    http_response_code(404);
    require_once __DIR__ . '/controllers/DashboardController.php';
    $dc = new DashboardController($pdo);
    $dc->notFound();
  }
} else {
  // Controller doesn't exist → check if it's an API route
  $apiFile = __DIR__ . "/api/" . ucfirst($controllerName) . "Api.php";
  if (file_exists($apiFile)) {
    require_once $apiFile;
    $apiClass = ucfirst($controllerName) . 'Api';
    $api = new $apiClass($pdo);
    $api->$action($param);
  } else {
    http_response_code(404);
    echo '<h1>404 — Page non trouvée</h1>';
    echo '<a href="/">Retour à l\'accueil</a>';
  }
}
