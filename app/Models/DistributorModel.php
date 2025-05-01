<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorModel extends Model
{
    protected $table = 'distributors';
    protected $primaryKey = 'distributor_code';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'distributor_code',
        'distributor_name',
        'region_code',
        'owner_name',
        'address'
    ];


    public function getDistributorsWithRegion()
{
    return $this->select('
        distributors.*,
        regions.region_name,
        regions.region_code,
        regions.area,
        SUBSTRING(regions.region_name, 8) as region_number  // Ambil angka setelah "REGION "
    ')
    ->join('regions', 'distributors.region_code = regions.region_code', 'left')
    ->orderBy('CAST(region_number AS UNSIGNED)', 'ASC')  // Urutkan sebagai angka
    ->findAll();
}



}