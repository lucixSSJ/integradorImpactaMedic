jQuery(document).ready(function ($) {
  if (typeof $.fn.collapsible === "function") {
    // Collapsible
    let defaultOpen = "wcfm_collapsible_head";
    if (window.location.hash) {
      defaultOpen = window.location.hash.replace("#", "");
    }
    $(".page_collapsible").collapsible({
      defaultOpen,
      speed: "slow",
      loadOpen: function (elem, opts) {
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
        resetCollapsHeight(elem.next().find(".wcfm-content"));
      },
      loadClose: function (elem, opts) {
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
        elem
          .find("span")
          .removeClass("fa-arrow-alt-circle-right block-indicator");
        elem.next().stop(true, true).slideUp(opts.speed);
      },
    });
  }
  // Tabheight
  $(".page_collapsible").each(function () {
    if (!$(this).hasClass("wcfm_head_hide")) {
      collapsHeight += $(this).height() + 50;
    }
  });

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

  let blocksHtml = `<option value="">TODOS</option>`;
  for (const customBlock in customBlocks) {
    blocksHtml += `<option value="${customBlock}">${customBlocks[customBlock]}</option>`;
  }
  $(document.body).on("updated_wcfm-datatable", function () {
    $(".wcfm_item_delete").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        var rconfirm = confirm(
          "¿Seguro que quieres borrar este paciente?\nEsta acción no se puede deshacer?"
        );
        if (rconfirm) deleteWCFMItem($(this));
        return false;
      });
    });
    $(".wcfm_item_share").each(function () {
      $(this).click(function (event) {
        event.preventDefault();
        const item = $(this);
        const url = item.data("url");
        $.colorbox({
          html: `
              <div id="custom-share-modal">
                <h2 class="text-center color-alt"><i class="fas fa-share-square"></i> IMPRIMIR REPORTE PACIENTE</h2>
                <hr class="color-alt" style="max-width:100%;opacity:1;"/>
                <form id="form_print_pdf" class="elementor-form-fields-wrapper">
                  <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;">
                    <select id="report_block" name="report_block" class="elementor-field elementor-size-sm">
                      ${blocksHtml}
                    </select>
                  </div>
                  <div class="elementor-column elementor-col-100 elementor-field-group" style="padding-left:2.5px;padding-right:2.5px;margin-bottom:10px;justify-content:center;gap:10px;">
                    <button type="submit" class="btn btn-style-semi-round btn-icon-pos-left btn-color-danger" data-format="a4">
                      A4 PDF
                      <span class="wd-btn-icon">
                        <i class="far fa-file-pdf"></i> 
                      </span>
                    </button>
                    <button type="submit" class="btn btn-style-semi-round btn-icon-pos-left btn-color-danger" data-format="a5">
                      A5 PDF
                      <span class="wd-btn-icon">
                        <i class="far fa-file-pdf"></i> 
                      </span>
                    </button>
                  </div>
                </form>
              </div>`,
          width: $popup_width,
          onComplete: function () {
            $("#form_print_pdf").submit(function (event) {
              event.preventDefault();
              const customBlock = $(this)
                .find("select[name='report_block']")
                .val();
              const format = $(event.originalEvent.submitter).data("format");
              window.open(
                `${url}/?report_block=${customBlock}&format=${format}`,
                "_blank"
              );
            });
          },
        });
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
      action: "delete_wcfm_patient",
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
});
