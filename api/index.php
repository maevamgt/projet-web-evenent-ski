<?php
require_once '../bdd.php';
require_once '../vendor/autoload.php';

// intialisation de Silex app
$app = new Silex\Application();

//connection a la base de données
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'dbname' => 'projetski',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ),
));

// route pour "/users" affiche tous les utilisateurs
$app->get('/users', function () use ($app) {
    $sql = "SELECT * FROM inscription";
    $inscrits = $app['db']->fetchAll($sql);

    return $app->json($inscrits);
});

// route pour tous les utilisateurs en attente
$app->get('/users/waiting', function () use ($app) {
    $sql = "SELECT * FROM inscription WHERE etatInscription = 0 ORDER BY dateInscription ASC";
    $insrits = $app['db']->fetchall($sql);

    return $app->json($insrits);
});

// route pour tous les utilisateurs accepté
$app->get('/users/accepted', function () use ($app) {
    $sql = "SELECT * FROM inscription WHERE etatInscription = 1 ORDER BY dateInscription ASC";
    $insrits = $app['db']->fetchall($sql);

    return $app->json($insrits, 200);
});

// route pour tous les utilisateurs refusé
$app->get('/users/refused', function () use ($app) {
    $sql = "SELECT * FROM inscription WHERE etatInscription = 2 ORDER BY dateInscription ASC";
    $insrits = $app['db']->fetchall($sql);

    return $app->json($insrits, 200);
});

//route pour recuperer un utilisateur a partir de son id
$app->get('/users/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM inscription WHERE idInscript = ?";
    $user = $app['db']->fetchAssoc($sql, array((String)$id));

    if (empty($user)) {
        $app->abort(204);
    } else {
        return $app->json($user, 200);
    }
});

//route pour accepter un utilisateur
$app->put('/users/{id}/accept', function ($id) use ($app) {
    $sql = "SELECT * FROM inscription WHERE idInscript = ?";
    $user = $app['db']->fetchAssoc($sql, array((String)$id));
    if (empty($user)) {
        $app->abort(404);
    } else {

        if ($user['etatInscription'] != 0) {
            $app->abort(405);
        } else {
            $sql = "UPDATE inscription SET	etatInscription=1  WHERE idInscript = ?";
            $app['db']->executeUpdate($sql, array((String)$id));
            return http_response_code(200);
        }
    }
});

//route pour refuser un utilisateur
$app->put('/users/{id}/refuse', function ($id) use ($app) {
    $sql = "SELECT * FROM inscription WHERE idInscript = ?";
    $user = $app['db']->fetchAssoc($sql, array((String)$id));
    if (empty($user)) {
        $app->abort(404);
    } else {

        if ($user['etatInscription'] != 0) {
            $app->abort(405);
        } else {
            $sql = "UPDATE inscription SET	etatInscription=2  WHERE idInscript = ?";
            $app['db']->executeUpdate($sql, array((String)$id));
            return http_response_code(200);
        }
    }
});

$app->post('/create/{nom}&{prenom}&{date}&{sexe}&{mail}&{tel}&{rue}&{CP}&{ville}&{glisse}&{pointure}&{taille}&{niveau}', function ($nom, $prenom, $date, $sexe, $mail, $tel, $rue, $CP, $ville, $glisse, $pointure, $taille, $niveau) use ($app) {
    $id = uuid();
    //creation de la date du jour
    $dateInscription = date("Y-m-d");
    //etatInscription
    $etatInscription = 0;
    $user = array(
        'idInscript' => $id,
        'nom' => $nom,
        'prenom' => $prenom,
        'dateNais' => $date,
        'sexe' => $sexe,
        'mail' => $mail,
        'tel' => $tel,
        'rue' => $rue,
        'CP' => $CP,
        'ville' => $ville,
        'glisse' => $glisse,
        'pointure' => $pointure,
        'taille' => $taille,
        'niveau' => $niveau,
        'etatInscription' => $etatInscription,
        'dateInscription' => $dateInscription,
    );
    $app['db']->insert('inscription', $user);
    return "id inscrit : " . $id;


});

// default route
$app->get('/', function () {
    return "Liste des méthodes disponibles:
  - /users - renvoi une liste de tous les utilisateurs;
  - /users/waiting : renvoi une liste de tous les utilisateurs en attente de validation;
  - /users/accepted : renvoi une liste de tous les utilisateurs validés;
  - /users/refused : renvoi une liste de tous les utilisateurs refusés;
  - /users/{id} : renvoi les informations de l'utilisateur et retourne une erreur 204 si il n'existe pas;
  - /create/{nom}&{prenom}&{date}&{sexe}&{mail}&{tel}&{rue}&{CP}&{ville}&{glisse}&{pointure}&{taille}&{niveau} créé un utilisateur  
  ";
});


$app->run();