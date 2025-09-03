jQuery(document).ready(function ($) {
  $("#search_person").on("click", function () {
    const documentNumberSelector = $(this).data("document-number");
    // Get the document number
    var documentNumber = $(documentNumberSelector).val();

    if (documentNumber.length !== 8) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El número de documento debe tener 8 dígitos.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    const fullNameField = $(this).data("full-name");
    const namesField = $(this).data("names");
    const lastNameField = $(this).data("last-name");
    const addressField = $(this).data("address");

    $("#wcfm-content").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    wp.apiFetch({
      path: `/v1/person/${documentNumber}`,
      method: "GET",
    })
      .then(function (response) {
        if (fullNameField) {
          $(`#${fullNameField}`).val(response.nombre_completo);
        }
        if (namesField) {
          $(`#${namesField}`).val(response.nombres);
        }
        if (lastNameField) {
          $(`#${lastNameField}`).val(
            response.apellido_paterno + " " + response.apellido_materno
          );
        }
        if (addressField) {
          $(`#${addressField}`).val(response.direccion);
        }

        $("#wcfm-content .wcfm-message")
          .html(
            '<span class="wcicon-status-completed"></span>' +
              "Datos encontrados correctamente."
          )
          .addClass("wcfm-success")
          .slideDown("slow");
      })
      .catch(function (error) {
        $("#wcfm-content .wcfm-message")
          .html('<span class="wcicon-status-cancelled"></span>' + error.message)
          .addClass("wcfm-error")
          .slideDown();
      })
      .finally(function () {
        wcfmMessageHide();
        $("#wcfm-content").unblock();
      });
  });
});
