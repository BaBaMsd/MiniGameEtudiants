<?php
class Etudiant
{
  private $_degats,
          $_id,
          $_nom;
  
  const CEST_MOI = 1; // Constante renvoyée par la méthode `frapper` si on se frappe soi-même.
  const ETUDIANT_TUE = 2; // Constante renvoyée par la méthode `frapper` si on a tué le Etudiant en le frappant.
  const ETUDIANT_FRAPPE = 3; // Constante renvoyée par la méthode `frapper` si on a bien frappé le Etudiant.
  
  
  public function __construct(array $donnees)
  {
    $this->hydrate($donnees);
  }
  
  public function frapper(Etudiant $etu)
  {
    if ($etu->id() == $this->_id)
    {
      return self::CEST_MOI;
    }
    
    // On indique au Etudiant qu'il doit recevoir des dégâts.
    // Puis on retourne la valeur renvoyée par la méthode : self::Etudiant_TUE ou self::Etudiant_FRAPPE
    return $etu->recevoirDegats();
  }
  
  public function hydrate(array $donnees)
  {
    foreach ($donnees as $key => $value)
    {
      $method = 'set'.ucfirst($key);
      
      if (method_exists($this, $method))
      {
        $this->$method($value);
      }
    }
  }
  
  public function recevoirDegats()
  {
    $this->_degats += 5;
    
    // Si on a 100 de dégâts ou plus, on dit que le Etudiant a été tué.
    if ($this->_degats >= 100)
    {
      return self::ETUDIANT_TUE;
    }
    
    // Sinon, on se contente de dire que le Etudiant a bien été frappé.
    return self::ETUDIANT_FRAPPE;
  }
  
  
  // GETTERS //
  

  public function degats()
  {
    return $this->_degats;
  }
  
  public function id()
  {
    return $this->_id;
  }
  
  public function nom()
  {
    return $this->_nom;
  }
  
  public function setDegats($degats)
  {
    $degats = (int) $degats;
    
    if ($degats >= 0 && $degats <= 100)
    {
      $this->_degats = $degats;
    }
  }
  
  public function setId($id)
  {
    $id = (int) $id;
    
    if ($id > 0)
    {
      $this->_id = $id;
    }
  }
  
  public function setNom($nom)
  {
    if (is_string($nom))
    {
      $this->_nom = $nom;
    }
  }

  public function nomValide()
  {
    return !empty($this->_nom);
  }
}