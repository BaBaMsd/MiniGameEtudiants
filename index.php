<div class="py-2">
<div class="container py-2 " >
<h1>Mini jeu de combat</h1>
<hr>
<?php
// On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_GET['deconnexion']))
{
  session_destroy();
  header('Location: .');
  exit();
}

$dsn = 'mysql:dbname=db;host=127.0.0.1';
$user = 'root';
$password = '';

$db = new PDO($dsn, $user, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // On émet une alerte à chaque fois qu'une requête a échoué.

$manager = new EtudiantsManager($db);

if (isset($_SESSION['perso'])) // Si la session perso existe, on restaure l'objet.
{
  $perso = $_SESSION['perso'];
}

if (isset($_POST['creer']) && isset($_POST['nom'])) // Si on a voulu créer un personnage.
{
  $perso = new Etudiant(['nom' => $_POST['nom']]); // On crée un nouveau personnage.
  
  if (!$perso->nomValide())
  {
    $message = 'Le nom choisi est invalide.';
    unset($perso);
  }
  elseif ($manager->exists($perso->nom()))
  {
    $message = 'Le nom du etudiant est déjà pris.';
    unset($perso);
  }
  else
  {
    $manager->add($perso);
  }
}

elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) // Si on a voulu utiliser un etudiant.
{
  if ($manager->exists($_POST['nom'])) // Si celui-ci existe.
  {
    $perso = $manager->get($_POST['nom']);
  }
  else
  {
    $message = 'Ce etudiant n\'existe pas !'; // S'il n'existe pas, on affichera ce message.
  }
}

elseif (isset($_GET['frapper'])) // Si on a cliqué sur une bouton pour le frapper.
{
  if (!isset($perso))
  {
    $message = 'Merci de créer un etudiant ou de vous identifier.';
  }
  
  else
  {
    if (!$manager->exists((int) $_GET['frapper']))
    {
      $message = 'Le etudiant que vous voulez frapper n\'existe pas !';
    }
    
    else
    {
      $persoAFrapper = $manager->get((int) $_GET['frapper']);
      
      $retour = $perso->frapper($persoAFrapper); // On stocke dans $retour les éventuelles erreurs ou messages que renvoie la méthode frapper.
      
      switch ($retour)
      {
        case Etudiant::CEST_MOI :
          $message = 'Mais... pourquoi voulez-vous vous frapper ???';
          break;
        
        case Etudiant::ETUDIANT_FRAPPE :
          $message = 'Le etudiant a bien été frappé !';
          
          $manager->update($perso);
          $manager->update($persoAFrapper);        
          break;
        
        case Etudiant::ETUDIANT_TUE :
          $message = 'Vous avez tué ce etudiant !';
          
          $manager->update($perso);
          $manager->delete($persoAFrapper);
          
          break;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Mini jeu de combat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>  

    <meta charset="utf-8" />
  </head>
<div class=" py-2">
  <body>
<?php
if (isset($message)) // On a un message à afficher ?
{ ?>
  <div class='row justify-content-center'>
          <div class='col-md-6'>
              <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                  <p>"<?php echo '', $message ; // Si oui, on l'affiche. ?></p>
                  <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>
          </div>
  </div>
  
<?php }

if (isset($perso)) // Si on utilise un etudiant (nouveau ou pas).
{
?>
<div class="row"> 
<div class="col-sm-6">
 <div class="card">
    <div class="card-header bg-white">
      <h5>Mes informations</h5>
    </div>
    <div class="card-body px-2 py-1">
      <p>
        Nom : <?= htmlspecialchars($perso->nom()) ?><br />
        Dégâts : <?= $perso->degats() ?><br />
        Puissance : <?= 100 - $perso->degats() ?>
        <p><a href="?deconnexion=1" class="btn btn btn-primary px-4 ">Déconnexion</a></p>
      </p>
    </div>
  </div>
</div> 
<div class="col-sm-6">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5>Nombre de etudiants créés :</h5>
    </div>
    <div class="card-body px-5 py-5  ">
    <center><h1><?= $manager->count() ?></h1></center>
    </div>
    
  </div>
</div>
</div> 
<div class="py-3">
  <div class="card">
          
        <div class="card-header bg-white">
          <h5><center>Qui frapper ?</center></h5>
        </div>
        <div class="card-body px-2 py-2">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Nom</th>
                <th scope="col">Dégâts</th>
                <th>Puissance</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
  <?php

  $persos = $manager->getList($perso->nom());

  if (empty($persos))
  {
    echo 'Personne à frapper !';
  }

  else
  {
    foreach ($persos as $unPerso)
    { ?>
              <tr>
                <td><?php echo $unPerso->nom(); ?></td>
                <td><?php echo $unPerso->degats() ?></td>
                <td><?php echo 100 - $unPerso->degats() ?></td>
                <td><?php echo "<center><a href='?frapper=", $unPerso->id(),"' class='btn btn btn-primary text-white px-4 '>Frapper</center>" ?></td> 
              </tr>
  <?php }?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>  

<?php 
}
}
else
{
?>
  <div class="px-7 c"> 
    <div class="card col-md-6 ">
    <div class="card-header bg-white">
      <center><h5>Choisir un Etudiant</h5></center>
    </div>
    <div class="card-body">
    <form action="" class="row g-3" method="post">
    <div class="col-11">
      <centre><b>Nom Etudiant: </b><input type="text" class="form-control" name="nom" maxlength="50" /></centre>
    </div>
    <div class="col-md-7">
      <button class="btn btn-primary" type="submit"value="Créer ce etudiant" name="creer">Créer ce etudiant</button>
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary" type="submit"value="Créer ce etudiant" name="utiliser">Utiliser ce etudiant</button>
    </div>
    
    </div>
    </form>    
  </div>
  </div>  
  </div>

   
<?php
}
?>
</div>
</div>
</div>
  </body>
</html>

<?php
if (isset($perso)) // Si on a créé un etudiant, on le stocke dans une variable session afin d'économiser une requête SQL.
{
  $_SESSION['perso'] = $perso;
}
