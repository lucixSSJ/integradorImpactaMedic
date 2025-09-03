jQuery(document).ready(function ($) {
  $(document.body).on("updated_wcfm-datatable", function () {
    $(".wcfm_item_delete").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        var rconfirm = confirm(
          "¿Seguro que quieres borrar esta orden médica?\nEsta acción no se puede deshacer?"
        );
        if (rconfirm) deleteWCFMItem($(this));
        return false;
      });
    });
  });

  function deleteWCFMItem(item) {
    jQuery("#wcfm-datatable_wrapper").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    var data = {
      action: "delete_wcfm_medical_order",
      id: item.data("id"),
      wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
    };
    jQuery.ajax({
      type: "POST",
      url: wcfm_params.ajax_url,
      data: data,
      success: function (response) {
        if ($wcfm_datatable) $wcfm_datatable.ajax.reload();
        jQuery("#wcfm-datatable_wrapper").unblock();
      },
    });
  }

  // handle control repeater height change
  $("#wcfm-content").on(
    "click",
    ".cx-ui-repeater-add, .cx-ui-repeater-copy, .cx-ui-repeater-toggle, .cx-ui-repeater-remove",
    function () {
      const wcfmContent = $(this).closest(".wcfm-content");
      setTimeout(function () {
        resetCollapsHeight(wcfmContent);
      }, 0);
    }
  );
});
