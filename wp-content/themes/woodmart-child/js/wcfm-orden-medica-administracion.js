jQuery(document).ready(function ($) {
  $(document.body).trigger("updated_wcfm-datatable");
  $("#document_number").focus().select();
  $("#paciente_id")
    .select2()
    .on("change", function () {
      const currentObj = $(this);
      console.log(currentObj.val());
      if (currentObj.val() && currentObj.val() != 0) {
        const option = currentObj.find("option:selected");

        $("#document_number").val(option.data("clinic_id"));
        $("#name").val(option.data("name"));
        $("#last_name").val(option.data("last_name"));
        $("#birth_date").val(option.data("birth_date")).trigger("change");
        $("#gender").val(option.data("gender")).trigger("change");
      }
    });
  // trigger #paciente_id change event for select with value
  const recordId = $("#_ID").val();
  if (!recordId || recordId === "0") {
    $("#paciente_id").trigger("change");
  }
  // Calculate IMC by weight and height
  function calculateIMC(weight, height) {
    if (weight && height) {
      return (weight / (height * height)).toFixed(2);
    }
    return "";
  }
  /*
  $("#wcfm_form").on(
    "input",
    "#historial_medico .weight-wrap input",
    function () {
      const weight = $(this).val();
      const weightParent = $(this).closest(".weight-wrap");
      const height = weightParent.next().find("input").val();
      if (weight && height) {
        const imc = (weight / (height * height)).toFixed(2);
        weightParent.next().next().find("input").val(imc);
      }
    }
  );
  $("#wcfm_form").on(
    "input",
    "#historial_medico .height-wrap input",
    function () {
      const height = $(this).val();
      const heightParent = $(this).closest(".height-wrap");
      const weight = heightParent.prev().find("input").val();
      if (weight && height) {
        const imc = (weight / (height * height)).toFixed(2);
        heightParent.next().find("input").val(imc);
      }
    }
  );*/
  // Manejador de eventos unificado
  $("#wcfm_form").on("input", function (e) {
    // Verificar si el input cambiado es de peso o altura
    const $input = $(e.target);
    const isWeightInput = $input.is('[id*="_peso-kg"], .weight-wrap input');
    const isHeightInput = $input.is('[id*="_talla-m"], .height-wrap input');

    if (!isWeightInput && !isHeightInput) return;

    // Encontrar los campos relacionados en el mismo grupo/formulario
    const $formGroup = $input.closest(".wcfm-container, .form-group");

    const $weightInput = $formGroup.find(
      '[id*="_peso-kg"], .weight-wrap input'
    );
    const $heightInput = $formGroup.find(
      '[id*="_talla-m"], .height-wrap input'
    );
    const $imcInput = $formGroup.find('[id*="_imc"], .imc-wrap input');

    // Calcular IMC
    const imc = calculateIMC($weightInput.val(), $heightInput.val());

    // Actualizar campo IMC
    if (imc) {
      $imcInput.val(imc);
    } else {
      $imcInput.val("");
    }
  });
  // Validate Form Data
  function wcfm_medical_orders_manage_form_validate() {
    $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-error")
      .slideUp();

    var documentNumber = $.trim($("#document_number").val());
    if (documentNumber.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El Nº Doc. de Identificación es requerido.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    var issueDate = $.trim($("#issue_date").val());
    if (issueDate.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> La Fecha de Ingreso es requerida.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    var name = $.trim($("#name").val());
    if (name.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El Nombre es requerido.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    var lastName = $.trim($("#last_name").val());
    if (lastName.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El Apellido es requerido.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    return true;
  }

  // Submit Order
  $("#wcfm_form").submit(function (event) {
    event.preventDefault();

    // Validations
    $is_valid = wcfm_medical_orders_manage_form_validate();
    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger("wcfm_form_validate", $(this));
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
        controller: "wcfm-medical-orders-manage",
        wcfm_form: $(this).serialize(),
        wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
      };
      $.post(wcfm_params.ajax_url, data, function (response) {
        if (response) {
          $("#wcfm-content .wcfm-message")
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
              .slideDown("slow", function () {
                if (response.data.redirect)
                  window.location = response.data.redirect;
              });
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
        }
      });
    }
  });

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
  // $(".wcfm-tabWrap").find(".wcfm-container").hide();
  setTimeout(function () {
    if (window.location.hash) {
      $(".wcfm-tabWrap").find(window.location.hash).click();
    } else {
      $(".wcfm-tabWrap").find(".page_collapsible:first").click();
    }
  }, 100);

  // Tabheight
  $(".page_collapsible").each(function () {
    if (!$(this).hasClass("wcfm_head_hide")) {
      collapsHeight += $(this).height() + 50;
    }
  });
});
