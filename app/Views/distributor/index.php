<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributor Management System</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">


    <link href="<?= base_url('css/index.css') ?>" rel="stylesheet">
</head>

<body>
    <div class="container">

        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary"><i class="fas fa-truck me-2"></i> Distributor Management System
                    </h1>
                    <p class="text-muted mb-0">Manage your distributor network efficiently</p>
                </div>
                <a href="/distributors/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Distributor
                </a>
            </div>
        </div>


        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <label for="regionFilter" class="form-label">Filter by Region:</label>
                    <select id="regionFilter" class="form-select select2">
                        <option value="">All Regions</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= esc($region['region_code']) ?>">
                                <?= esc($region['region_name']) ?> (<?= esc($region['area']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-2">Total Distributors:</h4>
                        <span id="distributorCount" class="badge bg-primary badge-count">0</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i> Distributor List</span>
                <a href="<?= base_url('distributors/export/excel') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="distributorsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th>Distributor</th>
                                <!-- <th>Owner</th> -->
                                <th>Address</th>
                                <th class="text-center">Area</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="distributorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Distributor Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <!-- <p><strong><i class="fas fa-id-card me-2"></i> Distributor Code:</strong> <span
                                        id="distributorCode" class="d-block mt-1"></span></p> -->
                                <p><strong><i class="fas fa-store me-2"></i> Distributor Name:</strong> <span
                                        id="distributorName" class="d-block mt-1"></span></p>
                                <p><strong><i class="fas fa-user-tie me-2"></i> Owner Name:</strong> <span
                                        id="distributorOwner" class="d-block mt-1"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Address:</strong> <span
                                        id="distributorAddress" class="d-block mt-1"></span></p>
                                <p><strong><i class="fas fa-map me-2"></i> Region:</strong> <span id="distributorRegion"
                                        class="d-block mt-1"></span></p>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3"><i class="fas fa-map-marked-alt me-2"></i> Territories</h5>
                        <div id="territoryList" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('js/index.js') ?>"></script>


</body>

</html>