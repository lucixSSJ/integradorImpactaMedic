jQuery(document).ready(function ($) {
  $(document.body).trigger("updated_wcfm-datatable");
  $("#document_number").focus().select();
  $("#paciente_id")
    .select2()
    .on("change", function () {
      const currentObj = $(this);
      if (currentObj.val() && currentObj.val() != 0) {
        const option = currentObj.find("option:selected");
        let fullName = option.data("name");
        const lastName = option.data("last_name");
        if (lastName) {
          fullName += " " + lastName;
        }
        $("#paciente_name").val(fullName);
        $("#document_number").val(option.data("clinic_id"));
        $("#paciente_email").val(option.data("email"));
        $("#birth_date").val(option.data("birth_date")).trigger("change");
      }
    });
  // trigger #paciente_id change event for select with value
  const recordId = $("#_ID").val();
  if (!recordId || recordId === "0") {
    $("#paciente_id").trigger("change");
  }
  $("#receta_etiquetas").select2();

  // Validate Form Data
  function wcfm_prescriptions_manage_form_validate() {
    $("#wcfm-content .wcfm-message")
      .html("")
      .removeClass("wcfm-error")
      .slideUp();

    var prescriptionDate = $.trim(
      $("#wcfm_prescription_form").find("#prescription_date").val()
    );
    if (prescriptionDate.length == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> La Fecha de Emisión es requerida.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    var pacienteName = $.trim(
      $("#wcfm_prescription_form").find("#paciente_name").val()
    );
    if (pacienteName.length == 0 || pacienteName == 0) {
      $("#wcfm-content .wcfm-message")
        .html(
          '<span class="wcicon-status-cancelled"></span> El Nombre Paciente es requerido.'
        )
        .addClass("wcfm-error")
        .slideDown();
      audio.play();
      return false;
    }

    return true;
  }

  // Submit Order
  $("#wcfm_prescriptions_manage_submit_button").click(function (event) {
    event.preventDefault();

    // Validations
    $is_valid = wcfm_prescriptions_manage_form_validate();
    if ($is_valid) {
      $wcfm_is_valid_form = true;
      $(document.body).trigger(
        "wcfm_form_validate",
        $("#wcfm_prescription_form")
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
      const params = new URLSearchParams(window.location.search);
      const historialID = params.get("historial_medico_id");
      const prescription_body = tinymce.get("prescription_body").getContent();
      var data = {
        action: "wcfm_ajax_controller",
        controller: "wcfm-prescriptions-manage",
        wcfm_prescription_form: $("#wcfm_prescription_form").serialize(),
        prescription_body: tinymce.get("prescription_body").getContent(),
        indications: tinymce.get("indications").getContent(),
        status: "submit",
        wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
      };
      if (historialID && prescription_body.trim() !== "") {
        data.historial_medico_id = historialID;
        data.treatment_content = prescription_body;
      }
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

  // Initialize combinations storage
  if (!window.medicamentoCombinations) {
    window.medicamentoCombinations = {};
  }

  // Function to save combination to database
  function saveCombination() {
    const combination = {
      principio_activo: $("#principio_activo").val(),
      medicamento: $("#medicamento").val(),
      presentacion: $("#presentacion").val(),
      concentracion: $("#concentracion").val(),
      via_administracion: $("#via_administracion").val(),
      dosis_descripcion: $("#dosis_descripcion").val(),
      dosis_cantidad: $("#dosis_cantidad").val(),
      cada: $("#cada").val(),
      cada_unidad: $("#cada_unidad").val(),
      duracion: $("#duracion").val(),
      duracion_unidad: $("#duracion_unidad").val(),
      especificaciones: $("#especificaciones").val(),
      indicaciones_complementarias: $("#indicaciones_complementarias").val(),
    };

    // Save combination for both principio_activo and medicamento if both exist
    const keysToSave = [];

    if (combination.principio_activo) {
      keysToSave.push(combination.principio_activo);
    }

    if (combination.medicamento) {
      keysToSave.push(combination.medicamento);
    }

    // Save combination for each key
    keysToSave.forEach((key) => {
      wp.apiFetch({
        path: "/v1/medicamento-combinations",
        method: "POST",
        data: {
          key: key,
          combination: combination,
        },
      })
        .then((response) => {
          loadCombinationsFromDatabase(key);
        })
        .catch((error) => {
          console.error("Error guardando combinación:", error);
        });
    });
  }

  // Function to load combinations from database
  function loadCombinationsFromDatabase(key) {
    return wp
      .apiFetch({
        path: `/v1/medicamento-combinations/${encodeURIComponent(key)}`,
        method: "GET",
      })
      .then((combinations) => {
        window.medicamentoCombinations[key] = combinations;
        return combinations;
      })
      .catch((error) => {
        console.error("Error cargando combinaciones:", error);
        return [];
      });
  }

  // Function to load most used combination
  function loadMostUsedCombination(key) {
    // Check if we have cached data
    if (
      window.medicamentoCombinations[key] &&
      window.medicamentoCombinations[key].length > 0
    ) {
      const mostUsed = window.medicamentoCombinations[key][0].combination;
      fillFieldsWithCombination(mostUsed);
    } else {
      // Load from database if not cached
      loadCombinationsFromDatabase(key).then((combinations) => {
        if (combinations && combinations.length > 0) {
          const mostUsed = combinations[0].combination;
          fillFieldsWithCombination(mostUsed);
        }
      });
    }
  }

  // Function to fill fields with combination data
  function fillFieldsWithCombination(mostUsed) {
    // Only fill fields that are currently empty to avoid overwriting user input
    if (!$("#medicamento").val() && mostUsed.medicamento)
      $("#medicamento").val(mostUsed.medicamento);
    if (!$("#principio_activo").val() && mostUsed.principio_activo)
      $("#principio_activo").val(mostUsed.principio_activo);
    if (!$("#presentacion").val() && mostUsed.presentacion)
      $("#presentacion").val(mostUsed.presentacion);
    if (!$("#concentracion").val() && mostUsed.concentracion)
      $("#concentracion").val(mostUsed.concentracion);
    if (!$("#via_administracion").val() && mostUsed.via_administracion)
      $("#via_administracion").val(mostUsed.via_administracion);
    if (!$("#dosis_descripcion").val() && mostUsed.dosis_descripcion)
      $("#dosis_descripcion").val(mostUsed.dosis_descripcion);
    if (!$("#dosis_cantidad").val() && mostUsed.dosis_cantidad)
      $("#dosis_cantidad").val(mostUsed.dosis_cantidad);
    if (!$("#cada").val() && mostUsed.cada) $("#cada").val(mostUsed.cada);
    if (!$("#cada_unidad").val() && mostUsed.cada_unidad)
      $("#cada_unidad").val(mostUsed.cada_unidad);
    if (!$("#duracion").val() && mostUsed.duracion)
      $("#duracion").val(mostUsed.duracion);
    if (!$("#duracion_unidad").val() && mostUsed.duracion_unidad)
      $("#duracion_unidad").val(mostUsed.duracion_unidad);
    if (!$("#especificaciones").val() && mostUsed.especificaciones)
      $("#especificaciones").val(mostUsed.especificaciones);
    if (
      !$("#indicaciones_complementarias").val() &&
      mostUsed.indicaciones_complementarias
    )
      $("#indicaciones_complementarias").val(
        mostUsed.indicaciones_complementarias
      );
  }
  let lastPrincipioActivo = "";
  // Autocomplete fields
  $("#principio_activo")
    .autocomplete({
      source: function (request, response) {
        var results = $.ui.autocomplete.filter(
          wcfm_impacta_data.principios_activos,
          request.term
        );
        response(results.slice(0, 15));
      },
      minLength: 0,
      select: function (event, ui) {
        if (lastPrincipioActivo !== ui.item.value) {
          lastPrincipioActivo = ui.item.value;
          loadMostUsedCombination(ui.item.value);
        }
      },
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    })
    .on("change", function () {
      const value = $(this).val();
      if (value && value !== lastPrincipioActivo) {
        lastPrincipioActivo = value;
        loadMostUsedCombination(value);
      }
    })
    .on("keypress", function (e) {
      if (e.which === 13) {
        // Enter key
        e.preventDefault();
        const value = $(this).val();
        if (value && value !== lastPrincipioActivo) {
          lastPrincipioActivo = value;
          loadMostUsedCombination(value);
        }
      }
    });
  let lastMedicamento = "";
  $("#medicamento")
    .autocomplete({
      source: function (request, response) {
        const term = request.term.toLowerCase();
        const principioActivo = $("#principio_activo").val().toLowerCase();
        const results = [];
        wcfm_impacta_data.medicamentos.forEach((medicamento) => {
          if (principioActivo) {
            if (
              medicamento.principio_activo &&
              medicamento.principio_activo.toLowerCase() == principioActivo
            ) {
              if (medicamento.name.toLowerCase().includes(term)) {
                results.push(medicamento.name);
              }
            }
          } else {
            if (medicamento.name.toLowerCase().includes(term)) {
              results.push(medicamento.name);
            }
          }
        });
        response(results.slice(0, 15));
      },
      minLength: 0,
      select: function (event, ui) {
        if (lastMedicamento !== ui.item.value) {
          lastMedicamento = ui.item.value;
          loadMostUsedCombination(ui.item.value);
        }
      },
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    })
    .on("change", function () {
      const value = $(this).val();
      if (value && value !== lastMedicamento) {
        lastMedicamento = value;
        loadMostUsedCombination(value);
      }
    })
    .on("keypress", function (e) {
      if (e.which === 13) {
        // Enter key
        e.preventDefault();
        const value = $(this).val();
        if (value && value !== lastMedicamento) {
          lastMedicamento = value;
          loadMostUsedCombination(value);
        }
      }
    });
  const presentaciones = [
    "Tableta",
    "Tableta efervescente",
    "Comprimido",
    "Capsula",
    "Sobre",
    "Ampolla",
    "Jarabe",
    "Suspensión",
    "Gotas",
    "Inhalador",
    "Crema top",
    "Crema vag",
    "Ung",
    "Inyectable",
    "Gotas",
    "Plv",
    "Liq oral",
    "Liq inh",
    "Gas",
    "Sol oft",
    "Sol rec",
    "Sol nbz",
    "Sol top",
    "Sol dia",
    "Spray nasal",
    "Spray oral",
    "Gotas oftálmicas",
    "Gotas oticas",
    "Tubo",
    "Frasco",
    "Supositorio",
  ];
  const mapaPresentacionDosis = {
    Jarabe: ["Cucharadita", "Cucharada", "ml", "ml por kilo"],
    Gotas: ["Gotas", "Gotas por kilo"],
    Suspensión: ["Cucharadita", "Cucharada", "ml", "ml por kilo"],
    Plv: ["Cucharadita", "Cucharada", "ml", "ml por kilo"],
    default: [
      "Tableta",
      "Cápsula",
      "Cucharadita",
      "Cucharada",
      "ml",
      "Gotas",
      "Gotas por kilo",
      "ml por kilo",
      "Sobre",
      "PUFF",
    ],
  };
  const autocompletarDosis = {
    Tableta: "Tableta",
    Comprimido: "Comprimido",
    "Tableta efervescente": "Tableta efervescente",
    "Sol oft": "Gotas",
    "Sol rec": "Gotas",
    "Sol nbz": "Gotas",
    "Sol top": "Gotas",
    "Sol dia": "Gotas",
    "Gotas oftálmicas": "Gotas",
    "Gotas oticas": "Gotas",
    Cápsula: "Cápsula",
    Inyectable: "Inyectable",
    "Crema top": "Aplicar",
    "Crema vag": "Aplicar",
    Ung: "Aplicar",
    "Spray nasal": "PUFF",
    "Spray oral": "PUFF",
    Inhalador: "PUFF",
    Ampolla: "Ampolla",
    Cápsula: "Cápsula",
    Sobre: "Sobre",
    Tubo: "Tubo",
    Frasco: "Frasco",
    Supositorio: "Supositorio",
  };
  $("#presentacion")
    .autocomplete({
      source: presentaciones,
      minLength: 0,
      select: function (event, ui) {
        const selecionado = ui.item.value;
        $("#dosis_descripcion").val(selecionado);
        const opcionesDosis =
          mapaPresentacionDosis[selecionado] ||
          mapaPresentacionDosis["default"];
        $("#dosis_descripcion").autocomplete("option", "source", opcionesDosis);
        if (autocompletarDosis[selecionado]) {
          $("#dosis_descripcion").val(autocompletarDosis[selecionado]);
        } else {
          $("#dosis_descripcion").val("");
        }
      },
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    });
  // Además, seguir capturando la entrada manual
  $("#presentacion").on("input", function () {
    $("#dosis_descripcion").val($(this).val());
  });
  const viasAdministracion = [
    "Oral",
    "Sublingual",
    "Intramuscular",
    "Subcutáneos",
    "Endovenosa",
    "Nasal",
    "Tópica",
    "Rectal",
    "Vaginal",
    "Parenteral",
    "Oftálmica",
    "Ótica",
    "Inhalatoria",
  ];
  $("#via_administracion")
    .autocomplete({
      source: viasAdministracion,
      minLength: 0,
      select: function (event, ui) {
        const selecionado = ui.item.value;
        if (selecionado == "Inhalatoria") {
          $("#dosis_descripcion").val("PUFF");
        }
      },
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    });
  const dosisDescripcionPlural = {
    Tableta: "Tabletas",
    Cápsula: "Cápsulas",
    Cucharadita: "Cucharaditas",
    Cucharada: "Cucharadas",
    ml: "ml",
    Gotas: "Gotas",
    "Gotas por kilo": "Gotas por kilo",
    "ML por kilo": "ML por kilo",
    Sobre: "Sobres",
    PUFF: "PUFFs",
    Tubo: "Tubos",
    Comprimido: "Comprimidos",
    Inyectable: "Inyectables",
    Frasco: "Frascos",
    Supositorio: "Supositorios",
  };

  /*const dosisDescripcion = [
    "Tableta",
    "Cápsula",
    "Cucharadita", 
    "Cucharada",
    "ML",
    "Gotas",
    "Gotas por kilo", 
    "ML por kilo",
    "Sobre",
    "PUFF",
  ]*/
  $("#dosis_descripcion")
    .autocomplete({
      source: mapaPresentacionDosis["default"],
      minLength: 0,
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    });
  //presentaicon de nuevo?
  $("#presentacion").on("input", function () {
    const valor = $(this).val();
    $("dosis_descripcion").val(valor);
    if (presentaciones.includes(valor)) {
      const opcionesDosis =
        mapaPresentacionDosis[valor] || mapaPresentacionDosis["default"];
      $("#dosis_descripcion").autocomplete("option", "source", opcionesDosis);
      if (autocompletarDosis[valor]) {
        $("#dosis_descripcion").val(autocompletarDosis[valor]);
      }
    }
  });
  //function
  function getDosisDescripcion(cantidad, descripcion) {
    if (cantidad > 1 && dosisDescripcionPlural[descripcion]) {
      return dosisDescripcionPlural[descripcion];
    }
    return descripcion;
  }
  const dosisCantidad = [
    "¼",
    "½",
    "0.25",
    "0.5",
    "0.75",
    "1",
    "2",
    "3",
    "4",
    "5",
    "10",
    "15",
    "20",
    "25",
    "50",
    "100",
  ];
  $("#dosis_cantidad")
    .autocomplete({
      source: dosisCantidad,
      minLength: 0,
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    });
  $("#concentracion")
    .autocomplete({
      source: function (request, response) {
        var results = $.ui.autocomplete.filter(
          wcfm_impacta_data.concentraciones,
          request.term
        );
        response(results.slice(0, 15));
      },
      minLength: 0,
    })
    .focus(function () {
      $(this).autocomplete("search", $(this).val());
    });

  $("#indicaciones_complementarias").change(function () {
    const indicacionesComplementarias = $(this).val();
    if (indicacionesComplementarias == "Otras") {
      $(".indicaciones_complementarias_otras").show();
    } else {
      $(".indicaciones_complementarias_otras").hide();
    }
  });
  $("#indicaciones_complementarias").trigger("change");

  let paragraphCount = 0;
  setTimeout(() => {
    const indicationsEditor = tinymce.get("indications");
    if (indicationsEditor) {
      const prescription_body = indicationsEditor.getContent();
      paragraphCount = prescription_body.split("</p>").length - 1;
    }
  }, 500);
  $("#add_to_prescription_body").click(function () {
    let newBodyContent = "";
    let bodyHasContent = false;
    let newIndicationsContent = "";
    let indicationsHasContent = false;
    const principioActivo = $("#principio_activo").val();
    const medicamento = $("#medicamento").val();
    const presentacion = $("#presentacion").val();
    const concentracion = $("#concentracion").val();
    const viaAdministracion = $("#via_administracion").val();
    const dosisDescripcion = $("#dosis_descripcion").val();
    const dosisCantidad = $("#dosis_cantidad").val();
    const especificaciones = $("#especificaciones").val();
    let unidad = "";
    const cadaFormula = $("#cada").val();
    let duracion_por = $("#duracion").val();
    const duracion_dias_meses = $("#duracion_unidad").val();
    const dosisDescripcionFormula = $("#dosis_descripcion").val();
    const pesoPaciente = $("#weight").val();
    if (principioActivo) {
      paragraphCount++;

      if (bodyHasContent) newBodyContent += " ";
      newBodyContent += `${paragraphCount}.- ${principioActivo}`;
      bodyHasContent = true;
      if (indicationsHasContent) newIndicationsContent += " ";
      newIndicationsContent += `${paragraphCount}.- ${principioActivo}`;
      indicationsHasContent = true;
    }
    if (medicamento) {
      if (bodyHasContent) newBodyContent += "&ensp; | ";
      newBodyContent += `<strong>${medicamento}</strong>`;
      bodyHasContent = true;
      if (indicationsHasContent) newIndicationsContent += "&ensp; | ";
      newIndicationsContent += `\t<strong>${medicamento}</strong>`;
      indicationsHasContent = true;
      if (
        !wcfm_impacta_data.medicamentos
          .map((medicamento) => medicamento.name.toLowerCase())
          .includes(medicamento.toLowerCase())
      ) {
        wcfm_impacta_data.medicamentos.push({
          name: medicamento,
          principio_activo: principioActivo,
        });
      }
      wp.apiFetch({
        path: "/v1/medicamentos",
        method: "POST",
        data: {
          name: medicamento,
          principio_activo: principioActivo,
        },
      });
    }
    if (concentracion) {
      if (bodyHasContent) newBodyContent += "&ensp; | ";
      newBodyContent += concentracion;
      bodyHasContent = true;
      if (
        !wcfm_impacta_data.concentraciones
          .map((str) => str.toLowerCase())
          .includes(concentracion.toLowerCase())
      ) {
        wcfm_impacta_data.concentraciones.push(concentracion);
      }
      wp.apiFetch({
        path: "/v1/concentraciones",
        method: "POST",
        data: {
          name: concentracion,
        },
      });
    }
    if (presentacion) {
      if (bodyHasContent) newBodyContent += "&ensp; | ";
      newBodyContent += presentacion;
      bodyHasContent = true;
    }

    //cantidad -> unidad
    let nuevaUnidad = " ";
    let nuevaUnidadParseada;
    if (
      dosisDescripcionFormula == "Tableta" ||
      dosisDescripcionFormula == "Tableta efervescente" ||
      dosisDescripcionFormula == "Cápsula" ||
      dosisDescripcionFormula == "Sobre" ||
      dosisDescripcionFormula == "Comprimido" ||
      dosisDescripcionFormula == "Supositorio" ||
      dosisDescripcionFormula == "Inyectable"
    ) {
      nuevaUnidad = calcularUnidades(
        dosisCantidad,
        cadaFormula,
        duracion_por,
        duracion_dias_meses
      );
    }

    if (dosisDescripcionFormula === "Ampolla") {
      nuevaUnidad = getUnidadesAmpollas(
        dosisCantidad,
        cadaFormula,
        duracion_por
      );
    }

    if (String(nuevaUnidad).trim() !== "") {
      nuevaUnidadParseada = Math.ceil(parseFloat(nuevaUnidad));
      unidad = nuevaUnidadParseada;
    }
    if (unidad) {
      if (bodyHasContent) newBodyContent += " |";
      newBodyContent += unidad + " " + (unidad === 1 ? "unidad" : "unidades");
      if (bodyHasContent) newBodyContent += "";
      bodyHasContent = true;
    }

    const bodyEditor = tinymce.get("prescription_body");
    const bodyContent = bodyEditor.getContent();
    bodyEditor.setContent(bodyContent + newBodyContent);

    const cada = $("#cada").val();
    const cadaUnidad = $("#cada_unidad").val();

    if (dosisCantidad && dosisDescripcion) {
      if (indicationsHasContent) newIndicationsContent += ", ";
      let descripcionFinal = getDosisDescripcion(
        dosisCantidad,
        dosisDescripcion
      );
      let nuevasgotas = dosisCantidad * pesoPaciente;
      if (dosisDescripcion == "Gotas por kilo") {
        newIndicationsContent += `${nuevasgotas} Gotas por toma`;
      } else if (dosisDescripcion == "ml por kilo") {
        newIndicationsContent += `${nuevasgotas} ml por toma`;
      } else {
        newIndicationsContent += `${dosisCantidad} ${descripcionFinal}`;
      }
      indicationsHasContent = true;
    }

    if (viaAdministracion) {
      if (indicationsHasContent) newIndicationsContent += ", ";
      newIndicationsContent += `<strong>vía ${viaAdministracion.toLowerCase()}</strong>`;
      indicationsHasContent = true;
    }

    const tomas = $("#tomas").val();
    if (tomas) {
      if (indicationsHasContent) newIndicationsContent += ", ";
      if (tomas == "Gotas por kilo de peso") {
        const gotasPorKilo = $("#gotas_por_kilo").val();
        newIndicationsContent += `${gotasPorKilo} `;
      }
      newIndicationsContent += tomas;
      indicationsHasContent = true;
    }

    if (cada) {
      if (indicationsHasContent) newIndicationsContent += ", ";
      newIndicationsContent += `cada ${cada} `;
      if (cada == 1) {
        if (cadaUnidad == "horas") {
          newIndicationsContent += "hora";
        } else if (cadaUnidad == "días") {
          newIndicationsContent += "día";
        }
      } else {
        newIndicationsContent += cadaUnidad;
      }
      indicationsHasContent = true;
    }

    const duracion = $("#duracion").val();
    const duracionUnidad = $("#duracion_unidad").val();

    if (duracion) {
      if (indicationsHasContent) newIndicationsContent += " ";
      newIndicationsContent += `por ${duracion} `;
      if (duracion == 1) {
        if (duracionUnidad == "días") {
          newIndicationsContent += "día";
        } else if (duracionUnidad == "meses") {
          newIndicationsContent += "mes";
        }
      } else {
        newIndicationsContent += duracionUnidad;
      }
      indicationsHasContent = true;
    }
    if (especificaciones) {
      if (indicationsHasContent) newIndicationsContent += " ";
      newIndicationsContent += `, ${especificaciones.toLowerCase()} `;
      indicationsHasContent = true;
    }

    const indicacionesComplementarias = $(
      "#indicaciones_complementarias"
    ).val();
    if (indicacionesComplementarias) {
      if (indicationsHasContent) newIndicationsContent += ", ";
      if (indicacionesComplementarias == "Otras") {
        const otrasIndicaciones = $(
          "#indicaciones_complementarias_otras"
        ).val();
        newIndicationsContent += otrasIndicaciones;
      } else {
        newIndicationsContent += `${indicacionesComplementarias}.`;
      }
      indicationsHasContent = true;
    }

    const indicationsEditor = tinymce.get("indications");
    const indicationsContent = indicationsEditor.getContent();
    indicationsEditor.setContent(indicationsContent + newIndicationsContent);

    // Save the combination for future use
    saveCombination();
    lastPrincipioActivo = "";
    lastMedicamento = "";
    // Clear fields
    $("#principio_activo").val("");
    $("#medicamento").val("");
    $("#presentacion").val("");
    $("#concentracion").val("");
    $("#via_administracion").val("");
    $("#dosis_descripcion").val("").trigger("change");
    $("#dosis_cantidad").val("");
    $("#especificaciones").val("");
    $("#cada").val("");
    $("#duracion").val("");
    $("#indicaciones_complementarias").val("").trigger("change");
    $("#indicaciones_complementarias_otras").val("");
  });

  // Function to show saved combinations (optional - for debugging/management)
  function showSavedCombinations(key) {
    if (window.medicamentoCombinations[key]) {
      console.log(
        `Combinaciones guardadas para "${key}":`,
        window.medicamentoCombinations[key]
      );
      return window.medicamentoCombinations[key];
    } else {
      // Load from database and show
      loadCombinationsFromDatabase(key).then((combinations) => {
        console.log(
          `Combinaciones cargadas de BD para "${key}":`,
          combinations
        );
        return combinations;
      });
    }
    return [];
  }

  // Add a way to clear combinations if needed (optional)
  window.clearMedicamentoCombinations = function (key = null) {
    if (key) {
      delete window.medicamentoCombinations[key];
      console.log(`Combinaciones locales limpiadas para "${key}"`);
    } else {
      window.medicamentoCombinations = {};
      console.log("Todas las combinaciones locales limpiadas");
    }
    // Note: This only clears local cache, not database records
    console.log(
      "Nota: Esto solo limpia el cache local, no los registros de la base de datos"
    );
  };

  // Make functions globally available for debugging
  window.showSavedCombinations = showSavedCombinations;

  // Function to show all cached combinations
  window.showAllCachedCombinations = function () {
    console.log(
      "Todas las combinaciones en cache:",
      window.medicamentoCombinations
    );
    Object.keys(window.medicamentoCombinations).forEach((key) => {
      console.log(`\n--- Combinaciones para "${key}" ---`);
      window.medicamentoCombinations[key].forEach((combo, index) => {
        console.log(`${index + 1}. (${combo.count} usos):`, combo.combination);
      });
    });
  };

  $("#prescription_title").change(function () {
    const title = $(this).val();
    if (title === "Receta Médica") {
      $("#wcfm_prescription_items_container").show();
    } else {
      $("#wcfm_prescription_items_container").hide();
    }
  });
  $("#prescription_title").trigger("change");
});
function getUnidadesAmpollas(posologia, cada_hora, numero_dias) {
  return posologia * (24 / cada_hora) * numero_dias;
}
function calcularUnidades(
  dosisCantidad,
  cadaFormula,
  duracion_por,
  duracion_dias_meses
) {
  const fracciones = { "¼": 0.25, "½": 0.5 };
  const dosiCantidadVerificada =
    fracciones[dosisCantidad] ?? parseFloat(dosisCantidad);
  const duracionEnDias =
    duracion_dias_meses === "meses" ? duracion_por * 30 : duracion_por;
  if (
    !isNaN(cadaFormula) &&
    cadaFormula > 0 &&
    !isNaN(duracionEnDias) &&
    duracionEnDias > 0
  ) {
    return (
      (24 / cadaFormula) *
      duracionEnDias *
      dosiCantidadVerificada
    ).toFixed(2);
  }
  return "()";
}
