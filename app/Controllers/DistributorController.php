<?php

namespace App\Controllers;
use App\Models\RegionModel;
use App\Models\DistributorModel;
use App\Models\TerritoryModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class DistributorController extends BaseController
{


    public function index()
    {
        $regionModel = new RegionModel();
        $distributorModel = new DistributorModel();

        $regions = $regionModel->findAll();
        $distributors = $distributorModel->findAll();

        return view('distributor/index', [
            'regions' => $regions,
            'distributors' => $distributors
        ]);

    }


    public function create()
    {
        helper(['form']);


        $regionModel = new RegionModel();
        $regions = $regionModel->findAll();

        return view('distributor/create', [
            'regions' => $regions
        ]);


    }


    public function store()
    {
        try {
            $db = \Config\Database::connect();
            $db->transBegin();


            $rules = [
                'kode_distributor' => 'required',
                'nama_distributor' => 'required',
                'kode_region' => 'required',
                'nama_owner' => 'required',
                'alamat' => 'required',
                'territory_code.*' => 'required',
                'territory_name.*' => 'required'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $distributorModel = new DistributorModel();
            $territoryModel = new TerritoryModel();

            $data = [
                'distributor_code' => $this->request->getPost('kode_distributor'),
                'distributor_name' => $this->request->getPost('nama_distributor'),
                'region_code' => $this->request->getPost('kode_region'),
                'owner_name' => $this->request->getPost('nama_owner'),
                'address' => $this->request->getPost('alamat'),
            ];


            $existing = $distributorModel->where('distributor_code', $data['distributor_code'])->first();
            if ($existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'The distributor code is already registered.'
                ]);
            }


            if (!$distributorModel->insert($data)) {
                throw new \RuntimeException('Failed to save distributor data');
            }


            $territoryCodes = $this->request->getPost('territory_code');
            $territoryNames = $this->request->getPost('territory_name');

            if ($territoryCodes && $territoryNames) {
                for ($i = 0; $i < count($territoryCodes); $i++) {
                    $code = $territoryCodes[$i];
                    $name = $territoryNames[$i];

                    if (!empty($code) && !empty($name)) {

                        $existingTerritory = $territoryModel->where('territory_code', $code)->first();
                        if ($existingTerritory) {
                            throw new \RuntimeException("Territory code $code already registered");
                        }


                        if (
                            !$territoryModel->insert([
                                'territory_code' => $code,
                                'territory_name' => $name,
                                'distributor_code' => $data['distributor_code']
                            ])
                        ) {
                            throw new \RuntimeException("Failed to save territory with code $code");
                        }
                    }
                }
            }


            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Distributors and territories were successfully added.'
            ]);

        } catch (\Exception $e) {

            isset($db) && $db->transRollback();

            log_message('error', 'Error in DistributorController::store - ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }







    public function edit($distributor_code)
    {
        $distributorModel = new DistributorModel();
        $regionModel = new RegionModel();
        $territoryModel = new TerritoryModel();


        $distributor = $distributorModel->find($distributor_code);

        if (!$distributor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Distributor not found.');
        }


        $regions = $regionModel->findAll();


        $territories = $territoryModel
            ->where('distributor_code', $distributor_code)
            ->findAll();


        return view('distributor/edit', [
            'distributor' => $distributor,
            'regions' => $regions,
            'territories' => $territories
        ]);
    }




    public function update($distributor_code)
    {
        try {
            $db = \Config\Database::connect();
            $db->transBegin();

            $rules = [
                'distributor_name' => 'required',
                'region_code' => 'required',
                'owner_name' => 'required',
                'address' => 'required',
                'territory_code.*' => 'required',
                'territory_name.*' => 'required'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $distributorModel = new DistributorModel();
            $territoryModel = new TerritoryModel();


            $distributor = $distributorModel->find($distributor_code);
            if (!$distributor) {
                throw new \RuntimeException('Distributor not found.');
            }


            $data = $this->request->getPost([
                'distributor_name',
                'region_code',
                'owner_name',
                'address',
            ]);


            if (!$distributorModel->update($distributor_code, $data)) {
                throw new \RuntimeException('Failed to save distributor data');
            }


            $territoryCodes = $this->request->getPost('territory_code');
            $territoryNames = $this->request->getPost('territory_name');

            if ($territoryCodes && $territoryNames) {

                $territoryModel->where('distributor_code', $distributor_code)->delete();


                for ($i = 0; $i < count($territoryCodes); $i++) {
                    $code = $territoryCodes[$i];
                    $name = $territoryNames[$i];

                    if (!empty($code) && !empty($name)) {

                        $existing = $territoryModel
                            ->where('territory_code', $code)
                            ->where('distributor_code !=', $distributor_code)
                            ->first();

                        if ($existing) {
                            throw new \RuntimeException("Territory code $code already used by other distributors");
                        }


                        if (
                            !$territoryModel->insert([
                                'territory_code' => $code,
                                'territory_name' => $name,
                                'distributor_code' => $distributor_code,
                            ])
                        ) {
                            throw new \RuntimeException("Failed to save territory with code $code");
                        }
                    }
                }
            }


            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Distributor and territory data have been successfully updated.'
            ]);

        } catch (\Exception $e) {

            isset($db) && $db->transRollback();

            log_message('error', 'Error in DistributorController::update - ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }





    public function delete($distributor_code)
    {
        try {
            $db = \Config\Database::connect();
            $db->transBegin();

            $model = new DistributorModel();
            $territoryModel = new TerritoryModel();


            $distributor = $model->find($distributor_code);
            if (!$distributor) {
                throw new \RuntimeException('Distributor not found');
            }


            $territoryModel->where('distributor_code', $distributor_code)->delete();

            if (!$model->delete($distributor_code)) {
                throw new \RuntimeException('Failed to delete distributor data');
            }

            $db->transCommit();

            if ($this->request->isAJAX()) {
                $newCount = $model->countAll();
                return $this->response->setJSON([
                    'success' => true,
                    'new_count' => $newCount,
                    'message' => 'Distributor successfully removed'
                ]);
            }

            return redirect()->to('/distributors')->with('success', 'Distributor successfully removed');

        } catch (\Exception $e) {
            isset($db) && $db->transRollback();

            log_message('error', 'Error in DistributorController::delete - ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Failed to remove distributor: ' . $e->getMessage());
        }
    }


    public function detail($code)
    {
        $distributorModel = new DistributorModel();
        $territoryModel = new TerritoryModel();


        $distributor = $distributorModel->where('distributor_code', $code)->first();
        $distributor = $distributorModel->select('distributors.*, regions.region_name')
            ->join('regions', 'regions.region_code = distributors.region_code')
            ->where('distributor_code', $code)
            ->first();
        if (!$distributor) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Distributor not found']);
        }

        $territories = $territoryModel->where('distributor_code', $code)->findAll();

        return $this->response->setJSON([
            'distributor_name' => $distributor['distributor_name'],
            'owner_name' => $distributor['owner_name'],
            'address' => $distributor['address'],
            'region_name' => $distributor['region_code'],
            'territories' => $territories
        ]);
    }

    public function exportExcel()
    {
        try {
            ini_set('memory_limit', '512M');
            $distributorModel = new DistributorModel();


            $distributors = $distributorModel->select('
                distributors.distributor_code,
                distributors.distributor_name,
                distributors.region_code,
                regions.region_name,
                distributors.owner_name,
                distributors.address
            ')
                ->join('regions', 'distributors.region_code = regions.region_code', 'left')
                ->findAll();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();


            $headers = [
                'A' => 'Kode Distributor',
                'B' => 'Nama Distributor',
                'C' => 'Kode Region',
                'D' => 'Nama Region',
                'E' => 'Nama Owner',
                'F' => 'Alamat'
            ];

            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . '1', $header);
            }


            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);


            $row = 2;
            foreach ($distributors as $d) {
                $sheet->setCellValue('A' . $row, $d['distributor_code']);
                $sheet->setCellValue('B' . $row, $d['distributor_name']);
                $sheet->setCellValue('C' . $row, $d['region_code']);
                $sheet->setCellValue('D' . $row, $d['region_name'] ?? '-');
                $sheet->setCellValue('E' . $row, $d['owner_name']);
                $sheet->setCellValue('F' . $row, $d['address']);
                $row++;
            }


            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }


            $sheet->getProtection()->setSheet(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'export_distributor_' . date('Ymd_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Export Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate file Excel: ' . $e->getMessage());
        }
    }

    public function data()
    {
        try {
            $request = service('request');
            $model = new DistributorModel();


            $draw = $request->getGet('draw');
            $start = $request->getGet('start') ?? 0;
            $length = $request->getGet('length') ?? 10;
            $search = $request->getGet('search')['value'] ?? '';
            $regionCode = $request->getGet('region_code');

            $length = min($length, 100);


            $builder = $model->db->table('distributors')
                ->select('
                    distributors.distributor_code,
                    distributors.distributor_name,
                    distributors.owner_name,
                    distributors.address,
                    regions.region_code,
                    regions.region_name,  
                    regions.area
                ')
                ->join('regions', 'distributors.region_code = regions.region_code', 'left');


            if (!empty($regionCode)) {
                $builder->where('regions.region_code', $regionCode);
            }


            if (!empty($search)) {
                $builder->groupStart()
                    ->like('distributors.distributor_name', $search)
                    ->orLike('regions.region_name', $search)
                    ->orLike('distributors.owner_name', $search)
                    ->orLike('distributors.address', $search)
                    ->orLike('regions.area', $search)
                    ->groupEnd();
            }


            $totalRecords = $builder->countAllResults(false);

            $builder->limit($length, $start);


            $data = $builder->get()->getResultArray();


            $filteredRecords = $builder->countAllResults(false);

            return $this->response->setJSON([
                'draw' => (int) $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in DistributorController::data - ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'draw' => $this->request->getGet('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'A server error occurred while processing data'
            ]);
        }
    }


    public function count()
    {
        try {
            $regionCode = $this->request->getGet('region_code');

            $builder = (new DistributorModel())->builder();

            if (!empty($regionCode)) {
                $builder->where('region_code', $regionCode);
            }

            return $this->response->setJSON([
                'success' => true,
                'count' => $builder->countAllResults()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Count Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'count' => 0,
                'message' => 'Failed to get count'
            ]);
        }
    }

}

