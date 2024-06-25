jQuery(document).ready(function ($) {
  $("#submitdiv a.submitdelete")
    .text()
    .replace("Verplaatsen naar prullenbak", "Naar prullenbak");

  jQuery("#postimagediv a#remove-post-thumbnail")
    .text()
    .replace("Uitgelichte afbeelding verwijderen", "");
});
