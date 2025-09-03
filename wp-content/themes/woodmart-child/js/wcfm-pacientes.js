jQuery(document).ready(function ($) {
  $wcfm_datatable = $("#wcfm-datatable").DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    pageLength: 10,
    language: $.parseJSON(dataTables_language),
    // stateSave: true,
    columns: [
      { responsivePriority: 1 },
      { responsivePriority: 1 },
      { responsivePriority: 2 },
      { responsivePriority: 3 },
      { responsivePriority: 4 },
      { responsivePriority: 1 },
    ],
    columnDefs: [
      { targets: 0, orderable: true },
      { targets: 1, orderable: true },
      { targets: 2, orderable: true },
      { targets: 3, orderable: true },
      { targets: 4, orderable: false },
      { targets: 5, orderable: false },
    ],
    order: [[0, "desc"]],
    dom: "Bfrtip",
    buttons: [
      {
        extend: "print",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "pdfHtml5",
        orientation: "landscape",
        pageSize: "LEGAL",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "excelHtml5",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "csv",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
    ],
    ajax: {
      type: "POST",
      url: wcfm_params.ajax_url,
      data: function (d) {
        (d.action = "wcfm_ajax_controller"),
          (d.controller = "wcfm-pacientes"),
          (d.wcfm_ajax_nonce = wcfm_params.wcfm_ajax_nonce);
      },
      complete: function () {
        // Fire wcfm-table refresh complete
        $(document.body).trigger("updated_wcfm-datatable");
      },
    },
  });
  // Filters
  if ($(".wcfm_filters_wrap").length > 0) {
    $(".dataTable").before($(".wcfm_filters_wrap"));
    $(".wcfm_filters_wrap").css("display", "inline-block");
  }

  $("#export-excel").click(function (event) {
    event.preventDefault();
    const data = $wcfm_datatable.ajax.params();
    const params = $.param({
      order: data.order,
      search: data.search,
    });
    const excel_url = `/pacientes-excel/${data.wcfm_ajax_nonce}/?${params}`;
    window.open(excel_url, "_blank");
  });

  $("#import_patients_button").click(function (event) {
    event.preventDefault();
    $.colorbox({
      href: "#import-patients-modal",
      inline: true,
      width: $popup_width,
    });
  });
  $(".close-modal").click(function (event) {
    event.preventDefault();
    $.colorbox.close();
  });
  $("#import-patients-modal").on("submit", function (e) {
    e.preventDefault();

    var formData = new FormData();
    formData.append("action", "import_patients");
    formData.append("nonce", wcfm_params.wcfm_ajax_nonce);
    formData.append("import_file", $("#import_patients_file")[0].files[0]);

    $(this).block({
      message: "Procesando...",
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    $.ajax({
      url: wcfm_params.ajax_url,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          alert(response.data);
          $wcfm_datatable.ajax.reload();
          $.colorbox.close();
          $("#import_patients_file").val("");
        } else {
          alert("Error: " + response.data);
        }
      },
      error: function (error) {
        alert("Error al procesar la solicitud");
      },
      complete: function () {
        $("#import-patients-modal").unblock();
      },
    });
  });

  $("#import_medical_histories_button").click(function (event) {
    event.preventDefault();
    $.colorbox({
      href: "#import-medical-histories-modal",
      inline: true,
      width: $popup_width,
    });
  });
  $("#import-medical-histories-modal").on("submit", function (e) {
    e.preventDefault();

    var formData = new FormData();
    formData.append("action", "import_medical_histories");
    formData.append("nonce", wcfm_params.wcfm_ajax_nonce);
    formData.append("import_file", $("#import_historial_file")[0].files[0]);

    $(this).block({
      message: "Procesando...",
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    $.ajax({
      url: wcfm_params.ajax_url,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          alert(response.data);
          $wcfm_datatable.ajax.reload();
          $.colorbox.close();
          $("#import_historial_file").val("");
        } else {
          alert("Error: " + response.data);
        }
      },
      error: function (error) {
        alert("Error al procesar la solicitud");
      },
      complete: function () {
        $("#import-medical-histories-modal").unblock();
      },
    });
  });
});
