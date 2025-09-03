jQuery(document).ready(function ($) {
  // Validate Form Data
  function wcfm_prescriptions_manage_form_validate() {
    $(".wcfm-message").html("").removeClass("wcfm-error").slideUp();

    return true;
  }

  // Submit Order
  $("#wcfm_prescriptions_config_submit_button").click(function (event) {
    event.preventDefault();
    // Validations
    $is_valid = wcfm_prescriptions_manage_form_validate();

    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger(
        "wcfm_form_validate",
        $("#wcfm_prescription_config_form")
      );
      $is_valid = $wcfm_is_valid_form;
    }

    if ($is_valid) {
      $("#wcfm-content").block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });
      var data = {
        action: "wcfm_ajax_controller",
        controller: "wcfm-prescriptions-config-manage",
        wcfm_prescription_config_form: $(
          "#wcfm_prescription_config_form"
        ).serialize(),
        status: "submit",
        wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
      };
      $.post(wcfm_params.ajax_url, data, function (response) {
        $(".wcfm-message")
          .html("")
          .removeClass("wcfm-success")
          .removeClass("wcfm-error")
          .slideUp();
        wcfm_notification_sound.play();
        if (response.success) {
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-completed"></span>' + response.data.message
            )
            .addClass("wcfm-success")
            .slideDown("slow", function () {
              if (response.data.redirect) window.location = response.data.redirect;
            });
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-cancelled"></span>' + response.data.message
            )
            .addClass("wcfm-error")
            .slideDown();
        }
        wcfmMessageHide();
        $("#wcfm-content").unblock();
      });
    }
  });
});
