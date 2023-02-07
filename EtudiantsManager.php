<?php
class EtudiantsManager
{
  private $_db; // Instance de PDO
  
  public function __construct($db)
  {
    $this->setDb($db);
  }
  
  public function add(Etudiant $perso)
  {
    $q = $this->_db->prepare('INSERT INTO etudiants(nom) VALUES(:nom)');
    $q->bindValue(':nom', $perso->nom());
    $q->execute();
    
    $perso->hydrate([
      'id' => $this->_db->lastInsertId(),
      'degats' => 0,
    ]);
  }
  
  public function count()
  {
    return $this->_db->query('SELECT COUNT(*) FROM etudiants')->fetchColumn();
  }
  
  public function delete(Etudiant $perso)
  {
    $this->_db->exec('DELETE FROM etudiants WHERE id = '.$perso->id());
  }
  
  public function exists($info)
  {
    if (is_int($info)) // On veut voir si tel Etudiant ayant pour id $info existe.
    {
      return (bool) $this->_db->query('SELECT COUNT(*) FROM etudiants WHERE id = '.$info)->fetchColumn();
    }
    
    // Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
    
    $q = $this->_db->prepare('SELECT COUNT(*) FROM etudiants WHERE nom = :nom');
    $q->execute([':nom' => $info]);
    
    return (bool) $q->fetchColumn();
  }
  
  public function get($info)
  {
    if (is_int($info))
    {
      $q = $this->_db->query('SELECT id, nom, degats FROM etudiants WHERE id = '.$info);
      $donnees = $q->fetch(PDO::FETCH_ASSOC);
      
      return new Etudiant($donnees);
    }
    else
    {
      $q = $this->_db->prepare('SELECT id, nom, degats FROM etudiants WHERE nom = :nom');
      $q->execute([':nom' => $info]);
    
      return new Etudiant($q->fetch(PDO::FETCH_ASSOC));
    }
  }





  
  
  public function getList($nom)
  {
    $persos = [];
    
    $q = $this->_db->prepare('SELECT id, nom, degats FROM etudiants WHERE nom <> :nom ORDER BY nom');
    $q->execute([':nom' => $nom]);
    
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $persos[] = new Etudiant($donnees); 
    }
    
    return $persos;
  }
  
  public function update(Etudiant $perso)
  {
    $q = $this->_db->prepare('UPDATE etudiants SET degats = :degats WHERE id = :id');
    
    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
    $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
    
    $q->execute();
  }
  
  public function setDb(PDO $db)
  {
    $this->_db = $db;
  }
}