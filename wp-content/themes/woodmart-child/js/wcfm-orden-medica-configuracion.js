jQuery(document).ready(function ($) {
  // Collapsible
  $(".page_collapsible:not(.page_collapsible_dummy)").collapsible({
    defaultOpen: "wcfm_collapsible_head",
    speed: "slow",
    loadOpen: function (elem) {
      //replace the standard open state with custom function
      elem.next().show();
    },
    loadClose: function (elem, opts) {
      //replace the close state with custom function
      elem.next().hide();
    },
    animateOpen: function (elem, opts) {
      $(".collapse-open")
        .addClass("collapse-close")
        .removeClass("collapse-open");
      elem.addClass("collapse-open");
      $(".collapse-close")
        .find("span")
        .removeClass("fa-arrow-alt-circle-right block-indicator");
      elem.find("span").addClass("fa-arrow-alt-circle-right block-indicator");
      $(".wcfm-tabWrap")
        .find(".wcfm-container")
        .stop(true, true)
        .slideUp(opts.speed);
      elem.next().stop(true, true).slideDown(opts.speed);
    },
    animateClose: function (elem, opts) {
      elem.find("span").removeClass("fa-arrow-circle-up block-indicator");
      elem.next().stop(true, true).slideUp(opts.speed);
    },
  });
  $(".page_collapsible").each(function () {
    $(this).html(
      '<div class="page_collapsible_content_holder">' +
        $(this).html() +
        "</div>"
    );
    $(this)
      .find(".page_collapsible_content_holder")
      .after($(this).find("span"));
  });
  $(".page_collapsible").find("span").addClass("wcfmfa");
  $(".collapse-open").addClass("collapse-close").removeClass("collapse-open");
  setTimeout(function () {
    if (window.location.hash) {
      $(".wcfm-tabWrap").find(window.location.hash).click();
    } else {
      $(".wcfm-tabWrap").find(".page_collapsible:first").click();
    }
  }, 500);
  // Tabheight
  $(".page_collapsible").each(function () {
    if (!$(this).hasClass("wcfm_head_hide")) {
      collapsHeight += $(this).height() + 50;
    }
  });
  // Validate Form Data
  function wcfm_medical_orders_manage_form_validate() {
    $(".wcfm-message").html("").removeClass("wcfm-error").slideUp();

    return true;
  }

  // Submit Order
  $("#wcfm_medical_orders_config_submit_button").click(function (event) {
    event.preventDefault();
    // Validations
    $is_valid = wcfm_medical_orders_manage_form_validate();

    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger(
        "wcfm_form_validate",
        $("#wcfm_medical_order_config_form")
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
      controller: "wcfm-medical-orders-config-manage",
      wcfm_medical_order_config_form: $(
        "#wcfm_medical_order_config_form"
      ).serialize(),
      status: "submit",
      wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
    };
    $.post(wcfm_params.ajax_url, data, function (response) {
      if (response) {
        $response_json = $.parseJSON(response);
        $(".wcfm-message")
          .html("")
          .removeClass("wcfm-success")
          .removeClass("wcfm-error")
          .slideUp();
        wcfm_notification_sound.play();
        if ($response_json.redirect || $response_json.status) {
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-completed"></span>' +
                $response_json.message
            )
            .addClass("wcfm-success")
            .slideDown("slow", function () {
              if ($response_json.redirect)
                window.location = $response_json.redirect;
            });
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-cancelled"></span>' +
                $response_json.message
            )
            .addClass("wcfm-error")
            .slideDown();
        }
        wcfmMessageHide();
        $("#wcfm-content").unblock();
      }
    });
  });

  // handle dynamic select field_type_options change
  $("#wcfm_medical_order_config_form").on(
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
