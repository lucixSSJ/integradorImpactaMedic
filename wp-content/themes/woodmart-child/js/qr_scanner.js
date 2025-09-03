jQuery(document).ready(function ($) {
  var qrScanner = null;
  $(".wcfm_order_read_qr_customer").click(function (event) {
    event.preventDefault();
    $("#wcfm_orders_manage_expander").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
    jQuery.colorbox({
      html: `<div class="elementor-widget-video">
                <div class="e-hosted-video elementor-wrapper elementor-open-inline">
                  <video id="qr-video" class="elementor-video" disablepictureinpicture playsinline></video>
                </div>
            </div>`,
      //   height: "auto",
      width: $popup_width,
      onComplete: function () {
        const videoElem = document.getElementById("qr-video");
        qrScanner = new QrScanner(videoElem, (qrResult) => {
          jQuery.colorbox.remove();
          var customerSelect = $("#wcfm_orders_manage_expander").find(
            "#customer_id"
          );
          if (customerSelect.length > 0) {
            customerSelect.select2("open");
            $("input.select2-search__field")
              .eq(0)
              .val(qrResult)
              .trigger("input");
          }
        });
        qrScanner.start();
      },
      onClosed: function () {
        qrScanner.stop();
      },
    });
    $("#wcfm_orders_manage_expander").unblock();
  });
});
