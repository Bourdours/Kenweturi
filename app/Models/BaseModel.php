<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle parent dont héritent tous les modèles de l'application.
 * Regroupe les méthodes communes à toutes les tables.
 */
class BaseModel extends Model
{
    // Toutes les tables retournent des tableaux PHP par défaut
    protected $returnType = 'array';
}
