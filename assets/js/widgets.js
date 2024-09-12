jQuery(document).ready(function ($) {
  $(".widget__content__dropdown").on("click", function () {
    $(this).toggleClass("active");
    $(this).find(".item__dropdown__value").slideToggle();
  });

  // $("#send-sitescan-email").on("click", function (e) {
  //   e.preventDefault();
  //   $.ajax({
  //     url: ajaxurl,
  //     type: "POST",
  //     data: {
  //       action: "send_sitescan_email",
  //     },
  //     success: function (response) {
  //       console.log(response);
  //     },
  //   });
  // });
});
