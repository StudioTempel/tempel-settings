jQuery(document).ready(function ($) {
  // Init select2 for forms

  if ($("#conversion_selected_forms").length) {
    $("#conversion_selected_forms").select2({
      placeholder: "Selecteer formulieren",
      allowClear: true,
      multiple: true,
    });
  }

  if ($("#status_safeupdate_day").length) {
    $("#status_safeupdate_day").select2({
      minimumResultsForSearch: -1,
      placeholder: "Selecteer de dag waarop de safeupdate plaatsvind",
    });
  }

  // $("#total-conversion-scope").select2({
  //   minimumResultsForSearch: -1,
  // });

  // Init flatpickr for backup interval
  if ($("#status_backup_interval").length) {
    $("#status_backup_interval").flatpickr({
      enableTime: true,
      dateFormat: "H:i",
      time_24hr: true,
      noCalendar: true,
    });
  }

  $("button#reset_status_last_checkup_date").on("click", function () {
    if (confirm("Weet je zeker dat je de checkup wilt resetten?")) {
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "reset_checkup",
        },
        success: function (response) {
          alert("Checkup is gereset");
          console.log(response);
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
  });

  $("button#support_clear_faq_cache").on("click", function () {
    if (confirm("Weet je zeker dat je de FAQ cache wilt legen?")) {
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "clear_faq_cache",
        },
        success: function (response) {
          alert("FAQ cache is geleegd");
          console.log(response);
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
  });

  $(".category__header input[type='checkbox']").on("change", function () {
    console.log("change");

    let $settingsCategory = $(this).closest(".settings__category");
    $settingsCategory
      .find(".category__content.content__collapsable")
      .slideToggle();
  });
});
