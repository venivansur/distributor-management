$(document).ready(function () {
  $(".select2").select2({
    theme: "bootstrap-5",
    width: "100%",
    placeholder: "Select a region",
    allowClear: true,
  });

  $("#distributorForm").submit(function (e) {
    e.preventDefault();

    if (!validateForm()) {
      return false;
    }

    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();

    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');

    const formData = new FormData(this);

    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    $.ajax({
      url: form.attr("action") || "/distributors/store",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        handleSubmissionSuccess(response);
      },
      error: function (xhr) {
        handleSubmissionError(xhr);
      },
      complete: function () {
        submitBtn.prop("disabled", false).html(originalText);
      },
    });
  });

  $(document).on("click", "#addTerritoryBtn", function () {
    addTerritory();
  });

  $(document).on("click", ".remove-territory-btn", function () {
    $(this)
      .closest(".territory-card")
      .fadeOut(300, function () {
        $(this).remove();
        reindexTerritoryFields();
      });
  });
});

function validateForm() {
  let isValid = true;
  const form = $("#distributorForm");

  form.find(".is-invalid").removeClass("is-invalid");
  form.find(".invalid-feedback").remove();

  form.find("[required]").each(function () {
    if (!$(this).val()) {
      markFieldAsInvalid($(this), "This field is required");
      isValid = false;
    }
  });

  $(".territory-card").each(function (index) {
    const codeField = $(this).find('input[name="territory_code[]"]');
    const nameField = $(this).find('input[name="territory_name[]"]');

    if (!codeField.val() || !nameField.val()) {
      if (!codeField.val())
        markFieldAsInvalid(codeField, "Territory code is required");
      if (!nameField.val())
        markFieldAsInvalid(nameField, "Territory name is required");
      isValid = false;
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

function markFieldAsInvalid(field, message) {
  field.addClass("is-invalid");
  field.after(`<div class="invalid-feedback">${message}</div>`);
}

function handleSubmissionSuccess(response) {
  if (response.redirect) {
    window.location.href = response.redirect;
    return;
  }

  const successMessage =
    response.message || "Distributor has been successfully saved.";
  const warningMessage =
    response.message || "Operation completed with warnings.";

  Swal.fire({
    title: response.success ? "Success!" : "Warning",
    text: response.success ? successMessage : warningMessage,
    icon: response.success ? "success" : "warning",
    confirmButtonText: "OK",
    confirmButtonColor: "var(--primary-color)",
  }).then((result) => {
    if (result.isConfirmed) {
      if (!response.redirect && response.success) {
        window.location.href = "/distributors";
      }

      if (!response.redirect) {
        $("#editForm")[0].reset();
        $(".territory-card:not(:first)").remove();
      }
    }
  });
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
        markFieldAsInvalid(input, messages.join(", "));
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
}

function addTerritory() {
  const container = $("#territory-container");
  const index = $(".territory-card").length;

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
            <button type="button" class="btn btn-danger w-100 remove-territory-btn">
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

function reindexTerritoryFields() {
  $(".territory-card").each(function (index) {
    $(this)
      .find("input")
      .each(function () {
        const name = $(this)
          .attr("name")
          .replace(/\[\d+\]/, `[${index}]`);
        $(this).attr("name", name);
      });
  });
}
