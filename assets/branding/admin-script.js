jQuery(document).ready(
  (function () {
    jQuery("#submitdiv a.submitdelete").text(
      jQuery("#submitdiv a.submitdelete")
        .text()
        .replace("Verplaatsen naar prullenbak", "Naar prullenbak")
    );
    jQuery("#postimagediv a#remove-post-thumbnail").text(
      jQuery("#postimagediv a#remove-post-thumbnail")
        .text()
        .replace("Uitgelichte afbeelding verwijderen", "")
    );
    // jQuery(".misc-pub-post-status").text(jQuery(".misc-pub-post-status").text().replace(" Status: ", "Status"));

    //    const body = jQuery('body');
    //    body.append('<div class="contact">Design & realisatie: <a href="https://studiotempel.nl" target="_blank">Studio Tempel</a></div>');
    //    body.append('<div class="message float animate">Check onze nieuwe website op <a href="https://studiotempel.nl" target="_blank">studiotempel.nl</a> 🎉</div>');
    //    body.append('<div class="message float top"><a href="https://studiotempel.nl" target="_blank">5 tips om jouw website te optimalieren voor seo 🤓</a></div>');
    //    body.append('<div class="message float animate">Have a nice day! 🌈</div>');
    //
    //    const message = jQuery('body .message.float.animate');
    //     setTimeout(function () {
    //         message.addClass('show');
    //     }, 500);
  })()
);
