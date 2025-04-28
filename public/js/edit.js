$(document).ready(function () {
  $(".select2").select2({
    theme: "bootstrap-5",
    width: "100%",
    placeholder: "Select a region",
    allowClear: true,
  });

  $(document).on("click", ".btn-remove-territory:not(:disabled)", function () {
    const card = $(this).closest(".territory-card");
    Swal.fire({
      title: "Are you sure?",
      text: "This territory will be removed!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Yes, remove it!",
    }).then((result) => {
      if (result.isConfirmed) {
        card.fadeOut(300, function () {
          $(this).remove();
          validateTerritories();
        });
      }
    });
  });

  $("#editForm").submit(function (e) {
    e.preventDefault();

    if (!validateForm()) {
      return false;
    }

    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();

    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');

    const formData = new FormData(this);
    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        handleSubmissionResponse(response);
      },
      error: function (xhr) {
        handleSubmissionError(xhr);
      },
      complete: function () {
        submitBtn.prop("disabled", false).html(originalText);
      },
    });
  });
});

function addTerritory() {
  const container = $("#territory-container");
  const newCard = $(`
        <div class="territory-card mb-3 animate__animated animate__fadeIn">
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
                    <button type="button" class="btn btn-danger w-100 btn-remove-territory">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `);

  container.append(newCard);

  setTimeout(() => {
    $("html, body").animate(
      {
        scrollTop: newCard.offset().top - 100,
      },
      500
    );
  }, 50);
}

function validateForm() {
  let isValid = true;
  const form = $("#editForm");

  form.find(".is-invalid").removeClass("is-invalid");
  form.find(".invalid-feedback").remove();

  form.find("[required]").each(function () {
    if (!$(this).val()) {
      $(this).addClass("is-invalid");
      $(this).after(
        '<div class="invalid-feedback">This field is required</div>'
      );
      isValid = false;
    }
  });

  if ($(".territory-card").length === 0) {
    Swal.fire("Error", "Please add at least one territory", "error");
    isValid = false;
  }

  $(".territory-card").each(function () {
    const code = $(this).find('input[name="territory_code[]"]').val();
    const name = $(this).find('input[name="territory_name[]"]').val();

    if (!code || !name) {
      isValid = false;
      if (!code) {
        $(this)
          .find('input[name="territory_code[]"]')
          .addClass("is-invalid")
          .after(
            '<div class="invalid-feedback">Territory code is required</div>'
          );
      }
      if (!name) {
        $(this)
          .find('input[name="territory_name[]"]')
          .addClass("is-invalid")
          .after(
            '<div class="invalid-feedback">Territory name is required</div>'
          );
      }
    }
  });

  if (!isValid) {
    Swal.fire({
      title: "Validation Error",
      text: "Please fill all required fields correctly",
      icon: "error",
      confirmButtonText: "OK",
    });

    $("html, body").animate(
      {
        scrollTop: form.find(".is-invalid").first().offset().top - 100,
      },
      500
    );
  }

  return isValid;
}

function handleSubmissionResponse(response) {
  if (response.success) {
    if (response.redirect) {
      window.location.href = response.redirect;
      return;
    }

    Swal.fire({
      title: "Success!",
      text: response.message || "Distributor has been successfully updated.",
      icon: "success",
      confirmButtonText: "OK",
      confirmButtonColor: "var(--primary-color)",
      allowOutsideClick: false,
    }).then((result) => {
      if (result.isConfirmed && !response.redirect) {
        window.location.href = "/distributors";
      }
    });
  } else {
    let errorMessage = response.message || "Failed to update distributor.";

    if (response.errors) {
      errorMessage = Object.values(response.errors).flat().join("<br>");
    }

    Swal.fire({
      title: "Error!",
      html: errorMessage,
      icon: "error",
      confirmButtonText: "OK",
      timer: 10000,
      timerProgressBar: true,
    });
  }
}

function handleSubmissionError(xhr) {
  let errorMessage = "An error occurred while submitting the form.";

  if (xhr.responseJSON) {
    if (xhr.responseJSON.message) {
      errorMessage = xhr.responseJSON.message;
    }
    if (xhr.responseJSON.errors) {
      $.each(xhr.responseJSON.errors, function (field, messages) {
        const input = $(`[name="${field}"]`);
        input.addClass("is-invalid");
        input.after(
          `<div class="invalid-feedback">${messages.join(", ")}</div>`
        );
      });
      errorMessage = "Please correct the errors in the form.";
    }
  } else if (xhr.statusText) {
    errorMessage = xhr.statusText;
  }

  Swal.fire({
    title: "Error!",
    text: errorMessage,
    icon: "error",
    confirmButtonText: "OK",
  });

  if ($(".is-invalid").length > 0) {
    $("html, body").animate(
      {
        scrollTop: $(".is-invalid").first().offset().top - 100,
      },
      500
    );
  }
}
