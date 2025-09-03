jQuery(document).ready(function ($) {
  const correoPorDefecto = "soporte@impactamedic.com";
  const correoPorDefecto365 = "soporte@impacta365";
  let countriesHtml = "";
  const countrySelected = localStorage.getItem("countrySelected") ?? "51";
  const serviceId = $("#service_id").val();
  const currentUserId = parseInt($("#agenda-wrapper").data("user-id"));
  for (const countryLabel of countryCodes) {
    if (!Array.isArray(countryLabel)) {
      const countryCode = countryLabel.replace("+", "");
      countriesHtml += `<option value="${countryCode}" ${
        countryCode == countrySelected ? "selected" : ""
      }>${countryLabel}</option>`;
    }
  }

  $("#wcfm-calendar").fullCalendar({
    header: {
      left: "prev,next today",
      center: "title",
      right: "month,agendaWeek,agendaDay",
      ignoreTimezone: false,
    },
    timeFormat: "HH:mm",
    locale: "es",
    events: function (start, end, timezone, callback) {
      wp.apiFetch({
        path:
          "/v1/events?date_start=" +
          start +
          "&date_end=" +
          end +
          "&service=" +
          serviceId,
        method: "GET",
      })
        .then(function (response) {
          callback(response);
        })
        .catch(function (error) {
          callback([]);
          console.error("Error fetching events", error);
        });
    },
    eventRender: function (event, element) {
      element.qtip({
        content: $(
          "<span>" +
            event.title +
            "<br>" +
            event.start.toLocaleString() +
            "<br>" +
            "Correo: " +
            event.email +
            "<br>" +
            "Telefono: " +
            event.phone +
            "</span>"
        ),
      });
    },
    eventClick: async function (event, jsEvent, view) {
      // Prevent default action
      jsEvent.preventDefault();
      // Block UI
      $("#wcfm-content").block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });
      // Open Colorbox with event details
      const eventType = event.event_type;
      const id = event.id;
      const email = event.email;
      const phone = event.phone;
      const observaciones = event.observations || "Sin observaciones.";
      let zoomUrlAdmin = "";
      let zoomUrl = "";
      let zoomPassword = "";
      const pacienteUrl = `${event.url}#wcfm_collapsible_head_historial_medico`;
      const isAppointment = eventType === "appointment";
      let changeStateButton = "";
      if (isAppointment) {
        changeStateButton = `
        <button id="btn-finalize" type="button" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success" data-id="${id}">
          CITA TERMINADA
          <span class="wd-btn-icon">
            <i class="fas fa-check"></i>
          </span>
        </button>`;
        const response = await wp.apiFetch({
          method: "GET",
          path: api.appointment_meta + id,
        });
        if (response.success) {
          for (const field of response.fields) {
            if (field.key === "zoom_start_url") {
              const urlMatch = field.value.match(/href="([^"]+)"/);
              if (urlMatch) {
                zoomUrlAdmin = urlMatch[1];
              }
            }
            if (field.key === "zoom_join_url") {
              const urlMatch = field.value.match(/href="([^"]+)"/);
              if (urlMatch) {
                zoomUrl = urlMatch[1];
              }
            }
            if (field.key === "zoom_password") {
              zoomPassword = field.value;
            }
          }
        }
      }
      let dateField;
      switch (eventType) {
        case "next_control_date":
          dateField = "next_control_date";
          break;
        case "surgery_date":
          dateField = "surgery_date";
          break;
        case "next_appointment_date":
          dateField = "next_appointment_date";
          break;
        default:
          dateField = "appointment";
          break;
      }
      let eventTitle = "";
      switch (eventType) {
        case "next_control_date":
          eventTitle = `${
            currentUserId === 609 ? "MEDICINA FÍSICA" : "PRÓXIMO CONTROL"
          } ${event.title}`;
          break;
        case "surgery_date":
          eventTitle = `${
            currentUserId === 609 ? "FISIOTERAPIA" : "PROCEDIMIENTO"
          } ${event.title}`;
          break;
        case "next_appointment_date":
          eventTitle = `${
            currentUserId === 609 ? "TRAUMATOLOGÍA" : "PROXIMA CITA"
          } ${event.title}`;
          break;
        default:
          eventTitle = `CITA ${event.title}`;
          break;
      }
      const extractPaciente = (title) => {
        const parentesisMatch = title.split("(")[0].trim();

        if (parentesisMatch.includes("-")) {
          return parentesisMatch.split("-")[0].trim();
        }

        if (parentesisMatch) {
          return parentesisMatch;
        }

        if (title.includes("-")) {
          return title.split("-")[0].trim();
        }
        return title || "";
      };

      const pacientes = extractPaciente(event.title);
      const dias = {
        Mon: "Lunes",
        Tue: "Martes",
        Wed: "Miercoles",
        Thu: "Jueves",
        Fri: "Viernes",
        Sat: "Sábado",
        Sun: "Domingo",
      };
      const meses = {
        Jan: "Enero",
        Feb: "Febrero",
        Mar: "Marzo",
        Apr: "Abril",
        May: "Mayo",
        Jun: "Junio",
        Jul: "Julio",
        Aug: "Agosto",
        Sep: "Septiembre",
        Oct: "Octubre",
        Nov: "Noviembre",
        Dec: "Diciembre",
      };
      const fechaOriginal = event.start.toLocaleString();
      const partes = fechaOriginal.split(" ");
      const diaSemana = dias[partes[0]];
      const dia = partes[2];
      const mes = meses[partes[1]];
      const year = partes[3];
      const hour = partes[4];
      const diaFormateado = `el día ${diaSemana} ${dia} de ${mes} del ${year}`;
      const horaFormateada = `a las ${hour.substring(0, 5)} hrs`;

      const first_msg = event.first_msg || "";
      const end_msg = event.end_msg || "";
      const signature = event.signature || "";
      const lugarAtencion = event.title.split("-")[1]
        ? event.title.split("-")[1].trim()
        : "";

      const saludo = first_msg || "Hola";
      const msgInicio = `${saludo} ${pacientes}, recuerda que ${diaFormateado} ${horaFormateada}, tiene cita médica.`;

      const msgLugarAtencion = lugarAtencion ? `\n\n*${lugarAtencion}*` : "";
      const msgFirma = signature ? `\n\n*${signature}*` : "";
      const msgFinal = end_msg ? `\n\n${end_msg}` : "";
      const msfirmaAtencion = msgFirma || msgLugarAtencion;

      const mensajeCompleto = `${msgInicio}${msgFinal}${msfirmaAtencion}`;
      const textFormattedAppoinments = encodeURIComponent(mensajeCompleto);

      $.colorbox({
        html: `<div id="custom-share-modal">
        <h2 class="text-center color-alt">
          <i class="fas fa-share-square"></i> ${eventTitle}
        </h2>
        <hr class="color-alt" style="max-width:100%;opacity:1;"/>
        <div class="wcfm-content elementor-form-fields-wrapper">
          <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
            <p style="margin: 0;"><b>Fecha:</b> ${event.start.toLocaleString()}</p>
          </div>
          <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
            <p style="margin: 0;"><b>Correo:</b> <a href="mailto:${email}" target="_blank">${email}</a></p>
          </div>
          <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
            <p style="margin: 0;"><b>Teléfono:</b> <a href="tel:${phone}" target="_blank">${phone}</a></p>
          </div>
          <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
            <p style="margin: 0;"><b>Observaciones:</b> ${observaciones}</p>
          </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:10px;">
          ${changeStateButton}
          <button id="btn-delete" type="button" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-danger" data-id="${id}">
            ELIMINAR CITA
            <span class="wd-btn-icon">
              <i class="fas fa-trash-alt"></i>
            </span>
          </button>
          <a style="display:${
            pacienteUrl ? "inline-flex" : "none"
          }" href="${pacienteUrl}" target="_blank" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-primary">
            VER HISTORIA CLÍNICA
            <span class="wd-btn-icon">
              <i class="fas fa-user"></i>
            </span>
          </a>
        </div>
        <div style="display:${eventType === "appointment" ? "block" : "none"}">
          <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:10px;">
            <a href="https://api.whatsapp.com/send?phone=${phone}&text=${textFormattedAppoinments}" target="_blank" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
              Enviar Recordatorio
              <span class="wd-btn-icon">
                <i class="fab fa-whatsapp"></i>
              </span>
            </a>
            <a href="sms:?&body=${textFormattedAppoinments}" target="_blank" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-link">
              Sms
              <span class="wd-btn-icon">
                <i class="fas fa-sms"></i>
              </span>
            </a>
          </div>
          <form id="form_share_email" class="elementor-form-fields-wrapper">
            <input type="hidden" name="id" value="${id}" />
            <div class="elementor-column elementor-col-80 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <input type="email" id="email" name="email" placeholder="Correo" class="elementor-field elementor-size-sm" value="${email}"/>
            </div>
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success">
                Enviar
                <span class="wd-btn-icon">
                  <i class="far fa-paper-plane"></i>
                </span>
              </button>
            </div>
          </form>
          <form id="form_share_whatsapp" class="elementor-form-fields-wrapper">
            <input type="hidden" name="id" value="${id}" />
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <select name="country_code" class="elementor-field elementor-size-sm" required="required">
                ${countriesHtml}
              </select>
            </div>
            <div class="elementor-column elementor-col-60 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <input type="tel" name="phone_number" placeholder="Número Celular" class="elementor-field elementor-size-sm" value="${phone}" required />
            </div>
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success">
                Enviar
                <span class="wd-btn-icon">
                  <i class="fab fa-whatsapp"></i>
                </span>
              </button>
            </div>
          </form>
          <form id="form_share_sms" class="elementor-form-fields-wrapper">
            <input type="hidden" name="id" value="${id}" />
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <select name="country_code" class="elementor-field elementor-size-sm" required="required">
                ${countriesHtml}
              </select>
            </div>
            <div class="elementor-column elementor-col-60 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <input type="tel" name="phone_number" placeholder="Número Celular" class="elementor-field elementor-size-sm" value="${phone}" required />
            </div>
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-link">
                Enviar
                <span class="wd-btn-icon">
                  <i class="fas fa-sms"></i>
                </span>
              </button>
            </div>
          </form>
        </div>
         <div style="display:${eventType !== "appointment" ? "block" : "none"}">
          <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:10px;">
            <a href="https://api.whatsapp.com/send?phone=${phone}&text=${textFormattedAppoinments}" target="_blank" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
              Enviar Recordatorio
              <span class="wd-btn-icon">
                <i class="fab fa-whatsapp"></i>
              </span>
            </a>
          </div>
          <form id="form_share_email_simple" class="elementor-form-fields-wrapper">
            <input type="hidden" name="id" value="${id}" />
            <div class="elementor-column elementor-col-80 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <input type="email" id="email" name="email" placeholder="Correo" class="elementor-field elementor-size-sm" value="${email}"/>
            </div>
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success">
                Enviar
                <span class="wd-btn-icon">
                  <i class="far fa-paper-plane"></i>
                </span>
              </button>
            </div>
          </form>
          <form id="form_share_whatsapp_simple" class="elementor-form-fields-wrapper">
            <input type="hidden" name="id" value="${id}" />
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <select name="country_code" class="elementor-field elementor-size-sm" required="required">
                ${countriesHtml}
              </select>
            </div>
            <div class="elementor-column elementor-col-60 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <input type="tel" name="phone_number" placeholder="Número Celular" class="elementor-field elementor-size-sm" value="${phone}" required />
            </div>
            <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
              <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success">
                Enviar
                <span class="wd-btn-icon">
                  <i class="fab fa-whatsapp"></i>
                </span>
              </button>
            </div>
          </form>
        </div>
        </div>`,
        width: $popup_width,
        onComplete: function () {
          // Unblock UI
          $("#wcfm-content").unblock();

          $("#email").focus();
          $("#form_share_email").on("submit", function (event) {
            event.preventDefault();
            const currentForm = $(this);
            $("#custom-share-modal").block({
              message: "Enviando Correo...",
              overlayCSS: {
                background: "#fff",
                opacity: 0.6,
              },
            });
            const formObj = {};
            for (const iterator of currentForm.serializeArray()) {
              formObj[iterator.name] = iterator.value;
            }
            if (!formObj.email || formObj.email.trim() === "") {
              formObj.email = correoPorDefecto;
            }
            $.ajax({
              type: "POST",
              url: wcfm_params.ajax_url,
              data: {
                action: "email_wcfm_appointment",
                wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
                ...formObj,
              },
              success: function (response) {
                alert(response.data);
              },
              complete: function () {
                $("#custom-share-modal").unblock();
              },
            });
          });
          $("#form_share_email_simple").on("submit", function (event) {
            event.preventDefault();
            const currentForm = $(this);
            $("#custom-share-modal").block({
              message: "Enviando Correo...",
              overlayCSS: {
                background: "#fff",
                opacity: 0.6,
              },
            });
            const formObj = {};
            for (const iterator of currentForm.serializeArray()) {
              formObj[iterator.name] = iterator.value;
            }
            // Agregar correo por defecto si está vacío
            if (!formObj.email || formObj.email.trim() === "") {
              formObj.email = correoPorDefecto;
            }
            $.ajax({
              type: "POST",
              url: wcfm_params.ajax_url,
              data: {
                action: "email_wcfm_appointment",
                wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
                ...formObj,
              },
              success: function (response) {
                alert(response.data);
              },
              complete: function () {
                $("#custom-share-modal").unblock();
              },
            });
          });
          $("#form_share_whatsapp, #form_share_whatsapp_simple").on(
            "submit",
            function (event) {
              event.preventDefault();
              const item = $(this);
              const countryCode = item
                .find('select[name="country_code"]')
                .val();
              const phoneNumber = item.find('input[name="phone_number"]').val();
              window.open(
                `https://api.whatsapp.com/send?phone=${countryCode}${phoneNumber}&text=${textFormattedAppoinments}`,
                "_blank"
              );
            }
          );
          $("#form_share_sms, #form_share_simple").on(
            "submit",
            function (event) {
              event.preventDefault();
              const item = $(this);
              const countryCode = item
                .find('select[name="country_code"]')
                .val();
              const phoneNumber = item.find('input[name="phone_number"]').val();
              window.open(
                `sms:${countryCode}${phoneNumber}?body=${textFormattedAppoinments}`,
                "_blank"
              );
            }
          );
          $('select[name="country_code"]').change(function () {
            localStorage.setItem("countrySelected", $(this).val());
          });
          $("#btn-finalize").on("click", function (event) {
            event.preventDefault();
            if (!isAppointment) {
              return;
            }
            const currentContainer = $("#custom-share-modal");
            currentContainer.block({
              message: null,
              overlayCSS: {
                background: "#fff",
                opacity: 0.6,
              },
            });
            wp.apiFetch({
              method: "POST",
              path: `${api.update_appointment}${id}`,
              data: {
                item: {
                  id: id,
                  status: "completed",
                },
              },
            })
              .then(function (response) {
                if (response.success) {
                  $.colorbox.close();
                  $("#wcfm-calendar").fullCalendar("refetchEvents");
                } else {
                  alert(response.data);
                }
              })
              .finally(function () {
                currentContainer.unblock();
                wcfmMessageHide();
              });
          });

          $("#btn-delete").on("click", function (event) {
            event.preventDefault();
            const isConfirm = confirm(
              "¿Estás seguro de eliminar esta cita? Esta acción no se puede deshacer."
            );
            if (!isConfirm) return;
            const currentContainer = $("#custom-share-modal");
            currentContainer.block({
              message: null,
              overlayCSS: {
                background: "#fff",
                opacity: 0.6,
              },
            });
            if (isAppointment) {
              wp.apiFetch({
                method: "POST",
                path: api.delete_appointment,
                data: {
                  items: [id],
                },
              })
                .then(function (response) {
                  if (response.success) {
                    $.colorbox.close();
                    $("#wcfm-calendar").fullCalendar("refetchEvents");
                  } else {
                    alert(response.data);
                  }
                })
                .finally(function () {
                  currentContainer.unblock();
                });
            } else {
              wp.apiFetch({
                method: "DELETE",
                path: `/v1/historial-medico-date/${id}`,
                data: {
                  date_field: dateField,
                },
              })
                .then(function (response) {
                  if (response.success) {
                    $.colorbox.close();
                    $("#wcfm-calendar").fullCalendar("refetchEvents");
                  } else {
                    alert(response.data);
                  }
                })
                .catch(function (error) {
                  alert(error.message);
                })
                .finally(function () {
                  currentContainer.unblock();
                });
            }
          });
        },
      });
    },
  });

  $("#paciente_id")
    .select2({
      dropdownParent: $("#appointment-form-modal"),
    })
    .on("change", function () {
      const currentObj = $(this);
      if (currentObj.val() && currentObj.val() != 0) {
        const option = currentObj.find("option:selected");
        let fullName = option.data("name");
        const lastName = option.data("last_name");
        if (lastName) {
          fullName += " " + lastName;
        }
        $("#user_name").val(fullName);
        $("#user_email").val(option.data("email"));
        $("#user_phone").val(option.data("phone"));
      }
    });
  $("#appointment_date").datepicker({
    minDate: 0,
  });
  $("#wcfm-content").on("click", ".add-edit-appointment", function () {
    if (serviceId == 0) {
      return alert("No tiene un tarjeta digital asignada");
    }
    const id = $(this).data("id");
    const isEdit = id ? true : false;
    $("#modal-title-prefix").text(isEdit ? "EDITAR" : "AÑADIR");

    // Reset form fields
    $("#appointment-form-modal")[0].reset();
    $("#paciente_id").val("0").trigger("change");
    $("#slot_timestamp")
      .empty()
      .append("<option value=''>Selecciona una hora</option>");

    $.colorbox({
      href: "#appointment-form-modal",
      inline: true,
      width: $popup_width,
      onComplete: function () {
        $("#user_name").focus();
      },
    });
  });
  $("#cancel-button").on("click", function () {
    $.colorbox.close();
  });
  // get date in format YYYY-MM-DD
  $("#appointment_date, #provider").on("change", function () {
    const slotSelect = $("#slot_timestamp");
    slotSelect.empty();
    slotSelect.append("<option value=''>Selecciona una hora</option>");
    const providerId = $("#provider").val();
    const appointmentDateVal = $("#appointment_date").val();
    if (!providerId || !appointmentDateVal) return;

    let dateNow = new Date();

    wp.apiFetch({
      method: "POST",
      path: api.date_slots,
      data: {
        service: serviceId,
        provider: providerId,
        date: new Date(appointmentDateVal).getTime() / 1000,
        timestamp: Math.floor(
          (dateNow.getTime() - dateNow.getTimezoneOffset() * 60 * 1000) / 1000
        ),
        admin: true,
      },
    }).then(function (response) {
      if (response.success) {
        const slots = response.data.slots;
        // iterate over slots and append to select - slots is an object with objects inside
        if (typeof slots === "string") {
          alert(slots);
          return;
        }

        for (const slot in slots) {
          // get hour from slot from slots[slot].from timestamp
          const dateFrom = new Date(slots[slot].from * 1000);
          const hourFrom = String(dateFrom.getUTCHours()).padStart(2, "0");
          const minuteFrom = String(dateFrom.getUTCMinutes()).padStart(2, "0");
          const stringSlotStart = `${hourFrom}:${minuteFrom}`;
          // get hour from slot from slots[slot].to timestamp
          const dateTo = new Date(slots[slot].to * 1000);
          const hourTo = String(dateTo.getUTCHours()).padStart(2, "0");
          const minuteTo = String(dateTo.getUTCMinutes()).padStart(2, "0");
          const stringSlotEnd = `${hourTo}:${minuteTo}`;
          slotSelect.append(
            `<option value="${slot}" data-slot-start="${stringSlotStart}" data-slot-end="${stringSlotEnd}" data-slot-end-timestamp="${slots[slot].to}">
                ${stringSlotStart} - ${stringSlotEnd}
              </option>`
          );
        }
      } else {
        alert(response.data);
      }
    });
  });
  $("#appointment-form-modal").on("submit", function (event) {
    event.preventDefault();
    if ($("#provider").val() == "") {
      return alert("Debes seleccionar un Lugar de Atención");
    }
    const slotTimestamp = $("#slot_timestamp");
    if (slotTimestamp.val() == "") {
      return alert("Debes seleccionar una Hora");
    }
    const slotSelected = slotTimestamp.find("option:selected");
    if (!slotSelected.val()) {
      return alert("Debes seleccionar una Hora");
    }
    const slotStart = slotSelected.data("slot-start");
    const slotEnd = slotSelected.data("slot-end");
    const slotEndTimestamp = slotSelected.data("slot-end-timestamp");

    const appointmentDateVal = $("#appointment_date").val();
    if (!appointmentDateVal) {
      return alert("Debes seleccionar una Fecha");
    }
    const dateParts = appointmentDateVal.split("-");
    const currentForm = $(this);
    currentForm.block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });

    wp.apiFetch({
      method: "POST",
      path: api.add_appointment,
      data: [
        {
          service: serviceId,
          provider: currentForm.find("#provider").val(),
          date: `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`,
          date_timestamp: new Date(appointmentDateVal).getTime() / 1000,
          slot: slotStart,
          slot_end: slotEnd,
          slot_timestamp: currentForm.find("#slot_timestamp").val(),
          slot_end_timestamp: slotEndTimestamp,
          status: "pending",
          user_name: currentForm.find("#user_name").val(),
          user_email: currentForm.find("#user_email").val() || correoPorDefecto,
          user_phone: currentForm.find("#user_phone").val(),
          paciente_id: currentForm.find("#paciente_id").val(),
          observations: currentForm.find("#observations").val(),
          event_type: "appointment",
        },
      ],
    })
      .then(function (response) {
        if (response.success) {
          $.colorbox.close();
          $("#wcfm-calendar").fullCalendar("refetchEvents");
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-completed"></span>' + response.data
            )
            .addClass("wcfm-success")
            .slideDown();
          // reset fields
          $("#user_name").val("");
          $("#user_email").val("");
          $("#user_phone").val("");
          $("#provider").val("");
          $("#appointment_date").val("");
          $("#observations").val("");
          $("#slot_timestamp").empty();
          $("#slot_timestamp").append(
            "<option value=''>Selecciona una hora</option>"
          );
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              `<span class="wcicon-status-cancelled"></span> ${response.data}`
            )
            .slideDown();
          $("#wcfm-content .wcfm-message").notifyModal({
            duration: 3000,
            placement: "center",
            type: "alert",
            overlay: true,
            icon: false,
          });
        }
      })
      .finally(function () {
        currentForm.unblock();
        wcfmMessageHide();
      });
  });
  //Control
  $("#paciente_id_control")
    .select2({
      dropdownParent: $("#control-form-modal"),
    })
    .on("change", function () {
      const currentObj = $(this);
      if (currentObj.val() && currentObj.val() != 0) {
        const option = currentObj.find("option:selected");
        let fullName = option.data("name");
        const lastName = option.data("last_name");
        if (lastName) {
          fullName += " " + lastName;
        }
        $("#control_user_name").val(fullName);
        $("#control_user_email").val(option.data("email"));
        $("#control_user_phone").val(option.data("phone"));
        $("#control_observations").val(option.data("observations"));
      }
    });
  $("#control_date").datepicker({
    minDate: 0,
  });
  $("#wcfm-content").on("click", ".add-edit-next_control_date", function () {
    if (serviceId == 0) {
      return alert("No tiene un tarjeta digital asignada");
    }
    const id = $(this).data("id");
    const isEdit = id ? true : false;
    $("#modal-title-prefix-control").text(isEdit ? "EDITAR" : "AÑADIR");
    $.colorbox({
      href: "#control-form-modal",
      inline: true,
      width: $popup_width,
      onComplete: function () {
        $("#control_user_name").focus();
      },
    });
  });
  $("#cancel-button-control").on("click", function () {
    $.colorbox.close();
  });
  // get date in format YYYY-MM-DD
  $("#control_date, #control_provider").on("change", function () {
    const slotSelect = $("#control_slot_timestamp");
    slotSelect.empty();
    slotSelect.append("<option value=''>Selecciona una hora</option>");
    const providerId = $("#control_provider").val();
    const appointmentDateVal = $("#control_date").val();
    if (!providerId || !appointmentDateVal) return;

    let dateNow = new Date();

    wp.apiFetch({
      method: "POST",
      path: api.date_slots,
      data: {
        service: serviceId,
        provider: providerId,
        date: new Date(appointmentDateVal).getTime() / 1000,
        timestamp: Math.floor(
          (dateNow.getTime() - dateNow.getTimezoneOffset() * 60 * 1000) / 1000
        ),
        admin: true,
      },
    }).then(function (response) {
      if (response.success) {
        const slots = response.data.slots;
        // iterate over slots and append to select - slots is an object with objects inside
        if (typeof slots === "string") {
          alert(slots);
          return;
        }

        for (const slot in slots) {
          // get hour from slot from slots[slot].from timestamp
          const dateFrom = new Date(slots[slot].from * 1000);
          const hourFrom = String(dateFrom.getUTCHours()).padStart(2, "0");
          const minuteFrom = String(dateFrom.getUTCMinutes()).padStart(2, "0");
          const stringSlotStart = `${hourFrom}:${minuteFrom}`;
          // get hour from slot from slots[slot].to timestamp
          const dateTo = new Date(slots[slot].to * 1000);
          const hourTo = String(dateTo.getUTCHours()).padStart(2, "0");
          const minuteTo = String(dateTo.getUTCMinutes()).padStart(2, "0");
          const stringSlotEnd = `${hourTo}:${minuteTo}`;
          slotSelect.append(
            `<option value="${slot}" data-slot-start="${stringSlotStart}" data-slot-end="${stringSlotEnd}" data-slot-end-timestamp="${slots[slot].to}">
                ${stringSlotStart} - ${stringSlotEnd}
              </option>`
          );
        }
      } else {
        alert(response.data);
      }
    });
  });
  $("#control-form-modal").on("submit", function (event) {
    event.preventDefault();
    if ($("#control_provider").val() == "") {
      return alert("Debes seleccionar un Lugar de Atención");
    }
    const slotTimestamp = $("#control_slot_timestamp");
    if (slotTimestamp.val() == "") {
      return alert("Debes seleccionar una Hora");
    }
    const slotSelected = slotTimestamp.find("option:selected");
    if (!slotSelected.val()) {
      return alert("Debes seleccionar una Hora");
    }
    const slotStart = slotSelected.data("slot-start");
    const slotEnd = slotSelected.data("slot-end");
    const slotEndTimestamp = slotSelected.data("slot-end-timestamp");

    const appointmentDateVal = $("#control_date").val();
    if (!appointmentDateVal) {
      return alert("Debes seleccionar una Fecha");
    }
    const dateParts = appointmentDateVal.split("-");
    const currentForm = $(this);
    currentForm.block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });

    wp.apiFetch({
      method: "POST",
      path: api.add_appointment,
      data: [
        {
          service: serviceId,
          provider: currentForm.find("#control_provider").val(),
          date: `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`,
          date_timestamp: new Date(appointmentDateVal).getTime() / 1000,
          slot: slotStart,
          slot_end: slotEnd,
          slot_timestamp: currentForm.find("#control_slot_timestamp").val(),
          slot_end_timestamp: slotEndTimestamp,
          status: "pending",
          user_name: currentForm.find("#control_user_name").val(),
          user_email:
            currentForm.find("#control_user_email").val() || correoPorDefecto,
          user_phone: currentForm.find("#control_user_phone").val(),
          paciente_id: currentForm.find("#paciente_id_control").val(),
          observations: currentForm.find("#control_observations").val(),
          event_type: "next_control_date",
        },
      ],
    })
      .then(function (response) {
        if (response.success) {
          $.colorbox.close();
          $("#wcfm-calendar").fullCalendar("refetchEvents");
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-completed"></span>' + response.data
            )
            .addClass("wcfm-success")
            .slideDown();
          // reset fields
          $("#control_user_name").val("");
          $("#control_user_email").val("");
          $("#control_user_phone").val("");
          $("#control_provider").val("");
          $("#control_date").val("");
          $("#control_observations").val("");
          $("#control_slot_timestamp").empty();
          $("#control_slot_timestamp").append(
            "<option value=''>Selecciona una hora</option>"
          );
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              `<span class="wcicon-status-cancelled"></span> ${response.data}`
            )
            .slideDown();
          $("#wcfm-content .wcfm-message").notifyModal({
            duration: 3000,
            placement: "center",
            type: "alert",
            overlay: true,
            icon: false,
          });
        }
      })
      .finally(function () {
        currentForm.unblock();
        wcfmMessageHide();
      });
  });

  // PROCEDIMIENTO
  $("#paciente_id_procedimiento")
    .select2({
      dropdownParent: $("#procedimiento-form-modal"),
    })
    .on("change", function () {
      const currentObj = $(this);
      if (currentObj.val() && currentObj.val() != 0) {
        const option = currentObj.find("option:selected");
        let fullName = option.data("name");
        const lastName = option.data("last_name");
        if (lastName) {
          fullName += " " + lastName;
        }
        $("#procedimiento_user_name").val(fullName);
        $("#procedimiento_user_email").val(option.data("email"));
        $("#procedimiento_user_phone").val(option.data("phone"));
      }
    });
  $("#procedimiento_date").datepicker({
    minDate: 0,
  });
  $("#wcfm-content").on("click", ".add-edit-procedimiento", function () {
    if (serviceId == 0) {
      return alert("No tiene un tarjeta digital asignada");
    }
    const id = $(this).data("id");
    const isEdit = id ? true : false;
    $("#modal-title-prefix-procedimiento").text(isEdit ? "EDITAR" : "AÑADIR");
    $.colorbox({
      href: "#procedimiento-form-modal",
      inline: true,
      width: $popup_width,
      onComplete: function () {
        $("#procedimiento_user_name").focus();
      },
    });
  });
  $("#cancel-button-procedimiento").on("click", function () {
    $.colorbox.close();
  });
  // get date in format YYYY-MM-DD
  $("#procedimiento_date, #procedimiento_provider").on("change", function () {
    const slotSelect = $("#procedimiento_slot_timestamp");
    slotSelect.empty();
    slotSelect.append("<option value=''>Selecciona una hora</option>");
    const providerId = $("#procedimiento_provider").val();
    const appointmentDateVal = $("#procedimiento_date").val();
    if (!providerId || !appointmentDateVal) return;

    let dateNow = new Date();

    wp.apiFetch({
      method: "POST",
      path: api.date_slots,
      data: {
        service: serviceId,
        provider: providerId,
        date: new Date(appointmentDateVal).getTime() / 1000,
        timestamp: Math.floor(
          (dateNow.getTime() - dateNow.getTimezoneOffset() * 60 * 1000) / 1000
        ),
        admin: true,
      },
    }).then(function (response) {
      if (response.success) {
        const slots = response.data.slots;
        // iterate over slots and append to select - slots is an object with objects inside
        if (typeof slots === "string") {
          alert(slots);
          return;
        }

        for (const slot in slots) {
          // get hour from slot from slots[slot].from timestamp
          const dateFrom = new Date(slots[slot].from * 1000);
          const hourFrom = String(dateFrom.getUTCHours()).padStart(2, "0");
          const minuteFrom = String(dateFrom.getUTCMinutes()).padStart(2, "0");
          const stringSlotStart = `${hourFrom}:${minuteFrom}`;
          // get hour from slot from slots[slot].to timestamp
          const dateTo = new Date(slots[slot].to * 1000);
          const hourTo = String(dateTo.getUTCHours()).padStart(2, "0");
          const minuteTo = String(dateTo.getUTCMinutes()).padStart(2, "0");
          const stringSlotEnd = `${hourTo}:${minuteTo}`;
          slotSelect.append(
            `<option value="${slot}" data-slot-start="${stringSlotStart}" data-slot-end="${stringSlotEnd}" data-slot-end-timestamp="${slots[slot].to}">
              ${stringSlotStart} - ${stringSlotEnd}
            </option>`
          );
        }
      } else {
        alert(response.data);
      }
    });
  });
  $("#procedimiento-form-modal").on("submit", function (event) {
    event.preventDefault();
    if ($("#procedimiento_provider").val() == "") {
      return alert("Debes seleccionar un Lugar de Atención");
    }
    const slotTimestamp = $("#procedimiento_slot_timestamp");
    if (slotTimestamp.val() == "") {
      return alert("Debes seleccionar una Hora");
    }
    const slotSelected = slotTimestamp.find("option:selected");
    if (!slotSelected.val()) {
      return alert("Debes seleccionar una Hora");
    }
    const slotStart = slotSelected.data("slot-start");
    const slotEnd = slotSelected.data("slot-end");
    const slotEndTimestamp = slotSelected.data("slot-end-timestamp");

    const appointmentDateVal = $("#procedimiento_date").val();
    if (!appointmentDateVal) {
      return alert("Debes seleccionar una Fecha");
    }
    const dateParts = appointmentDateVal.split("-");
    const currentForm = $(this);
    currentForm.block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });

    wp.apiFetch({
      method: "POST",
      path: api.add_appointment,
      data: [
        {
          service: serviceId,
          provider: currentForm.find("#procedimiento_provider").val(),
          date: `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`,
          date_timestamp: new Date(appointmentDateVal).getTime() / 1000,
          slot: slotStart,
          slot_end: slotEnd,
          slot_timestamp: currentForm
            .find("#procedimiento_slot_timestamp")
            .val(),
          slot_end_timestamp: slotEndTimestamp,
          status: "pending",
          user_name: currentForm.find("#procedimiento_user_name").val(),
          user_email:
            currentForm.find("#procedimiento_user_email").val() ||
            correoPorDefecto,
          user_phone: currentForm.find("#procedimiento_user_phone").val(),
          paciente_id: currentForm.find("#paciente_id_procedimiento").val(),
          observations: currentForm.find("#procedimiento_observations").val(),
          event_type: "surgery_date",
        },
      ],
    })
      .then(function (response) {
        if (response.success) {
          $.colorbox.close();
          $("#wcfm-calendar").fullCalendar("refetchEvents");
          $("#wcfm-content .wcfm-message")
            .html(
              '<span class="wcicon-status-completed"></span>' + response.data
            )
            .addClass("wcfm-success")
            .slideDown();
          // reset fields
          $("#procedimiento_user_name").val("");
          $("#procedimiento_user_email").val("");
          $("#procedimiento_user_phone").val("");
          $("#procedimiento_provider").val("");
          $("#procedimiento_date").val("");
          $("#procedimiento_observations").val("");
          $("#procedimiento_slot_timestamp").empty();
          $("#procedimiento_slot_timestamp").append(
            "<option value=''>Selecciona una hora</option>"
          );
        } else {
          $("#wcfm-content .wcfm-message")
            .html(
              `<span class="wcicon-status-cancelled"></span> ${response.data}`
            )
            .slideDown();
          $("#wcfm-content .wcfm-message").notifyModal({
            duration: 3000,
            placement: "center",
            type: "alert",
            overlay: true,
            icon: false,
          });
        }
      })
      .finally(function () {
        currentForm.unblock();
        wcfmMessageHide();
      });
  });
  // Abrir modal de recordatorio
  $("#wcfm-content").on("click", ".add-edit-reminder", function () {
    if (serviceId == 0) {
      return alert("No tiene un tarjeta digital asignada");
    }
    const id = $(this).data("id");
    const isEdit = id ? true : false;
    $("#modal-title-prefix-reminder").text(isEdit ? "EDITAR" : "AÑADIR");
    $.colorbox({
      href: "#reminder-form-modal",
      inline: true,
      width: $popup_width,
      onComplete: function () {
        $("#lugar-atencion-reminder").focus();
      },
    });
  });
  // Cerrar modal de recordatorio
  $("#cancel-button-reminder").on("click", function () {
    $.colorbox.close();
  });
  //Modal de mensajes
  $("#reminder-form-modal").on("submit", function (e) {
    e.preventDefault();

    const formData = {
      action: "guardar_recordatorio_cita",
      lugar: $("#lugar-atencion-reminder").val(),
      first_msg: $("#first_msg").val(),
      end_msg: $("#end_msg").val(),
      signature: $("#signature").val(),
      service_id: serviceId,
      user_id: $("#agenda-wrapper").data("user-id"),
      wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
    };
    $.post(wcfm_params.ajax_url, formData, function (response) {
      if (response.success) {
        alert("Recordatorio guardado correctamente");
        $.colorbox.close();
        location.reload();
      } else {
        alert("Error al guardar el recordatorio: " + response.data);
      }
    });
  });
});
