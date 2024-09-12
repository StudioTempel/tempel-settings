jQuery(document).ready(function ($) {
  $(".widget__content__dropdown").on("click", function () {
    $(this).toggleClass("active");
    $(this).find(".item__dropdown__value").slideToggle();
  });
});
