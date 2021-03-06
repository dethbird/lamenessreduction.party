<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
define("APPLICATION_PATH", __DIR__ . "/../");
date_default_timezone_set('America/New_York');
session_cache_limiter(false);
session_start();

# Ensure src/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH ,
    APPLICATION_PATH . 'library',
    get_include_path(),
)));


require '../vendor/autoload.php';
require_once APPLICATION_PATH . 'src/library/View/Extension/TemplateHelpers.php';
require_once APPLICATION_PATH . 'vendor/php-activerecord/php-activerecord/ActiveRecord.php';

use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;
use Cocur\Slugify\Slugify;
use Symfony\Component\Yaml\Yaml;
use Guzzle\Http\Client;

# Load configs and add to the app container
$configs = Yaml::parse(file_get_contents("../configs/configs.yml"));
$app = new \Slim\Slim(
    array(
        'view' => new Slim\Views\Twig(),
        'templates.path' => APPLICATION_PATH . 'src/views',
        'cookies.encrypt' => true,
        'cookies.secret_key' => $configs['security']['secret'],
        'cookies.cipher' => MCRYPT_RIJNDAEL_256,
        'cookies.cipher_mode' => MCRYPT_MODE_CBC
    )
);

$markdownEngine = new MarkdownEngine\MichelfMarkdownEngine();

$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new TemplateHelpers(),
    new MarkdownExtension($markdownEngine)
);

$app->container->set('configs', $configs);


ActiveRecord\Config::initialize(function($cfg)
{
    global $configs;

    $cfg->set_model_directory(APPLICATION_PATH . '/src/library/Models');
    $cfg->set_connections(
        [
            'development' =>
                'mysql://'.$configs['mysql']['user']
                .':'.$configs['mysql']['password']
                .'@'.$configs['mysql']['host'].'/'
                .$configs['mysql']['database']
        ]
    );
});

// # authorize the user by session (middleware)
// $authorize = function ($app) {
//
//     return function () use ($app) {
//
//         $configs = $app->container->get('configs');
//
//         # if no user session, set to default user: application
//         if(!isset( $_SESSION['securityContext'])) {
//             $user = User::find_by_api_key($configs['application']['api_key']);
//             $_SESSION['securityContext'] = json_decode($user->to_json([
//                 'except' => ['api_key', 'password', 'email']
//             ]));
//         }
//
//         # store current path in session for smart login
//         $_SESSION['redirectTo'] = $app->request->getPathInfo();
//     };
// };
//
// # authorize the user by header auth token
// $authorizeByHeaders = function ($app) {
//
//     return function () use ($app) {
//
//         # check cookie for securityContext
//         $apiKey = $app->request->headers->get('X-Api-Key');
//         if ($apiKey == "") {
//             if (!isset($_SESSION['securityContext'])) {
//                 $app->halt(400, json_encode(['X-Api-Key'=>'Invalid api key, no active session']));
//             }
//         } else {
//             $user = User::find_by_api_key($apiKey);
//
//             if(!$user) {
//                 $app->halt(404, json_encode(['X-Api-Key'=>'Invalid api key, user not found']));
//             } else {
//                 $_SESSION['securityContext'] = json_decode($user->to_json([
//                     'except' => ['api_key', 'password', 'email']
//                 ]));
//             }
//         }
//     };
// };
//
// # authorize the user by header auth token
// $writeAccess = function ($app) {
//
//     return function () use ($app) {
//         # check cookie for securityContext
//         if (isset($_SESSION['securityContext'])) {
//
//             $user = $_SESSION['securityContext'];
//             if (!$user->write) {
//                 $app->halt(403);
//             }
//         }
//     };
// };

$app->notFound(function () use ($app) {
    $_SESSION['lastRequestUri'] = $_SERVER['REQUEST_URI'];
    $app->redirect("/");
});

# index
$app->get("/", function () use ($app) {

    $configs = $app->container->get('configs');
    $securityContext = isset($_SESSION['securityContext']) ? $_SESSION['securityContext'] : null;
    $lastRequestUri = isset($_SESSION['lastRequestUri']) ? $_SESSION['lastRequestUri'] : null;

    $templateVars = array(
        "configs" => $configs,
        'securityContext' => $securityContext,
        'lastRequestUri' => $lastRequestUri,
        "section" => "index"
    );

    $app->render(
        'pages/index.html.twig',
        $templateVars,
        200
    );
});

# logout
$app->get("/logout", function () use ($app) {
  $_SESSION['securityContext'] = null;
  $app->redirect("/");
});


// require_once APPLICATION_PATH . 'src/routes/api.php';
// require_once APPLICATION_PATH . 'src/routes/likedrop.php';
// require_once APPLICATION_PATH . 'src/routes/projects.php';
// require_once APPLICATION_PATH . 'src/routes/scripts.php';
// require_once APPLICATION_PATH . 'src/routes/service.php';


$app->run();
