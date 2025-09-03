jQuery(document).ready(function ($) {
  $("textarea").autosize();

  // Listen to autosize.resized events
  $(document).on("autosize.resized", "textarea", function () {
    // Call resetCollapsHeight when any textarea is resized
    if (typeof resetCollapsHeight === "function") {
      resetCollapsHeight($(this).closest(".wcfm-content"));
    }
  });

  $(document).on("cx-control-init", function (event, data) {
    if (data?.target) {
      data.target.find("textarea").each(function () {
        $(this).autosize();
      });
    }
  });
});
