jQuery(document).ready(function ($) {
  send_ajax_post_visitor();

  $(document).on("click", ".click-share", function () {
    send_ajax_post_visitor(true);
  });

  $(document).on("submit", ".send-form-share form", function () {
    send_ajax_post_visitor(true);
  });

  function send_ajax_post_visitor(has_shared = false) {
    $.ajax({
      url: ajax_var.url,
      type: "post",
      data: {
        action: ajax_var.action,
        nonce: ajax_var.nonce,
        post_id: ajax_var.post_id,
        has_shared,
      },
    });
  }
});
