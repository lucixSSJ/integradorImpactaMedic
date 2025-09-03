jQuery(document).ready(function ($) {
  $(document.body).on("updated_wcfm-datatable", function () {
    $(".wcfm_item_delete").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        var rconfirm = confirm(
          "¿Seguro que quieres borrar esta receta?\nEsta acción no se puede deshacer?"
        );
        if (rconfirm) deleteWCFMItem($(this));
        return false;
      });
    });
    $(".wcfm_item_clone").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        var rconfirm = confirm("¿Seguro que quieres duplicar esta receta?");
        if (rconfirm) duplicateWCFMPrescription($(this));
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
      action: "delete_wcfm_prescription",
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

  function duplicateWCFMPrescription(item) {
    jQuery("#wcfm-content").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    var data = {
      action: "clone_wcfm_prescription",
      id: item.data("id"),
      wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
    };
    jQuery.ajax({
      type: "POST",
      url: wcfm_params.ajax_url,
      data: data,
      success: function (response) {
        $("#wcfm-content .wcfm-message")
          .html("")
          .removeClass("wcfm-success")
          .removeClass("wcfm-error")
          .slideUp();
        wcfm_notification_sound.play();
        if (response.success) {
          if (typeof $wcfm_datatable !== "undefined") {
            $wcfm_datatable.ajax.reload();
          } else {
            $("#wcfm-content .wcfm-message")
              .html(
                '<span class="wcicon-status-completed"></span>' +
                  response.data.message
              )
              .addClass("wcfm-success")
              .slideDown("slow", function () {
                if (response.data.redirect)
                  window.location = response.data.redirect;
              });
          }
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-cancelled"></span>' +
                response.data.message
            )
            .addClass("wcfm-error")
            .slideDown();
        }
        wcfmMessageHide();
        $("#wcfm-content").unblock();
      },
    });
  }
});
