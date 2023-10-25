jQuery(document).ready(function () {
    const body = jQuery('body');
    let html = '';
    html += `
            <div class="wrapper">
      <div class="snow layer1 a"></div>
      <div class="snow layer1"></div>
     <div class="snow layer2 a"></div>     <div class="snow layer2"></div>
      <div class="snow layer3 a"></div>
     <div class="snow layer3"></div>

 </div>
             `;

    // add placeholder to user_login field
    jQuery('#user_login').attr('placeholder', 'Emailadres');
    jQuery('#user_pass').attr('placeholder', 'Wachtwoord');

    body.append('<div class="contact"><a href="https://studiotempel.nl" target="_blank"><img src="https://studiotempel.nl/branding/builtby.svg" alt="Built by studiotempel"></a></div>');
    // body.append('<div class="message float animate">StudioTempel is verhuisd naar <a href="https://goo.gl/maps/qtvVK7ujB2vKrXpf6">Bagijnhof 46</a></div>');
    // body.append('<div class="message float animate">Fijne feestdagen! ðŸŽ„</div>');
//    body.append('<div class="message float animate">Check onze nieuwe website op <a href="https://studiotempel.nl" target="_blank">studiotempel.nl</a> ðŸŽ‰</div>');
//    body.append(html);


//    body.append('<div class="message float top"><a href="https://studiotempel.nl" target="_blank">5 tips om jouw website te optimalieren voor seo ðŸ¤“</a></div>');
//    body.append('<div class="message float animate">Have a nice day! ðŸŒˆ</div>');

    const message = jQuery('body .message.float.animate');
    setTimeout(function () {
        message.addClass('show');
    }, 500);

}());
