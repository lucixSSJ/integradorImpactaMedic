jQuery(document).ready(function ($) {
  const lugarAtencionUrl = $("#lugar_atencion_url").val();
  $wcfm_datatable = $("#wcfm-datatable").DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    pageLength: 10,
    lengthChange: false,
    language: $.parseJSON(dataTables_language),
    // stateSave: true,
    columns: [
      { responsivePriority: 1 },
      { responsivePriority: 2 },
      { responsivePriority: 1 },
    ],
    columnDefs: [
      { targets: 0, orderable: true },
      { targets: 1, orderable: true },
      { targets: 2, orderable: false },
    ],
    order: [[0, "desc"]],
    ajax: function (data, callback, settings) {
      const params = new URLSearchParams({
        // Disable author filter because not work with wordfence plugin
        // https://www.wordfence.com/help/firewall/brute-force/#prevent-username-discovery
        // author: currentAuthorId,
        // page: data.start / data.length + 1,
        // per_page: data.length,
        per_page: -1,
        search: data.search.value,
      });

      if (data.order.length) {
        const order = data.order[0];
        const orderby = order.column === 0 ? "id" : "title";
        params.append("orderby", orderby);
        params.append("order", order.dir);
      }

      wp.apiFetch({
        path: `/wp/v2/lugares-atencion?${params.toString()}`,
      }).then((posts) => {
        callback({
          draw: data.draw,
          recordsTotal: posts.length,
          recordsFiltered: posts.length,
          data: posts.map((post) => {
            return [
              post.id,
              post.title.rendered,
              `<a class="wcfm-action-icon" href="${lugarAtencionUrl}${post.id}"
                >
                <span class="wcfmfa fa-edit text_tip" data-tip="Editar"></span>
              </a>
              <a class="wcfm-action-icon wcfm_delete_lugar_atencion" href="javascript:void(0)" data-id="${post.id}">
                <span class="wcfmfa fa-trash-alt text_tip" data-tip="Eliminar"></span>
              </a>
              `,
            ];
          }),
        });
      });
    },
  });

  // Delete lugar_atencion
  $("#wcfm-content").on(
    "click",
    ".wcfm_delete_lugar_atencion",
    function (event) {
      event.preventDefault();

      const id = $(this).data("id");
      if (confirm("¿Estás seguro de eliminar este lugar de atención?")) {
        $.blockUI({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6,
          },
        });
        wp.apiFetch({
          method: "DELETE",
          path: `/wp/v2/lugares-atencion/${id}?force=true`,
        })
          .then(function (response) {
            $wcfm_datatable.ajax.reload();
            $("#wcfm-content .wcfm-message")
              .html(
                '<span class="wcicon-status-completed"></span>' +
                  "Lugar de atención eliminado correctamente"
              )
              .addClass("wcfm-success")
              .slideDown();
          })
          .catch(function (error) {
            alert(error.message);
          })
          .finally(function () {
            $.unblockUI();
            wcfmMessageHide();
          });
      }
    }
  );
});
