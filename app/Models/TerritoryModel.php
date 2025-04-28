<?php

namespace App\Models;

use CodeIgniter\Model;

class TerritoryModel extends Model
{
    protected $table            = 'territories';
    protected $primaryKey       = 'territory_code'; 
    protected $useAutoIncrement = false; 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = ['territory_code', 'territory_name', 'distributor_code'];

    
    protected $useTimestamps = false;

   
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
}
