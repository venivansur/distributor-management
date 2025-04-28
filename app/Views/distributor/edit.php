<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Distributor | Distributor Management</title>
    
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="<?= base_url('css/edit.css') ?>" rel="stylesheet">
   
   
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0 text-primary">
                                <i class="fas fa-edit me-2"></i>Edit Distributor
                            </h1>
                            <p class="text-muted mb-0">Update the details of <?= esc($distributor['distributor_name']) ?></p>
                        </div>
                        <a href="/distributors" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                
             
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Validation Errors</h5>
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                
                <div class="form-container">
                    <form id="editForm" action="/distributors/update/<?= esc($distributor['distributor_code']) ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="PUT">
                        
                        
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="kode_distributor" class="form-label">Distributor Code</label>
                                    <input type="text" class="form-control" id="kode_distributor" name="kode_distributor" 
                                           value="<?= esc($distributor['distributor_code']) ?>" readonly>
                                    <div class="form-text">Unique identifier for the distributor</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="distributor_name" class="form-label">Distributor Name</label>
                                    <input type="text" class="form-control" id="distributor_name" name="distributor_name" 
                                           value="<?= old('distributor_name', $distributor['distributor_name']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="region_code" class="form-label">Region</label>
                                    <select class="form-select select2" id="region_code" name="region_code" required>
                                        <option value="">Select Region</option>
                                        <?php foreach ($regions as $region): ?>
                                            <option value="<?= esc($region['region_code']) ?>" 
                                                <?= old('region_code', $distributor['region_code']) == $region['region_code'] ? 'selected' : '' ?>>
                                                <?= esc($region['region_name']) ?> (<?= esc($region['area']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="owner_name" class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" 
                                           value="<?= old('owner_name', $distributor['owner_name']) ?>" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?= old('address', $distributor['address']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                       
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-map-marked-alt me-2"></i>Territories
                            </h5>
                            
                            <div id="territory-container">
                                <?php if (!empty($territories)): ?>
                                    <?php foreach ($territories as $territory): ?>
                                        <div class="territory-card">
                                            <div class="row g-3">
                                                <div class="col-md-5">
                                                    <label class="form-label">Territory Code</label>
                                                    <input type="text" name="territory_code[]" class="form-control" 
                                                           value="<?= esc($territory['territory_code']) ?>" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Territory Name</label>
                                                    <input type="text" name="territory_name[]" class="form-control" 
                                                           value="<?= esc($territory['territory_name']) ?>" required>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-remove-territory">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="territory-card">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label">Territory Code</label>
                                                <input type="text" name="territory_code[]" class="form-control" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Territory Name</label>
                                                <input type="text" name="territory_name[]" class="form-control" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger btn-remove-territory" disabled>
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="button" class="btn btn-add-territory w-100" onclick="addTerritory()">
                                <i class="fas fa-plus-circle me-2"></i>Add Another Territory
                            </button>
                        </div>
                        
                       
                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                            <button type="reset" class="btn btn-outline-secondary btn-responsive">
                                <i class="fas fa-undo me-2"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary btn-responsive">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('js/edit.js') ?>"></script>
  
   
</body>
</html>