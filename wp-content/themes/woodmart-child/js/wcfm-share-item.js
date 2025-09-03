jQuery(document).ready(function ($) {
  let countriesHtml = "";
  const countrySelected = localStorage.getItem("countrySelected") ?? "51";
  for (const countryLabel of customData.countryCodes) {
    if (!Array.isArray(countryLabel)) {
      const countryCode = countryLabel.replace("+", "");
      countriesHtml += `<option value="${countryCode}" ${
        countryCode == countrySelected ? "selected" : ""
      }>${countryLabel}</option>`;
    }
  }
  let blocksHtml = "";
  if (Object.keys(customData.customBlocks).length > 0) {
    blocksHtml += `<div class="elementor-form-fields-wrapper">`;
    blocksHtml += `<div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">`;
    blocksHtml += `<select id="report_block" name="report_block" class="elementor-field elementor-size-sm">`;
    blocksHtml += `<option value="">TODOS</option>`;
    for (const customBlock in customData.customBlocks) {
      blocksHtml += `<option value="${customBlock}">${customData.customBlocks[customBlock]}</option>`;
    }
    blocksHtml += `</select>`;
    blocksHtml += `</div>`;
    blocksHtml += `</div>`;
  } else {
    blocksHtml += `<input type="hidden" name="report_block" value="" />`;
  }
  let formatsHtml = `<select id="format" name="format" class="elementor-field elementor-size-sm" style="width: auto;">`;
  for (const reportFormat in customData.reportFormats) {
    formatsHtml += `<option value="${reportFormat}">${customData.reportFormats[reportFormat]}</option>`;
  }
  formatsHtml += `</select>`;
  $(document.body).on("updated_wcfm-datatable", function () {
    $(".wcfm_item_share").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        const item = $(this);
        const recordId = item.data("id");
        const url = item.data("url");
        const email = item.data("email") ?? "";
        const phone = item.data("phone") ?? "";
        $.colorbox({
          html: `<form id="custom-share-modal">
                <h2 class="text-center color-alt"><i class="fas fa-share-square"></i> Compartir ${customData.shareTitle}</h2>
                <hr class="color-alt" style="max-width:100%;opacity:1;"/>
                ${blocksHtml}
                <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:20px">
                  ${formatsHtml}
                  <button type="submit" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-danger" value="pdf">
                    PDF
                    <span class="wd-btn-icon">
                      <i class="far fa-file-pdf"></i> 
                    </span>
                  </button>
                  <button type="submit" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success" value="direct-whatsapp">
                    WhatsApp
                    <span class="wd-btn-icon">
                      <i class="fab fa-whatsapp"></i> 
                    </span>
                  </button>
                  <button type="submit" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-link" value="direct-sms">
                    Sms
                    <span class="wd-btn-icon">
                      <i class="fas fa-sms"></i> 
                    </span>
                  </button>
                </div>
                <div class="elementor-form-fields-wrapper">
                  <input type="hidden" name="id" value="${recordId}" />
                  <div class="elementor-column elementor-col-80 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <input type="email" id="email" name="email" placeholder="Correo" class="elementor-field elementor-size-sm" value="${email}" />
                  </div>
                  <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success" value="email">
                      Enviar
                      <span class="wd-btn-icon">
                        <i class="far fa-paper-plane"></i> 
                      </span>
                    </button>
                  </div>
                  <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <select name="country_code_whatsapp" class="elementor-field elementor-size-sm">
                      ${countriesHtml}
                    </select>
                  </div>
                  <div class="elementor-column elementor-col-60 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <input type="tel" name="phone_number_whatsapp" placeholder="Número Celular" class="elementor-field elementor-size-sm" value="${phone}" />
                  </div>
                  <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-success" value="whatsapp">
                      Enviar
                      <span class="wd-btn-icon">
                        <i class="fab fa-whatsapp"></i> 
                      </span>
                    </button>
                  </div>
                  <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <select name="country_code_sms" class="elementor-field elementor-size-sm">
                      ${countriesHtml}
                    </select>
                  </div>
                  <div class="elementor-column elementor-col-60 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <input type="tel" name="phone_number_sms" placeholder="Número Celular" class="elementor-field elementor-size-sm" value="${phone}" />
                  </div>
                  <div class="elementor-column elementor-col-20 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <button type="submit" class="btn btn-full-width btn-icon-pos-left btn-color-link" value="sms">
                      Enviar
                      <span class="wd-btn-icon">
                        <i class="fas fa-sms"></i> 
                      </span>
                    </button>
                  </div>
                </div>
              </form>`,
          width: $popup_width,
          onComplete: function () {
            $("#email").focus();
            $("#custom-share-modal").on("submit", function (event) {
              event.preventDefault();
              const currentForm = $(this);
              const submitButton = event.originalEvent.submitter;
              const submitType = $(submitButton).val();

              const reportBlock = currentForm
                .find("[name='report_block']")
                .val();
              const reportFormat = currentForm.find("[name='format']").val();
              const formattedUrl = `${url}/?report_block=${reportBlock}&format=${reportFormat}`;
              const textFormatted = encodeURIComponent(
                `Accede a tu ${customData.shareTitle} desde el siguiente enlace ${formattedUrl}`
              );

              if (submitType === "email") {
                const email = currentForm.find('input[name="email"]').val();
                // Simple email validation regex
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                  alert("Por favor, ingrese un correo electrónico válido.");
                  currentForm.find('input[name="email"]').focus();
                  return;
                }
                const formObj = {};
                for (const iterator of currentForm.serializeArray()) {
                  formObj[iterator.name] = iterator.value;
                }
                currentForm.block({
                  message: "Enviando...",
                  overlayCSS: {
                    background: "#fff",
                    opacity: 0.6,
                  },
                });
                $.ajax({
                  type: "POST",
                  url: wcfm_params.ajax_url,
                  data: {
                    action: customData.emailAction,
                    wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
                    ...formObj,
                  },
                  success: function (response) {
                    alert(response.data);
                  },
                  complete: function () {
                    currentForm.unblock();
                  },
                });
              } else if (submitType === "whatsapp") {
                const countryCode = currentForm
                  .find('[name="country_code_whatsapp"]')
                  .val();
                if (!countryCode) {
                  alert("Por favor, seleccione un código de país.");
                  currentForm.find('[name="country_code_whatsapp"]').focus();
                  return;
                }
                const phoneNumber = currentForm
                  .find('[name="phone_number_whatsapp"]')
                  .val();
                if (!phoneNumber) {
                  alert("Por favor, ingrese un número de celular.");
                  currentForm.find('[name="phone_number_whatsapp"]').focus();
                  return;
                }
                window.open(
                  `https://api.whatsapp.com/send?phone=${countryCode}${phoneNumber}&text=${textFormatted}`,
                  "_blank"
                );
              } else if (submitType === "sms") {
                const countryCode = currentForm
                  .find('[name="country_code_sms"]')
                  .val();
                if (!countryCode) {
                  alert("Por favor, seleccione un código de país.");
                  currentForm.find('[name="country_code_sms"]').focus();
                  return;
                }
                const phoneNumber = currentForm
                  .find('[name="phone_number_sms"]')
                  .val();
                if (!phoneNumber) {
                  alert("Por favor, ingrese un número de celular.");
                  currentForm.find('[name="phone_number_sms"]').focus();
                  return;
                }
                window.open(
                  `sms:${countryCode}${phoneNumber}?body=${textFormatted}`,
                  "_blank"
                );
              } else if (submitType === "pdf") {
                window.open(formattedUrl, "_blank");
              } else if (submitType === "direct-whatsapp") {
                window.open(
                  `https://api.whatsapp.com/send?text=${textFormatted}`,
                  "_blank"
                );
              } else if (submitType === "direct-sms") {
                window.open(`sms:?&body=${textFormatted}`, "_blank");
              }
            });
            $('select[name="country_code"]').change(function () {
              localStorage.setItem("countrySelected", $(this).val());
            });
          },
        });
      });
    });
  });
});
