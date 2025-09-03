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
    ],
    columnDefs: [
      { targets: 0, orderable: true },
      { targets: 1, orderable: true },
      { targets: 2, orderable: true },
      { targets: 3, orderable: true },
      { targets: 4, orderable: false },
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
          (d.controller = "wcfm-ordenes-medicas"),
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
    const excel_url = `/ordenes-medicas-excel/${data.wcfm_ajax_nonce}/?${params}`;
    window.open(excel_url, "_blank");
  });
});
