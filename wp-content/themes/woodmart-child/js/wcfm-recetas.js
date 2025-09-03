$tag_id = "";
$tag_name = "";
jQuery(document).ready(function ($) {
  $wcfm_datatable = $("#wcfm-datatable").DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    pageLength: 10,
    language: $.parseJSON(dataTables_language),
    // stateSave: true,
    columns: [
      { responsivePriority: 1 },
      { responsivePriority: 1 },
      { responsivePriority: 2 },
      { responsivePriority: 3 },
      { responsivePriority: 4 },
      { responsivePriority: 1 },
    ],
    columnDefs: [
      { targets: 0, orderable: true },
      { targets: 1, orderable: true },
      { targets: 2, orderable: true },
      { targets: 3, orderable: false },
      { targets: 4, orderable: false },
      { targets: 5, orderable: false },
    ],
    order: [[0, "desc"]],
    dom: "Bfrtip",
    buttons: [
      {
        extend: "print",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "pdfHtml5",
        orientation: "landscape",
        pageSize: "LEGAL",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "excelHtml5",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
      {
        extend: "csv",
        exportOptions: {
          columns: ":not(:last-child)",
        },
      },
    ],
    ajax: {
      type: "POST",
      url: wcfm_params.ajax_url,
      data: function (d) {
        (d.action = "wcfm_ajax_controller"),
          (d.controller = "wcfm-recetas"),
          (d.tag_id = $tag_id),
          (d.wcfm_ajax_nonce = wcfm_params.wcfm_ajax_nonce);
      },
      complete: function () {
        // Fire wcfm-table refresh complete
        $(document.body).trigger("updated_wcfm-datatable");
      },
    },
  });

  // Filters
  if ($(".wcfm_filters_wrap").length > 0) {
    $(".dataTable").before($(".wcfm_filters_wrap"));
    $(".wcfm_filters_wrap").css("display", "inline-block");
  }
  // Tags module
  $("#tag_id")
    .select2()
    .on("change", function () {
      $tag_id = $(this).val();
      $wcfm_datatable.ajax.reload();
      if ($tag_id) {
        $("#add-edit-button").text("Editar Etiqueta");
        $tag_name = $(this).find("option:selected").text().trim();
        $("#delete-button").show();
      } else {
        $("#add-edit-button").text("Añadir Etiqueta");
        $tag_name = "";
        $("#delete-button").hide();
      }
    });
  $("#add-edit-button").on("click", function () {
    const prefixLabel = $tag_id ? "EDITAR" : "AÑADIR";
    $.colorbox({
      html: `<form id="tag-form-modal">
        <h2 class="text-center color-alt"><i class="fas fa-tag"></i> ${prefixLabel} ETIQUETA</h2>
        <div class="wcfm-content">
          <input type="hidden" id="tag_id" name="tag_id" class="hidden" value="${$tag_id}">
          <label for="name" class="wcfm_title">Nombre Etiqueta</label>
          <input type="text" id="name" name="name" class="wcfm-text" value="${$tag_name}" required>
        </div>
        <div class="text-right">
          <button type="button" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-default" id="cancel-button">
            Cancelar
            <span class="wd-btn-icon">
						  <span class="wd-icon fas fa-ban"></span>
					  </span>
          </button>
          <button type="submit" class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
            ${prefixLabel}
            <span class="wd-btn-icon">
						  <span class="wd-icon fas fa-paper-plane"></span>
					  </span>
          </button>
        </div>
      </form>`,
      width: $popup_width,
      onComplete: function () {
        $("#name").focus().select();
        $("#cancel-button").on("click", function () {
          $.colorbox.close();
        });

        $("#tag-form-modal").on("submit", function (event) {
          event.preventDefault();
          const currentForm = $(this);
          currentForm.block({
            message: null,
            overlayCSS: {
              background: "#fff",
              opacity: 0.6,
            },
          });
          $.ajax({
            type: "POST",
            url: wcfm_params.ajax_url,
            data: {
              action: "wcfm_ajax_controller",
              controller: "wcfm-tags-manage",
              wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
              wcfm_tag_form: currentForm.serialize(),
            },
            success: function (response) {
              response = $.parseJSON(response);
              if (response.status) {
                if ($tag_id) {
                  // select2 #tag_id update text of custom option by value
                  $("#tag_id").select2("destroy");
                  $("#tag_id option[value='" + $tag_id + "']").text(
                    response.data.tag_name
                  );
                  $("#tag_id").select2();
                } else {
                  const newTag = new Option(
                    response.data.tag_name,
                    response.data.tag_id,
                    false,
                    false
                  );
                  $("#tag_id").append(newTag).trigger("change");
                }
                $wcfm_datatable.ajax.reload();
                $.colorbox.close();
              } else {
                alert(response.message);
              }
            },
            complete: function () {
              currentForm.unblock();
            },
          });
        });
      },
    });
  });
  // Implement delete tag with ajax
  $("#delete-button").on("click", function () {
    const rconfirm = confirm(
      `¿Seguro que quiere eliminar la etiqueta: ${$tag_name}?`
    );
    if (rconfirm) {
      $.ajax({
        type: "POST",
        url: wcfm_params.ajax_url,
        data: {
          action: "delete_wcfm_tag",
          tagid: $tag_id,
          wcfm_ajax_nonce: wcfm_params.wcfm_ajax_nonce,
        },
        success: function (response) {
          if (response.success) {
            $("#tag_id option[value='" + $tag_id + "']").remove();
            $("#tag_id").select2("destroy");
            $("#tag_id").select2();
            $wcfm_datatable.ajax.reload();
            $.colorbox.close();
          } else {
            alert(response.data);
          }
        },
      });
    }
  });
});
