jQuery(document).ready(function ($) {
  $(document.body).trigger("updated_wcfm-datatable");
  $("#document_number").focus().select();

  // Calculate IMC by weight and height
  $("#wcfm_patient_form").on("input", function (e) {

    const $input = $(e.target);
    const isWeightInput = $input.is(
      '[id*="weight-"],[id*="exa_fisi_weight"], [id*="_peso-kg"], .weight-wrap input'
    );
    const isHeightInput = $input.is(
      '[id*="height-"],[id*="exa_fisi_height"], [id*="_talla-m"], .height-wrap input'
    );
    if (!isWeightInput && !isHeightInput) return;
    const inputId = $input.attr("id") || "";
    const indexMatch = inputId.match(/-(\d+)$/);

    if (!indexMatch) {
      const $formGroup = $input.closest(".wcfm-container, .form-group");
      const $weightInput = $formGroup.find(
        '[id*="_peso-kg"], [id*="exa_fisi_weight"], .weight-wrap input'
      );
      const $heightInput = $formGroup.find(
        '[id*="_talla-m"], [id*="exa_fisi_height"], .height-wrap input'
      );
      const $imcInput = $formGroup.find('[id*="_imc"], .imc-wrap input, [id*="exa_fisi_imc"]');
      const imc = calculateIMC($weightInput.val(), $heightInput.val());      
      if (imc) {
        $imcInput.val(imc);
      } else {
        $imcInput.val("");
      }
      return;
    }

    const index = indexMatch[1];

    // Buscar los campos específicos de esta consulta usando el mismo índice
    const $weightInput = $(`#weight-${index}, [id*="_peso-kg-${index}"]`);
    const $heightInput = $(`#height-${index}, [id*="_talla-m-${index}"]`);
    const $imcInput = $(`#imc-${index}, [id*="_imc-${index}"]`);
    const $ascInput = $(`#body_surface_area-${index}, [id*="_asc-${index}"]`);

    // Si no encuentra los campos con el patrón esperado, buscar en el contenedor padre
    if (
      $weightInput.length === 0 ||
      $heightInput.length === 0 ||
      $imcInput.length === 0
    ) {
      const $container = $input.closest(
        ".wcfm-container, .form-group, .consulta-group"
      );
      const $weightInputFallback = $container.find(
        '[id*="weight"], [id*="_peso-kg"]'
      );
      const $heightInputFallback = $container.find(
        '[id*="height"], [id*="_talla-m"]'
      );
      const $imcInputFallback = $container.find('[id*="imc"], [id*="_imc"]');
      const $ascInputFallback = $container.find(
        '[id*="body_surface_area"], [id*="_asc"]'
      );

      const imc = calculateIMC(
        $weightInputFallback.val(),
        $heightInputFallback.val()
      );
      const asc = calculateBodySurfaceArea(
        $weightInputFallback.val(),
        $heightInputFallback.val()
      );
      if (imc) {
        $imcInputFallback.val(imc);
      } else {
        $imcInputFallback.val("");
      }
      if (asc) {
        $ascInputFallback.val(asc);
      } else {
        $ascInputFallback.val("");
      }
      return;
    }

    // Calcular IMC
    const imc = calculateIMC($weightInput.val(), $heightInput.val());
    const asc = calculateBodySurfaceArea(
      $weightInput.val(),
      $heightInput.val()
    );

    // Actualizar campo IMC
    if (imc) {
      $imcInput.val(imc);
    } else {
      $imcInput.val("");
    }
    if (asc) {
      $ascInput.val(asc);
    } else {
      $ascInput.val("");
    }
  });

  $("#document_number").on("input", function () {
    $("#clinic_id").val($(this).val());
  });
  // Caculate IFGe by creatinina
  $("#wcfm_patient_form").on(
    "input",
    "#historial_medico .creatina-wrap input",
    function () {
      const gender = $("#gender").val();
      const fullAge = $("#calculated_age").val();
      const age = parseFloat(fullAge.split(/,| y /)[0].replace(/[^\d.]/g, ""));
      const itemIndex = $(this)
        .attr("name")
        .match(/\[item-(\d+)\]/)[1];
      const weight = parseFloat($(`#weight-${itemIndex}`).val());
      const creatina = $(this).val();
      const creatinaParent = $(this).closest(".creatina-wrap");
      if (creatina) {
        if (gender === "Hombre") {
          const indice_filtrado_glomerular = (
            ((140 - age) * weight) /
            (72 * creatina)
          ).toFixed(2);
          creatinaParent.next().find("input").val(indice_filtrado_glomerular);
        } else {
          const indice_filtrado_glomerular = (
            (((140 - age) * weight) / (72 * creatina)) *
            0.85
          ).toFixed(2);
          creatinaParent.next().find("input").val(indice_filtrado_glomerular);
        }
      }
    }
  );
  $("#document_number").on("input", function () {
    $("#clinic_id").val($(this).val());
  });
  //Verficacion de edad gestacional(Si se encuentra
  // embarazada se activa la funcion, sino pasa de largo)
  let has_pregnancy = false;
  const val = $("#antecedentes_se-encuentra-embarazada").val();
  has_pregnancy = val ? val.toLowerCase() === "si" : false;

  $("#wcfm_patient_form").on(
    "change",
    "#antecedentes_se-encuentra-embarazada",
    function () {
      const isPregnant = $(this).val().toLowerCase();
      has_pregnancy = isPregnant === "si";
      const fechaGuardada = $("#antecedentes_fecha-ultima-regla").val();
      if (has_pregnancy && fechaGuardada) {
        //Show
        $("#antecedentes_edad-gestacional").closest(".cx-control").show();
        $("#antecedentes_fecha-probable-de-parto")
          .closest(".cx-control")
          .show();
        const edad = getEdadGestacional(fechaGuardada);
        if (edad) {
          const texto = `${edad.semanas} semanas y ${edad.dias} días`;
          $("#antecedentes_edad-gestacional").val(texto);
          console.log("Edad gestacional actualizada automáticamente:", texto);
        }
      } else {
        //Hide
        $("#antecedentes_edad-gestacional").closest(".cx-control").hide();
        $("#antecedentes_fecha-probable-de-parto")
          .closest(".cx-control")
          .hide();
      }
    }
  );
  //Calcular edad gestacional
  $("#wcfm_patient_form").on("change", "input.hasDatepicker", function (e) {
    if (!has_pregnancy) return;
    const fechaTexto = $(this).val();
    const edad = getEdadGestacional(fechaTexto);
    if (edad) {
      const texto = `${edad.semanas} semanas y ${edad.dias} días`;
      $("#antecedentes_edad-gestacional").val(texto);
      console.log("Edad gestacional:", texto);
      $("#antecedentes_fecha-ultima-regla").val(fechaTexto);
    } else {
      $("#antecedentes_edad-gestacional").val("");
    }

    const fechaProbableParto = calculcarFPP(fechaTexto);
    $("#antecedentes_fecha-probable-de-parto").val(fechaProbableParto);
    $("#antecedentes_fecha-probable-de-parto")
      .nextAll('input[type="text"]')
      .first()
      .val(fechaProbableParto);
    console.log("Fecha probable de parto:", fechaProbableParto);
  });
  // show/hide gynecological fields by gender
  $("#gender").on("change", function () {
    if ($(this).val() === "Mujer") {
      $(".antecedentes_ginecologicos").show();
      $('.cx-control[data-control-name="menarquia"]').show();
      $('.cx-control[data-control-name="initiation_sexual_relations"]').show();
      $('.cx-control[data-control-name="menopause"]').show();

      $("#historial_medico .cx-ui-repeater-item-control.gender-woman").show();
      $("#historial_medico .obstetric_formula_head-wrap").show();
      $("#historial_medico .obstetric_formula_first_label-wrap").show();
      $("#historial_medico .obstetric_formula_second_label-wrap").show();
    } else {
      $(".antecedentes_ginecologicos").hide();
      $('.cx-control[data-control-name="menarquia"]').hide();
      $('.cx-control[data-control-name="initiation_sexual_relations"]').hide();
      $('.cx-control[data-control-name="menopause"]').hide();

      $("#historial_medico .cx-ui-repeater-item-control.gender-woman").hide();
      $("#historial_medico .obstetric_formula_head-wrap").hide();
      $("#historial_medico .obstetric_formula_first_label-wrap").hide();
      $("#historial_medico .obstetric_formula_second_label-wrap").hide();
    }
  });
  $("#gender").trigger("change");
  // Validate Form Data
  function wcfm_patients_manage_form_validate() {
    $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-error")
      .slideUp();

    var clinicId = $.trim($("#clinic_id").val());
    if (clinicId.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El Nº de Historia Clinica es requerido.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    var admissionDate = $.trim($("#admission_date").val());
    if (admissionDate.length == 0) {
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
  $("#wcfm_patient_form").submit(function (event) {
    event.preventDefault();

    // Validations
    $is_valid = wcfm_patients_manage_form_validate();
    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger("wcfm_form_validate", $(this));
      $is_valid = $wcfm_is_valid_form;
    }
    if ($is_valid) {
      copyMedicinaToTerapia();
      $("#wcfm-content").block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });
      var data = {
        action: "wcfm_ajax_controller",
        controller: "wcfm-patients-manage",
        wcfm_patient_form: $(this).serialize(),
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
               setTimeout(function() {
                        const patientId = getPatientIdFromPage();
                        console.log(4981);
                        if (patientId) {
                            updatePatientModal(patientId);
                            
                        }
                    }, 500);
              isFormDirty = false; // Reset dirty state after successful save
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
  function getMedicinaFisicaData() {
    const medicinaData = {};
    $('[name*="medicina-fisica_cie-10"][name*="cie10_value"]').each(
      function () {
        const name = $(this).attr("name");
        const value = $(this).val();
        const match = name.match(/\[item-(\d+)\]/);

        if (match && value) {
          const index = `item-${match[1]}`;
          if (!medicinaData[index]) medicinaData[index] = {};

          const selectedOption = $(this).find(`option[value="${value}"]`);
          const fullText = selectedOption.text();

          medicinaData[index]["cie10_value"] = value;
          medicinaData[index]["cie10_full_text"] = fullText;
        }
      }
    );
    $('[name*="medicina-fisica_cie-10"][name*="cie10_type"]').each(function () {
      const name = $(this).attr("name");
      const value = $(this).val();
      const match = name.match(/\[item-(\d+)\]/);
      if (match && value) {
        const index = `item-${match[1]}`;
        if (!medicinaData[index]) medicinaData[index] = {};
        medicinaData[index]["cie10_type"] = value;
        console.log(`CIE-10 Type ${index}:`, value);
      }
    });

    $('[name*="tratamiento-de-la-columna_terapia-fisica"]').each(function () {
      const name = $(this).attr("name");
      const match = name.match(/\[item-(\d+)\]/);
      if (match) {
        const index = `item-${match[1]}`;
        if (!medicinaData[index]) medicinaData[index] = {};

        if ($(this).is("select[multiple]")) {
          const selectedValues = $(this).val();
          medicinaData[index]["terapia-fisica"] = selectedValues || [];
        } else {
          const value = $(this).val();
          medicinaData[index]["terapia-fisica"] = value || "";
        }
      }
    });
    return medicinaData;
  }

  function copyMedicinaToTerapia() {
    var medicinaData = getMedicinaFisicaData();
    Object.keys(medicinaData).forEach(function (itemKey) {
      const medicinaItem = medicinaData[itemKey];

      if (medicinaItem.cie10_full_text && medicinaItem.cie10_type) {
        const combinedText = `${medicinaItem.cie10_full_text} (${medicinaItem.cie10_type})`;
        const diagnosticoFieldName = `control_paciente_2_values[${itemKey}][terapia-fisica_diagnosticos]`;
        $(`[name="${diagnosticoFieldName}"]`).val(combinedText);
        console.log(`Copiado CIE-10 con tipo ${itemKey}:`, combinedText);
      }
      if (medicinaItem["terapia-fisica"]) {
        const terapiaFieldName = `control_paciente_2_values[${itemKey}][terapia-fisica_terapias]`;
        if (Array.isArray(medicinaItem["terapia-fisica"])) {
          $(`[name="${terapiaFieldName}"]`).val(
            medicinaItem["terapia-fisica"].join(", ")
          );
        } else {
          $(`[name="${terapiaFieldName}"]`).val(medicinaItem["terapia-fisica"]);
        }
      }
    });
  }

  // Handle receta button click
  $("#wcfm_patient_form").on("click", "button.button-receta", function (event) {
    event.preventDefault();
    $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-error")
      .slideUp();
    // find ID input field by id attribute
    const currentId = $(this).attr("id");
    const historialMedicoId = $(`#ID${currentId}`).val();
    if (!historialMedicoId) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span>' +
            "El Historial Medico debe ser guardado antes de poder agregar una receta."
        )
        .addClass("wcfm-error")
        .slideDown();
      return;
    }
    const recetaId = $(`#receta_id${currentId}`).val();
    if (recetaId) {
      // redirect to edit receta
      window.location = `${wcfm_page_url}receta-administracion/${recetaId}?historial_medico_id=${historialMedicoId}`;
    } else {
      // redirect to create receta
      window.location = `${wcfm_page_url}receta-administracion?historial_medico_id=${historialMedicoId}`;
    }
  });
  // Handle orden medica button click
  $("#wcfm_patient_form").on(
    "click",
    "button.button-orden_medica",
    function (event) {
      event.preventDefault();
      $("#wcfm-content .wcfm-message")
        .html("")
        .removeClass("wcfm-error")
        .slideUp();
      // fin id
      const currentId = $(this).attr("id");
      const historialMedicoId = $(`#ID${currentId}`).val();
      if (!historialMedicoId) {
        $("#wcfm-content .wcfm-message")
          .html(
            '<span class = "wcicon-status-cancelled"></span>' +
              "El historial Medico debe ser guardado antes de agregar una orden medica."
          )
          .addClass("wcfm-error")
          .slideDown();
        return;
      }
      const ordenMedicaId = $(`#orden_medica_id${currentId}`).val();
      if (ordenMedicaId) {
        window.location = `${wcfm_page_url}orden-medica-administracion/${ordenMedicaId}?historial_medico_id=${historialMedicoId}`;
      } else {
        // redirect to create orden medica
        window.location = `${wcfm_page_url}orden-medica-administracion?historial_medico_id=${historialMedicoId}`;
      }
    }
  );
  // Handle download button click
  $("#wcfm_patient_form").on(
    "click",
    "button.button-download",
    function (event) {
      event.preventDefault();
      $("#wcfm-content .wcfm-message")
        .html("")
        .removeClass("wcfm-error")
        .slideUp();
      // find ID input field by id attribute
      const currentId = $(this).attr("id");
      const attachmentsIds = $(`#attachments${currentId}`).val();
      if (!attachmentsIds) {
        $("#wcfm-content .wcfm-message")
          .html(
            '<span class="wcicon-status-cancelled"></span>' +
              "No hay archivos adjuntos para descargar."
          )
          .addClass("wcfm-error")
          .slideDown();
        return;
      }
      window.open(`/descargar-medios-cliente/7?media_ids=${attachmentsIds}`);
    }
  );
  // handle pdf button click
  $("#wcfm_patient_form").on("click", "button.button-pdf", function (event) {
    event.preventDefault();
    $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-error")
      .slideUp();
    // find ID input field by id attribute
    const currentId = $(this).attr("id");
    const historialMedicoId = $(`#ID${currentId}`).val();
    if (!historialMedicoId) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span>' +
            "El Historial Medico debe ser guardado antes de poder generar un PDF."
        )
        .addClass("wcfm-error")
        .slideDown();
      return;
    }
    const pacienteId = $(`#_ID`).val();
    window.open(
      `/paciente-pdf/${pacienteId}/?report_block=historial-medico&historial_medico_id=${historialMedicoId}`,
      "_blank"
    );
  });
  // Download button to media popup with wp.media.view
  wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
    template: function (view) {
      // tmpl-attachment-details
      const html = wp.media.template("attachment-details")(view); // the template to extend
      const dom = document.createElement("div");
      dom.innerHTML = html;
      // create download link wrapper
      const details = dom.querySelector(".details");
      const downloadLink = document.createElement("a"); // create new element
      downloadLink.setAttribute("href", this.model.attributes.url); // add the image-id using the attributes
      downloadLink.setAttribute("download", "");
      downloadLink.classList.add("button"); // add a class to the element for styling
      downloadLink.innerHTML = "Descargar"; // element text
      details.appendChild(downloadLink); // add new element at the correct spot

      return dom.innerHTML;
    },
  });
  // Extend the Select Toolbar view
  var oldToolbar = wp.media.view.Toolbar.Select;
  wp.media.view.Toolbar.Select = oldToolbar.extend({
    initialize: function () {
      oldToolbar.prototype.initialize.apply(this, arguments);
      this.set("download", {
        style: "secondary",
        text: "Descargar Todos",
        priority: 60,
        click: this.downloadAll,
      });
    },
    downloadAll: function () {
      var selection = this.controller.state().get("selection");
      if (selection.length) {
        var mediaIds = selection.pluck("id");
        window.open(
          "/descargar-medios-cliente/7?media_ids=" + mediaIds.join(",")
        );
      }
    },
    refresh: function () {
      oldToolbar.prototype.refresh.apply(this, arguments);
      var selection = this.controller.state().get("selection");
      if (this.get("download")) {
        this.get("download").$el[0].disabled = !selection.length;
      }
    },
  });
  // set menu toggle state
  if (!$("#wcfm_menu").hasClass("wcfm_menu_toggle")) {
    $("#wcfm_menu").addClass("wcfm_menu_toggle");
  }
  // Exit into save
  let allowPatientModal = true;
  let isFormDirty = false;
  $("#wcfm_patient_form :input").on("change input", function(){
    isFormDirty = true;
  });
  $(document).on("click", "a", function (e){
      if(isFormDirty){
        e.preventDefault();
        let href= $(this).attr("href");
        $("#unsavedModal").fadeIn();
        allowPatientModal = false;
        $("#patient-modal").hide();
        $("#leavePage").off("click").on("click", function(){
          isFormDirty = false;
          window.location.href= href;
        });
        $("#stayHere").off("click").on("click", function(){
            $("#unsavedModal").fadeOut();
            allowPatientModal = true;
            $("#patient-modal").show();
        });
      }
  });
/*
  //Modal
  const el = document.getElementById("patient-data");
  const patientData = el ? JSON.parse(el.dataset.patient) : null;
  if (patientData && patientData.paciente) {
    const { name, last_name, birth_date } = patientData.paciente;
    const fullName = `${name || ""} ${last_name || ""}`.trim();

    let edadText = "";
    if (birth_date) {
      const birth = new Date(birth_date);
      const today = new Date();
      const diff = new Date(today - birth);
      const years = diff.getUTCFullYear() - 1970;
      const months = diff.getUTCMonth();
      const days = diff.getUTCDate() - 1;
      edadText = `${years} año(s) ${months} mes(es) ${days} día(s)`;
    }
    const resumenPaciente = `${fullName}${
      edadText ? " [" + edadText + "]" : ""
    }`;
    // Create modal element
    const modal = document.createElement("div");
    modal.id = "patient-modal";
    modal.style.cssText = `
        display: none;
        position: fixed;
        top: 10px;
        left: 52%;
        transform: translateX(-50%);
        width: 90%;
        max-height: 20vh;
        overflow-y: auto;
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        z-index: 9999;
        font-size: 14px;
        white-space: normal;
    `;

    let html = `<div style="line-height:1.4"><strong>Paciente:</strong> ${resumenPaciente}`;
    const antecedentes = patientData.antecedentes || {};
    html += `<hr style="margin: 6px 0;"><strong>Antecedentes:</strong> `;
    if (Object.keys(antecedentes).length > 0) {
      for (let [key, value] of Object.entries(antecedentes)) {
        html += `<span style="margin-right: 8px;"><b>${key}:</b> ${value} | </span>`;
      }
    } else {
      html += `<i></i>`;
    }
    html += `</div>`;
    modal.innerHTML = html;
    document.body.appendChild(modal);
    $(window).on("scroll", function () {
      if(allowPatientModal){
        if ($(this).scrollTop() > 10) {
          $("#patient-modal").fadeIn();
        } else {
          $("#patient-modal").fadeOut();
        }
     }
    });
  }*/
  // Validate that the dates are used by appointments module
  // for next_appointment_date, next_control_date, surgery_date
  let debounceTimers = {}; // Object to store timers by controlName
  let previousValues = {}; // Object to store previous values by controlName

  $(window).on("cx-control-change", function (event) {
    if (
      !event.controlName.includes("next_appointment_date") &&
      !event.controlName.includes("next_control_date") &&
      !event.controlName.includes("surgery_date")
    ) {
      return;
    }

    // Check if the value has actually changed
    const currentValue = event.controlStatus;
    const previousValue = previousValues[event.controlName];

    if (currentValue === previousValue) {
      return; // Value hasn't changed, don't validate
    }

    // Store the new value
    previousValues[event.controlName] = currentValue;

    // Clear the previous timer for this specific controlName
    clearTimeout(debounceTimers[event.controlName]);

    // Set a new timer for this specific controlName
    debounceTimers[event.controlName] = setTimeout(function () {
      if (event.controlStatus) {
        validateAppointmentAvailability(event.controlStatus, event.input);
      }
    }, 1000); // 1000ms delay
  });

  // Function to validate appointment availability
  function validateAppointmentAvailability(dateTimeString, inputField) {
    // Parse the date and time from the format 'yyyy-mm-dd HH:mm'
    const dateTime = new Date(dateTimeString);

    if (isNaN(dateTime.getTime())) {
      console.warn("Invalid date format:", dateTimeString);
      return;
    }

    // Convert to UTC timestamp (seconds) - important to use UTC to avoid timezone issues
    const timestamp = Math.floor(
      Date.UTC(
        dateTime.getFullYear(),
        dateTime.getMonth(),
        dateTime.getDate(),
        dateTime.getHours(),
        dateTime.getMinutes(),
        dateTime.getSeconds()
      ) / 1000
    );

    // Make REST API request using wp.apiFetch
    wp.apiFetch({
      path: "/v1/check-appointment-availability",
      method: "POST",
      data: {
        datetime: dateTimeString,
        timestamp: timestamp,
      },
    })
      .then((response) => {
        if (response.available) {
          // Date is available - don't show any message to user
        } else {
          // Show error message and clear the date field
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-cancelled"></span> ' +
                response.message
            )
            .addClass("wcfm-error")
            .removeClass("wcfm-success")
            .slideDown();

          // Clear the specific input field that caused the conflict
          // if (inputField) {
          //   $(inputField).val("").trigger("change");
          // }
        }
      })
      .catch((error) => {
        console.error("REST API error:", error);
        let errorMessage = "Error de conexión al verificar disponibilidad";

        if (error.message) {
          errorMessage = error.message;
        }

        $("#wcfm-content .wcfm-message")
          .html('<span class="wcicon-status-cancelled"></span> ' + errorMessage)
          .addClass("wcfm-error")
          .removeClass("wcfm-success")
          .slideDown();
      })
      .finally(() => {
        wcfmMessageHide();
      });
  }
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.data && settings.data.includes('controller=wcfm-patients-manage')) {
            var response = JSON.parse(xhr.responseText);
            if (response.success && response.data.patient_id) {
                // Habilitar y actualizar el botón con el ID del paciente
                $('#add_consultation_btn')
                    .prop('disabled', false)
                    .css('opacity', '1')
                    .attr('data-patient-id', response.data.patient_id);
            }
        }
    });

  $('#add_consultation_btn').on('click', function(e) {
    e.preventDefault();
    console.log("Si da click pero no ejecuta");
    var patientId = $(this).attr('data-patient-id');
    console.log("Patient ID:", patientId);
    /*if(!patientId || $(this).prop('disabled')) {
      return;
    }*/
    if(!patientId ) {
      console.log("Patient ID is missing. Cannot add consultation.");
      console.log("Patient ID:", patientId);
      return;
    }
    var consultationData = {
      patient_id: patientId,
      weight: $('input[name="exa_fisi_weight"').val() || '',
      height: $('input[name="exa_fisi_height"').val() || '',
      imc: $('input[name="exa_fisi_imc"').val() || '',
      heart_rate: $('input[name="heart_rate"').val() || '',
      respiratory_rate: $('input[name="respiratory_rate"').val() || '',
      blood_pressure: $('input[name="blood_pressure"').val() || '',
      temperature: $('input[name="body_temperature"').val() || '',
      oxygen_saturation: $('input[name="oxygen_saturation"').val() || '',
    }
    console.log("Datos de la consulta:", consultationData);
    $('#wcfm-content').block({
      message: null,
      overlayCSS: {
      background: "#fff",
      opacity: 0.6,
      },
    });
    // Lammada a la AJAX
    $.post(wcfm_params.ajax_url, {
      action: 'add_patient_consultation',
      consultation_data: consultationData,
      wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce
    }, function(response){
      $('#wcfm-content').unblock();
      $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-success")
      .removeClass("wcfm-error")
      .slideUp();

      wcfm_notification_sound.play();
      if(response.success){
        $('#wcfm-content .wcfm-message')
          .html('<span class="wcicon-status-completed"></span>' + response.data.message)
          .addClass('wcfm-success')
          .slideDown('slow')
          location.reload(); 
      } else{
        $('#wcfm-content .wcfm-message')
          .html('<span class="wcicon-status-cancelled"></span>' + response.data.message)
          .addClass('wcfm-error')
          .slideDown();
      }
    }).fail(function(){
      $('#wcfm-content').unblock();
      $('#wcfm-content .wcfm-message')
        .html('<span class="wcicon-status-cancelled"></span> Conexion Error al guardar la consulta. Inténtelo de nuevo.')
        .addClass('wcfm-error')
        .slideDown();
    });
  });
// SISTEMA MODAL DINÁMICO - JAVASCRIPT

// 1. FUNCIÓN PRINCIPAL - Actualizar modal con datos del paciente
function updatePatientModal(patientId) {
    if (!patientId) {
        console.error('Patient ID is required');
        return;
    }
    
    // Llamada AJAX
    $.ajax({
        url: wcfm_params.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_patient_modal_data',
            patient_id: patientId,
            nonce: wcfm_params.wcfm_ajax_nonce
        },
        success: function(response) {
            if (response.success) {
                renderPatientModal(response.data);
                showPatientModal();
            } else {
                console.error('Error:', response.data);
                hidePatientModal();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            hidePatientModal();
        }
    });
}

// 2. FUNCIÓN - Renderizar contenido del modal
function renderPatientModal(data) {
    const modalBody = document.getElementById('contenidoPaciente');
    if (!modalBody) return;
    
    let html = '';
    
    // Nombre y edad
    html += `<div class="patient-header" style="margin-bottom: 8px;">
        <strong style="font-size: 16px; color: #333;">${data.nombre_completo}</strong>
        ${data.edad ? `<span style="font-size: 14px; color: #666; margin-left: 10px;">[${data.edad}]</span>` : ''}
    </div>`;
    
    // Separator
    html += '<hr style="margin: 8px 0; border: 1px solid #e1e1e1;">';
    
    // Antecedentes
    if (data.antecedentes && data.antecedentes.length > 0) {
        html += '<div class="patient-antecedentes" style="margin-bottom: 8px;">';
        
        data.antecedentes.forEach((item, index) => {
            const isRam = item.is_ram;
            const isOptional = item.is_optional;
            const label = isOptional ? `(O)${item.label}` : item.label;
            const color = isRam ? '#d32f2f' : '#333'; // Rojo para RAM
            
            html += `<span style="color: ${color}; margin-right: 15px;">
                <strong>${label}:</strong> ${item.value}
            </span>`;
            
            // Add separator || except for last item
            if (index < data.antecedentes.length - 1) {
                html += '<span style="color: #999; margin-right: 15px;">||</span>';
            }
        });
        
        html += '</div>';
    }
    
    // CIE-10 Section
    const hasCie10Consulta = data.cie10_consulta && data.cie10_consulta.length > 0;
    const hasCie10Controles = data.cie10_controles && data.cie10_controles.length > 0;
    
    if (hasCie10Consulta || hasCie10Controles) {
        html += '<hr style="margin: 8px 0; border: 1px solid #e1e1e1;">';
        html += '<div class="patient-cie10" style="color: #1976d2;">'; // Azul para CIE-10
        
        // CIE-10 de última consulta
        if (hasCie10Consulta) {
            html += '<strong>CIE-10 Última Consulta:</strong> ';
            data.cie10_consulta.forEach((cie10, index) => {
                html += `${cie10.codigo}`;
                if (cie10.tipo) {
                    html += ` (${cie10.tipo})`;
                }
                if (index < data.cie10_consulta.length - 1) {
                    html += ', ';
                }
            });
            
            if (hasCie10Controles) {
                html += '<span style="color: #999; margin: 0 8px;">||</span>';
            }
        }
        
        // CIE-10 de controles
        if (hasCie10Controles) {
            html += '<strong>CIE-10 Último Control:</strong> ';
            data.cie10_controles.forEach((cie10, index) => {
                html += `${cie10.codigo}`;
                if (cie10.tipo) {
                    html += ` (${cie10.tipo})`;
                }
                if (index < data.cie10_controles.length - 1) {
                    html += ', ';
                }
            });
        }
        
        html += '</div>';
    }
    
    modalBody.innerHTML = html;
}

// 3. FUNCIÓN - Mostrar modal
function showPatientModal() {
    const modal = document.getElementById('modal-patient');
    if (modal) {
        modal.style.display = 'block';
        
        // Configurar estilos si no están definidos
        if (!modal.style.position) {
            modal.style.cssText = `
                display: block;
                position: fixed;
                top: 10px;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: 800px;
                max-height: 20vh;
                overflow-y: auto;
                background: white;
                border: 1px solid #ccc;
                border-radius: 8px;
                padding: 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                font-size: 14px;
                line-height: 1.4;
                white-space: normal;
            `;
        }
        
        // Fade in effect
        $(modal).fadeIn(300);
    }
}

// 4. FUNCIÓN - Ocultar modal
function hidePatientModal() {
    const modal = document.getElementById('modal-patient');
    if (modal) {
        $(modal).fadeOut(300);
    }
}

// 5. FUNCIÓN - Control de scroll para mostrar/ocultar modal
//let allowPatientModal = true;

function initPatientModalScroll() {
    $(window).on('scroll', function() {
        if (allowPatientModal) {
            const modal = document.getElementById('modal-patient');
            if (modal && modal.innerHTML.trim() !== '') {
                if ($(this).scrollTop() > 0) {
                    showPatientModal();
                } else {
                    hidePatientModal();
                }
            }
        }
    });
}

// 6. FUNCIÓN - Inicializar modal al cargar la página
function initPatientModal() {
    // Buscar patient ID en la página actual
    const patientId = getPatientIdFromPage();
    
    if (patientId) {
        updatePatientModal(patientId);
        initPatientModalScroll();
    }
}

// 7. FUNCIÓN AUXILIAR - Obtener Patient ID de la página
function getPatientIdFromPage() {
    // Opción 1: Desde URL params
    const urlParams = new URLSearchParams(window.location.search);
    let patientId = urlParams.get('wcfm-paciente-administracion');
    
    if (patientId) return patientId;
    
    // Opción 2: Desde elemento data (si existe)
    const el = document.getElementById('patient-data');
    if (el && el.dataset.patientId) {
        return el.dataset.patientId;
    }
    
    // Opción 3: Desde variable global (si está definida)
    if (typeof window.currentPatientId !== 'undefined') {
        return window.currentPatientId;
    }
    
    return null;
}


// 9. FUNCIONES PÚBLICAS PARA USO MANUAL
window.PatientModal = {
    update: updatePatientModal,
    show: showPatientModal,
    hide: hidePatientModal,
    toggle: function() {
        const modal = document.getElementById('modal-patient');
        if (modal && modal.style.display === 'block') {
            hidePatientModal();
        } else {
            showPatientModal();
        }
    }
};

// 10. FUNCIÓN DE DEBUG (remover en producción
/*
window.debugPatientModal = function(patientId) {
    console.log('Debugging patient modal for ID:', patientId);
    updatePatientModal(patientId || getPatientIdFromPage());
};*/



});

function calculateIMC(weight, height) {
  if (weight && height) {
    return (weight / (height * height)).toFixed(2);
  }
  return "";
}
function getEdadGestacional(fechaUltimaRegla, fechaActual = new Date()) {
  if (!fechaUltimaRegla) return null;

  const fur = new Date(fechaUltimaRegla);
  if (isNaN(fur)) return null;

  const diferenciaMs = fechaActual - fur;
  const diferenciaDias = Math.floor(diferenciaMs / (1000 * 60 * 60 * 24));

  const semanas = Math.floor(diferenciaDias / 7);
  const dias = diferenciaDias % 7;

  return { semanas, dias };
}
function calculcarFPP(fechaUltimaRegla) {
  if (!fechaUltimaRegla) return null;
  const fur = new Date(fechaUltimaRegla);
  if (isNaN(fur)) return null;
  fur.setDate(fur.getDate() + 280);
  return fur.toISOString().split("T")[0]; // Retorna la fecha en formato YYYY-MM-DD
}

function calculateBodySurfaceArea(weight, height) {
  if (isNaN(weight) || isNaN(height)) {
    return "";
  }
  //Calculate height in cm
  height = height * 100;
  if (weight && height) {
    return `${(
      0.007184 *
      Math.pow(weight, 0.425) *
      Math.pow(height, 0.725)
    ).toFixed(3)} m²`;
  }
  return "";
}
