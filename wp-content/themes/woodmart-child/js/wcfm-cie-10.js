jQuery(document).ready(function ($) {
  // Initialize select2 for cie-10 field when is added or duplicated
  $(document).on("cx-control-init", function (event, data) {
    if (data?.target) {
      data.target.find(".select-cie-10 select.cx-ui-select").each(function () {
        initializeSelectCie10(this);
      });
    }
  });
  // select2 for cie-10 field jet engine fields
  $(".select-cie-10 select.cx-ui-select").each(function () {
    initializeSelectCie10(this);
  });
  // select2 for cie-10 field wcfm fields
  $("#wcfm-content select.wcfm-multiple-cie-10-select").each(function () {
    initializeMultipleSelectCie10(this);
  });
  function initializeMultipleSelectCie10(selector) {
    $(selector).select2({
      ajax: {
        url: "/wp-json/v1/cie-10",
        dataType: "json",
        delay: 500,
        data: function (params) {
          return {
            q: params.term, // Search term
            page: params.page || 1,
            version: "2", // Update the version if this file is changed
          };
        },
        processResults: function (data, params) {
          params.page = params.page || 1;

          return {
            results: data.items,
            pagination: {
              more: params.page * 15 < data.total_count,
            },
          };
        },
        cache: true,
      },
      // minimumInputLength: 1,
      placeholder: "Seleccionar",
      allowClear: true,
      multiple: true,
    });
  }
  function initializeSelectCie10(selector) {
    $(selector).select2({
      ajax: {
        url: "/wp-json/v1/cie-10",
        dataType: "json",
        delay: 500,
        data: function (params) {
          return {
            q: params.term, // Search term
            page: params.page || 1,
            version: "2", // Update the version if this file is changed
          };
        },
        processResults: function (data, params) {
          params.page = params.page || 1;

          return {
            results: data.items,
            pagination: {
              more: params.page * 15 < data.total_count,
            },
          };
        },
        cache: true,
      },
      // minimumInputLength: 1,
      placeholder: "Seleccionar",
      allowClear: true,
    });
  }
});
