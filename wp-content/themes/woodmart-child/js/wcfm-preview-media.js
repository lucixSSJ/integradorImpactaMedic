jQuery(document).ready(function ($) {
  // Open a lightbox with the media library
  $("#wcfm-content").on("click", "div.preview-holder", function (event) {
    event.preventDefault();
    var imageUrl = $(this).attr("data-url-attr");
    var isImage = /\.(jpg|jpeg|png|gif|bmp)$/i.test(imageUrl);
    if (isImage) {
      const title = $(this).siblings("span.title").text().trim();
      $.colorbox({
        href: imageUrl,
        maxWidth: "90%",
        maxHeight: "90%",
        photo: true, // ensures it treats the content as an image
        title,
      });
    }
  });
});
