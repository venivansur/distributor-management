
function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}


function getCSRFToken() {
  return $('meta[name="csrf-token"]').attr('content');
}


function updateDistributorCount() {
  const regionCode = $("#regionFilter").val();
  $.get("/distributors/count", { region_code: regionCode })
    .done(function(response) {
      $("#distributorCount").text(response.count || "0");
    })
    .fail(function(xhr) {
      console.error("Count error:", xhr.responseText);
      $("#distributorCount").text("Error");
      Swal.fire("Error", "Failed to load distributor count", "error");
    });
}


function showDistributorDetails(distributorCode) {
  const sanitizedCode = escapeHtml(distributorCode);
  const loading = Swal.fire({
    title: "Loading",
    html: "Fetching distributor details...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  fetch(`/distributors/detail/${sanitizedCode}`)
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.json();
    })
    .then((data) => {
      loading.close();

      $("#distributorCode").text(data.distributor_code || "N/A");
      $("#distributorName").text(data.distributor_name || "N/A");
      $("#distributorOwner").text(data.owner_name || "N/A");
      $("#distributorAddress").text(data.address || "N/A");
      $("#distributorRegion").text(data.region_name || "N/A");

    
      const territoryList = $("#territoryList");
      territoryList.empty();
      
      if (data.territories && data.territories.length > 0) {
        data.territories.forEach((t) => {
          territoryList.append(`
            <span class="badge bg-light text-dark border m-1">
              <i class="fas fa-map-pin text-primary me-1"></i>
              ${escapeHtml(t.territory_name)}
            </span>
          `);
        });
      } else {
        territoryList.html('<div class="text-muted">No territories assigned</div>');
      }

     
      distributorModal.show();
    })
    .catch((error) => {
      loading.close();
      console.error("Error loading distributor details:", error);
      Swal.fire({
        title: "Error",
        text: "Failed to load distributor details",
        icon: "error",
        confirmButtonText: "OK",
      });
    });
}


function deleteDistributor(code) {
  const sanitizedCode = escapeHtml(code);
  
  Swal.fire({
    title: "Confirm Deletion",
    html: `<p>Are you sure you want to delete distributor <strong>${sanitizedCode}</strong>?</p>
           <p class="text-danger"><strong>This action cannot be undone.</strong></p>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
    backdrop: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Processing",
        html: "Deleting distributor...",
        timerProgressBar: true,
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
      });

      $.ajax({
        url: `/distributors/delete/${sanitizedCode}`,
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": getCSRFToken(),
          "X-Requested-With": "XMLHttpRequest",
        },
        success: function(data) {
          if (data.success) {
            $("#distributorsTable").DataTable().ajax.reload();
            updateDistributorCount();
            
            Swal.fire({
              title: "Deleted!",
              text: "The distributor has been deleted.",
              icon: "success",
              confirmButtonText: "OK",
            });
          } else {
            throw new Error(data.message || "Delete failed");
          }
        },
        error: function(xhr) {
          console.error("Error:", xhr.responseText);
          Swal.fire({
            title: "Error",
            text: xhr.responseJSON?.message || "Failed to delete distributor",
            icon: "error",
            confirmButtonText: "OK",
          });
        }
      });
    }
  });
}


$(document).ready(function () {
  
  $(".select2").select2({
    theme: "bootstrap-5",
    width: "100%",
    placeholder: "Select a region",
  });

  
  window.distributorModal = new bootstrap.Modal(document.getElementById('distributorModal'));


  const table = $("#distributorsTable").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "/distributors/data",
      type: "GET",
      data: function (d) {
        d.region_code = $("#regionFilter").val();
      },
      error: function (xhr, error, thrown) {
        console.error("Ajax error:", xhr.responseText);
        Swal.fire({
          title: "Error",
          text: "Failed to load distributor data",
          icon: "error",
        });
      },
    },
    order: [[0, "asc"]],
    columns: [
      {
        data: "region_name",
        name: "regions.region_name",
        className: "fw-semibold",
      },
      {
        data: "distributor_name",
        name: "distributors.distributor_name",
        render: function (data) {
          return `<div class="d-flex align-items-center">
            <i class="fas fa-building me-2 text-muted"></i>
            ${escapeHtml(data)}
          </div>`;
        },
      },   // {
        //   data: "owner_name",
        //   name: "distributors.owner_name",
        // },
      {
        data: "address",
        name: "distributors.address",
        render: function (data) {
          return `<div class="text-truncate" style="max-width: 300px;" title="${escapeHtml(data)}">
            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
            ${escapeHtml(data)}
          </div>`;
        },
      },
      {
        data: "area",
        name: "regions.area",
        className: "text-center",
        render: function (data) {
          const badgeClass = data === "East" ? "bg-primary" : "bg-success";
          const icon = data === "East" ? "sun" : "tree";
          return `<span class="badge rounded-pill ${badgeClass}">
            <i class="fas fa-${icon} me-1"></i>
            ${escapeHtml(data)}
          </span>`;
        },
      },
      {
        data: "distributor_code",
        name: "distributors.distributor_code",
        className: "text-end",
        orderable: false,
        render: function (data) {
          return `
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="showDistributorDetails('${escapeHtml(data)}')">
                <i class="fas fa-eye"></i> View
              </button>
              <a href="/distributors/edit/${encodeURIComponent(data)}" class="btn btn-outline-warning">
                <i class="fas fa-edit"></i> Edit
              </a>
              <button class="btn btn-outline-danger" onclick="deleteDistributor('${escapeHtml(data)}')">
                <i class="fas fa-trash-alt"></i> Delete
              </button>
            </div>
          `;
        },
      },
    ],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
    },
    drawCallback: function () {
      updateDistributorCount();
    },
  });

  
  $("#regionFilter").change(function () {
    table.ajax.reload();
    updateDistributorCount();
  });

  
  updateDistributorCount();
});