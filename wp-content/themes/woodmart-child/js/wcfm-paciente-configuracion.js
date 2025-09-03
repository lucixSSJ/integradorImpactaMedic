jQuery(document).ready(function ($) {
  // Validate Form Data
  function wcfm_patients_manage_form_validate() {
    $(".wcfm-message").html("").removeClass("wcfm-error").slideUp();

    return true;
  }

  // Submit Order
  $("#wcfm_patients_config_submit_button").click(function (event) {
    event.preventDefault();
    // Validations
    $is_valid = wcfm_patients_manage_form_validate();

    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger(
        "wcfm_form_validate",
        $("#wcfm_patient_config_form")
      );
      $is_valid = $wcfm_is_valid_form;
    }
    if (!$is_valid) {
      return;
    }

    $("#wcfm-content").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    var data = {
      action: "wcfm_ajax_controller",
      controller: "wcfm-patients-config-manage",
      wcfm_patient_config_form: $("#wcfm_patient_config_form").serialize(),
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
            '<span class="wcicon-status-completed"></span>' +
              response.data.message
          )
          .addClass("wcfm-success")
          .slideDown("slow");
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
    }).always(function () {
      $("#wcfm-content").unblock();
    });
  });

  // handle dynamic select field_type_options change
  $("#wcfm_patient_config_form").on(
    "change",
    ".field_type_options select",
    function () {
      const fieldType = $(this).val();
      if (["select", "mselect"].includes(fieldType)) {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_select_options")
          .show();
      } else {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_select_options")
          .hide();
      }
      if (["image_comment"].includes(fieldType)) {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_image")
          .show();
      } else {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_image")
          .hide();
      }
      if (["checkbox"].includes(fieldType)) {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_checkbox_options")
          .show();
      } else {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_checkbox_options")
          .hide();
      }
      if (
        ["title_custom"].includes(fieldType) ||
        ["subtitle"].includes(fieldType)
      ) {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_title_alignment, .field_type_title_custom_color")
          .show();
      } else {
        $(this)
          .closest(".cheryr-ui-repeater-content-box")
          .find(".field_type_title_alignment, .field_type_title_custom_color")
          .hide();
      }
    }
  );
  // fire change event in .field_type_options select when page is loaded
  $(".field_type_options select").change();
});
