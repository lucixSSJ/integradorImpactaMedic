jQuery(document).ready(function ($) {
  // get all select with class .appointment-provider and if has only one option with
  // not empty value then select this first option and trigger change event
  setTimeout(() => {
    $("select.appointment-provider").each(function () {
      if ($(this).find("option[value!='']").length === 1) {
        $(this)
          .val($(this).find("option[value!='']").first().val())
          .trigger("change");
      }
    });
  }, 2000);
});
