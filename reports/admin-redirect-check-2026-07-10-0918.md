# Admin redirect check (2026-07-10-0918)

Checked `/st-beheer/` and `/wp-admin/` via HTTP with curl following up to 10 redirects.

## BAD_WP_ADMIN (2)

- `overinzicht.nl/st-beheer/`: HTTP `200`, redirects `5`, final `https://overinzicht.nl/wp-login.php?redirect_to=https%3A%2F%2Foverinzicht.nl%2Fwp-admin%2F&reauth=1`
- `taxlex.nl/st-beheer/`: HTTP `403`, redirects `4`, final `https://taxlex.nl/wp-admin/`

## CHECK (26)

- `bierfabriek.com/st-beheer/`: HTTP `200`, redirects `2`, final `https://www.bierfabriek.com/`
- `cocorico.nl/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.cocorico.nl/st-beheer/`
- `deslimmechef.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://deslimmechef.nl/st-beheer/`
- `dianatromp.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://dianatromp.nl/st-beheer/`
- `dispatchapi.io/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.dispatchapi.io/st-beheer/`
- `femkecycling4kika.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://femkecycling4kika.nl/st-beheer/`
- `finelines.nl/st-beheer/`: HTTP `404`, redirects `1`, final `http://www.finelines.nl/st-beheer/`
- `forexfordynamics.com/st-beheer/`: HTTP `404`, redirects `2`, final `https://forexfordynamics.com/st-beheer`
- `horecabier.be/st-beheer/`: HTTP `404`, redirects `1`, final `https://horecabier.be/st-beheer/`
- `horizonflowerfamily.nl/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.horizonflowerfamily.nl/st-beheer/`
- `maartendegraaf.nl/st-beheer/`: HTTP `404`, redirects `1`, final `http://www.maartendegraaf.nl/st-beheer/`
- `mkba.info/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.mkba.info/st-beheer/`
- `nct.studiotempel.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://nct.studiotempel.nl/st-beheer/`
- `ofmedemblik.nl/st-beheer/`: HTTP `404`, redirects `3`, final `https://www.ofmedemblik.nl/st-beheer/?doing_wp_cron=1783667873.6489100456237792968750`
- `pgenkhuizen.nl/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.pgenkhuizen.nl/st-beheer/`
- `quicksilverbar.nl/st-beheer/`: HTTP `404`, redirects `2`, final `http://www.quicksilverbar.nl/st-beheer/?doing_wp_cron=1783667875.4082779884338378906250`
- `salonannemieke.nl/st-beheer/`: HTTP `404`, redirects `0`, final `http://salonannemieke.nl/st-beheer/`
- `shop.fullevents.nl/st-beheer/`: HTTP `404`, redirects `0`, final `http://shop.fullevents.nl/st-beheer/`
- `sinusjevi.nl/st-beheer/`: HTTP `200`, redirects `2`, final `https://sinusjevi.nl/`
- `tcwf.nl/st-beheer/`: HTTP `404`, redirects `0`, final `http://tcwf.nl/st-beheer/`
- `thefamilyhaarverzorging.nl/st-beheer/`: HTTP `404`, redirects `0`, final `http://thefamilyhaarverzorging.nl/st-beheer/`
- `trend-center.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://www.trend-center.nl/st-beheer/`
- `troostonline.nl/st-beheer/`: HTTP `404`, redirects `1`, final `https://troostonline.nl/st-beheer/`
- `vlaartechniek.nl/st-beheer/`: HTTP `404`, redirects `2`, final `https://www.vlaartechniek.nl/st-beheer/`
- `wardenaar.com/st-beheer/`: HTTP `404`, redirects `1`, final `https://wardenaar.com/st-beheer/`
- `woet.eu/st-beheer/`: HTTP `404`, redirects `1`, final `https://woet.eu/st-beheer/`

## ERROR (4)

- `kmsadvisreringen.nl/st-beheer/`: HTTP `000`, redirects `0`, final `http://kmsadvisreringen.nl/st-beheer/`, error `Could not resolve host: kmsadvisreringen.nl`
- `kmsadvisreringen.nl/wp-admin/`: HTTP `000`, redirects `0`, final `http://kmsadvisreringen.nl/wp-admin/`, error `Could not resolve host: kmsadvisreringen.nl`
- `woodstock-vloeren.com/st-beheer/`: HTTP `000`, redirects `0`, final `http://woodstock-vloeren.com/st-beheer/`, error `Could not resolve host: woodstock-vloeren.com`
- `woodstock-vloeren.com/wp-admin/`: HTTP `000`, redirects `0`, final `http://woodstock-vloeren.com/wp-admin/`, error `Could not resolve host: woodstock-vloeren.com`

## OK (70)

- `aica-summit.com/st-beheer/`: HTTP `200`, redirects `1`, final `https://aica-summit.com/st-beheer/`
- `axplain.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://axplain.nl/st-beheer/`
- `becam.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://becam.nl/st-beheer/`
- `bijoost.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://bijoost.nl/st-beheer/`
- `bit4u.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://bit4u.nl/st-beheer/`
- `boltcab.io/st-beheer/`: HTTP `200`, redirects `1`, final `https://boltcab.io/st-beheer/`
- `bouwbrug.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://bouwbrug.nl/st-beheer/`
- `bregbreg.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://bregbreg.nl/st-beheer/`
- `brynx.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://brynx.nl/st-beheer/`
- `buysmankruiden.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://buysmankruiden.nl/st-beheer/`
- `bvscout.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://bvscout.nl/st-beheer/`
- `chatlicense.com/st-beheer/`: HTTP `200`, redirects `1`, final `https://chatlicense.com/st-beheer/`
- `dataspark.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://dataspark.nl/st-beheer/`
- `deraadreinders.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://deraadreinders.nl/st-beheer/`
- `dycotrade.com/st-beheer/`: HTTP `200`, redirects `1`, final `https://dycotrade.com/st-beheer/`
- `epiphanyhomes.pt/st-beheer/`: HTTP `200`, redirects `1`, final `https://epiphanyhomes.pt/st-beheer/`
- `frisia.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://frisia.nl/st-beheer/`
- `funfestijn.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://funfestijn.nl/st-beheer/`
- `hetwapenvanmedemblik.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://hetwapenvanmedemblik.nl/st-beheer/`
- `hmg.studiotempel.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://hmg.studiotempel.nl/st-beheer/`
- `hoffdakentimmerwerken.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://hoffdakentimmerwerken.nl/st-beheer/`
- `hoogtij.amsterdam/st-beheer/`: HTTP `200`, redirects `1`, final `https://hoogtij.amsterdam/st-beheer/`
- `horecabier.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://horecabier.nl/st-beheer/`
- `jasperfijma.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://jasperfijma.nl/st-beheer/`
- `jerbohaarden.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://jerbohaarden.nl/st-beheer/`
- `karaat.nu/st-beheer/`: HTTP `200`, redirects `1`, final `https://karaat.nu/st-beheer/`
- `kbgmontage.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://kbgmontage.nl/st-beheer/`
- `kmsadvisering.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://kmsadvisering.nl/st-beheer/`
- `knipsalonhoutenbos.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://knipsalonhoutenbos.nl/st-beheer/`
- `koggevaarder.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://koggevaarder.nl/st-beheer/`
- `kooistrafeenstra.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://kooistrafeenstra.nl/st-beheer/`
- `leblancacademy.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://leblancacademy.nl/st-beheer/`
- `liefamsterdam.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://liefamsterdam.nl/st-beheer/`
- `limo4u.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://limo4u.nl/st-beheer/`
- `marksmen.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://marksmen.nl/st-beheer/`
- `me-2.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://me-2.nl/st-beheer/`
- `medemblikkerbedrijvengroep.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://medemblikkerbedrijvengroep.nl/st-beheer/`
- `micheldoorn.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://micheldoorn.nl/st-beheer/`
- `mkbkredietnederland.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://mkbkredietnederland.nl/st-beheer/`
- `moneycare.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://moneycare.nl/st-beheer/`
- `nr7finance.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://nr7finance.nl/st-beheer/`
- `nr7groep.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://nr7groep.nl/st-beheer/`
- `nzvschilders.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://nzvschilders.nl/st-beheer/`
- `ondernemersfondsmedemblik.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://ondernemersfondsmedemblik.nl/st-beheer/`
- `pknhoornzwaagblokker.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://pknhoornzwaagblokker.nl/st-beheer/`
- `postkrediet.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://postkrediet.nl/st-beheer/`
- `psmedemblik.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://psmedemblik.nl/st-beheer/`
- `raat.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://raat.nl/st-beheer/`
- `ragbag.eu/st-beheer/`: HTTP `200`, redirects `1`, final `https://ragbag.eu/st-beheer/`
- `rosabian.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://rosabian.nl/st-beheer/`
- `salonannemieke.nl/wp-admin/`: HTTP `404`, redirects `0`, final `http://salonannemieke.nl/wp-admin/`
- `samcity.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://samcity.nl/st-beheer/`
- `senshake.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://senshake.nl/st-beheer/`
- `shop.fullevents.nl/wp-admin/`: HTTP `404`, redirects `0`, final `http://shop.fullevents.nl/wp-admin/`
- `significant.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://significant.nl/st-beheer/`
- `stoommachinemuseum.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://stoommachinemuseum.nl/st-beheer/`
- `studiotempel.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://studiotempel.nl/st-beheer/`
- `taplite.com/st-beheer/`: HTTP `200`, redirects `1`, final `https://taplite.com/st-beheer/`
- `taxiid.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://taxiid.nl/st-beheer/`
- `taxiwebbooker.com/st-beheer/`: HTTP `200`, redirects `1`, final `https://taxiwebbooker.com/st-beheer/`
- `tcwf.nl/wp-admin/`: HTTP `404`, redirects `1`, final `https://tcwf.nl/404/`
- `treviancollectief.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://treviancollectief.nl/st-beheer/`
- `vanduintotdijk.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://vanduintotdijk.nl/st-beheer/`
- `vanlithbouw.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://vanlithbouw.nl/st-beheer/`
- `vanteeffelenexecutive.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://vanteeffelenexecutive.nl/st-beheer/`
- `vdheuveltrade.nl/st-beheer/`: HTTP `200`, redirects `2`, final `https://www.vdheuveltrade.nl/st-beheer/`
- `wlkm.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://wlkm.nl/st-beheer/`
- `woet.eu/wp-admin/`: HTTP `200`, redirects `2`, final `https://woet.eu/home/`
- `yvonnekuipers.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://yvonnekuipers.nl/st-beheer/`
- `zwaanvakschilders.nl/st-beheer/`: HTTP `200`, redirects `1`, final `https://zwaanvakschilders.nl/st-beheer/`

## OK_BLOCKED (66)

- `aica-summit.com/wp-admin/`: HTTP `403`, redirects `1`, final `https://aica-summit.com/wp-admin/`
- `axplain.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://axplain.nl/wp-admin/`
- `becam.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://becam.nl/wp-admin/`
- `bijoost.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://bijoost.nl/wp-admin/`
- `bit4u.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://bit4u.nl/wp-admin/`
- `boltcab.io/wp-admin/`: HTTP `403`, redirects `1`, final `https://boltcab.io/wp-admin/`
- `bouwbrug.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://bouwbrug.nl/wp-admin/`
- `bregbreg.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://bregbreg.nl/wp-admin/`
- `brynx.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://brynx.nl/wp-admin/`
- `buysmankruiden.nl/wp-admin/`: HTTP `403`, redirects `0`, final `http://buysmankruiden.nl/wp-admin/`
- `bvscout.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://bvscout.nl/wp-admin/`
- `chatlicense.com/wp-admin/`: HTTP `403`, redirects `1`, final `https://chatlicense.com/wp-admin/`
- `dataspark.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://dataspark.nl/wp-admin/`
- `deraadreinders.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://deraadreinders.nl/wp-admin/`
- `dycotrade.com/wp-admin/`: HTTP `403`, redirects `1`, final `https://dycotrade.com/wp-admin/`
- `epiphanyhomes.pt/wp-admin/`: HTTP `403`, redirects `1`, final `https://epiphanyhomes.pt/wp-admin/`
- `frisia.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://frisia.nl/wp-admin/`
- `funfestijn.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://funfestijn.nl/wp-admin/`
- `hetwapenvanmedemblik.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://hetwapenvanmedemblik.nl/wp-admin/`
- `hmg.studiotempel.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://hmg.studiotempel.nl/wp-admin/`
- `hoffdakentimmerwerken.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://hoffdakentimmerwerken.nl/wp-admin/`
- `hoogtij.amsterdam/wp-admin/`: HTTP `403`, redirects `1`, final `https://hoogtij.amsterdam/wp-admin/`
- `horecabier.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://horecabier.nl/wp-admin/`
- `jasperfijma.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://jasperfijma.nl/wp-admin/`
- `jerbohaarden.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://jerbohaarden.nl/wp-admin/`
- `karaat.nu/wp-admin/`: HTTP `403`, redirects `1`, final `https://karaat.nu/wp-admin/`
- `kbgmontage.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://kbgmontage.nl/wp-admin/`
- `kmsadvisering.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://kmsadvisering.nl/wp-admin/`
- `knipsalonhoutenbos.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://knipsalonhoutenbos.nl/wp-admin/`
- `koggevaarder.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://koggevaarder.nl/wp-admin/`
- `kooistrafeenstra.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://kooistrafeenstra.nl/wp-admin/`
- `leblancacademy.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://leblancacademy.nl/wp-admin/`
- `liefamsterdam.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://liefamsterdam.nl/wp-admin/`
- `limo4u.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://limo4u.nl/wp-admin/`
- `marksmen.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://marksmen.nl/wp-admin/`
- `me-2.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://me-2.nl/wp-admin/`
- `medemblikkerbedrijvengroep.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://medemblikkerbedrijvengroep.nl/wp-admin/`
- `micheldoorn.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://micheldoorn.nl/wp-admin/`
- `mkbkredietnederland.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://mkbkredietnederland.nl/wp-admin/`
- `moneycare.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://moneycare.nl/wp-admin/`
- `nr7finance.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://nr7finance.nl/wp-admin/`
- `nr7groep.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://nr7groep.nl/wp-admin/`
- `nzvschilders.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://nzvschilders.nl/wp-admin/`
- `ondernemersfondsmedemblik.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://ondernemersfondsmedemblik.nl/wp-admin/`
- `pknhoornzwaagblokker.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://pknhoornzwaagblokker.nl/wp-admin/`
- `postkrediet.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://postkrediet.nl/wp-admin/`
- `psmedemblik.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://psmedemblik.nl/wp-admin/`
- `raat.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://raat.nl/wp-admin/`
- `ragbag.eu/wp-admin/`: HTTP `403`, redirects `1`, final `https://ragbag.eu/wp-admin/`
- `rosabian.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://rosabian.nl/wp-admin/`
- `samcity.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://samcity.nl/wp-admin/`
- `senshake.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://senshake.nl/wp-admin/`
- `significant.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://significant.nl/wp-admin/`
- `stoommachinemuseum.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://stoommachinemuseum.nl/wp-admin/`
- `studiotempel.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://studiotempel.nl/wp-admin/`
- `taplite.com/wp-admin/`: HTTP `403`, redirects `1`, final `https://taplite.com/wp-admin/`
- `taxiid.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://taxiid.nl/wp-admin/`
- `taxiwebbooker.com/wp-admin/`: HTTP `403`, redirects `1`, final `https://taxiwebbooker.com/wp-admin/`
- `taxlex.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://taxlex.nl/wp-admin/`
- `treviancollectief.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://treviancollectief.nl/wp-admin/`
- `vanduintotdijk.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://vanduintotdijk.nl/wp-admin/`
- `vanlithbouw.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://vanlithbouw.nl/wp-admin/`
- `vanteeffelenexecutive.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://vanteeffelenexecutive.nl/wp-admin/`
- `wlkm.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://wlkm.nl/wp-admin/`
- `yvonnekuipers.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://yvonnekuipers.nl/wp-admin/`
- `zwaanvakschilders.nl/wp-admin/`: HTTP `403`, redirects `1`, final `https://zwaanvakschilders.nl/wp-admin/`

## WP_ADMIN_REDIRECT (24)

- `bierfabriek.com/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.bierfabriek.com/`
- `cocorico.nl/wp-admin/`: HTTP `200`, redirects `3`, final `https://www.cocorico.nl/wp-login.php?redirect_to=https%3A%2F%2Fcocorico.nl%2Fwp-admin%2F%3Fdoing_wp_cron%3D1783667881.8396201133728027343750&reauth=1`
- `deslimmechef.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://deslimmechef.nl/wp-login.php?redirect_to=https%3A%2F%2Fdeslimmechef.nl%2Fwp-admin%2F&reauth=1`
- `dianatromp.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://dianatromp.nl/wp-login.php?redirect_to=https%3A%2F%2Fdianatromp.nl%2Fwp-admin%2F&reauth=1`
- `dispatchapi.io/wp-admin/`: HTTP `404`, redirects `2`, final `https://www.dispatchapi.io/wp-login.php?redirect_to=https%3A%2F%2Fdispatchapi.io%2Fwp-admin%2F&reauth=1`
- `femkecycling4kika.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://femkecycling4kika.nl/wp-login.php?redirect_to=https%3A%2F%2Ffemkecycling4kika.nl%2Fwp-admin%2F&reauth=1`
- `finelines.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.finelines.nl/wp-login.php?redirect_to=https%3A%2F%2Ffinelines.nl%2Fwp-admin%2F&reauth=1`
- `forexfordynamics.com/wp-admin/`: HTTP `404`, redirects `2`, final `https://forexfordynamics.com/wp-admin`
- `horecabier.be/wp-admin/`: HTTP `200`, redirects `2`, final `https://horecabier.be/wp-login.php?redirect_to=https%3A%2F%2Fhorecabier.be%2Fwp-admin%2F&reauth=1`
- `horizonflowerfamily.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.horizonflowerfamily.nl/wp-login.php?redirect_to=https%3A%2F%2Fhorizonflowerfamily.nl%2Fwp-admin%2F&reauth=1`
- `maartendegraaf.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.maartendegraaf.nl/wp-login.php?redirect_to=https%3A%2F%2Fmaartendegraaf.nl%2Fwp-admin%2F&reauth=1`
- `mkba.info/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.mkba.info/wp-login.php?redirect_to=https%3A%2F%2Fmkba.info%2Fwp-admin%2F&reauth=1`
- `nct.studiotempel.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://nct.studiotempel.nl/wp-login.php?redirect_to=https%3A%2F%2Fnct.studiotempel.nl%2Fwp-admin%2F&reauth=1`
- `ofmedemblik.nl/wp-admin/`: HTTP `200`, redirects `3`, final `https://www.ofmedemblik.nl/wp-login.php?redirect_to=https%3A%2F%2Fofmedemblik.nl%2Fwp-admin%2F%3Fdoing_wp_cron%3D1783667873.8143908977508544921875&reauth=1`
- `overinzicht.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://overinzicht.nl/wp-login.php?redirect_to=https%3A%2F%2Foverinzicht.nl%2Fwp-admin%2F&reauth=1`
- `pgenkhuizen.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.pgenkhuizen.nl/wp-login.php?redirect_to=https%3A%2F%2Fpgenkhuizen.nl%2Fwp-admin%2F&reauth=1`
- `quicksilverbar.nl/wp-admin/`: HTTP `200`, redirects `5`, final `https://www.quicksilverbar.nl/inloggen/`
- `sinusjevi.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://sinusjevi.nl/wp-login.php?redirect_to=https%3A%2F%2Fsinusjevi.nl%2Fwp-admin%2F&reauth=1`
- `thefamilyhaarverzorging.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://thefamilyhaarverzorging.nl/wp-login.php?redirect_to=https%3A%2F%2Fthefamilyhaarverzorging.nl%2Fwp-admin%2F&reauth=1`
- `trend-center.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.trend-center.nl/wp-login.php?redirect_to=https%3A%2F%2Fwww.trend-center.nl%2Fwp-admin%2F&reauth=1`
- `troostonline.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://troostonline.nl/wp-login.php?redirect_to=https%3A%2F%2Ftroostonline.nl%2Fwp-admin%2F&reauth=1`
- `vdheuveltrade.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.vdheuveltrade.nl/wp-login.php?redirect_to=https%3A%2F%2Fvdheuveltrade.nl%2Fwp-admin%2F&reauth=1`
- `vlaartechniek.nl/wp-admin/`: HTTP `200`, redirects `2`, final `https://www.vlaartechniek.nl/wp-login.php?redirect_to=https%3A%2F%2Fvlaartechniek.nl%2Fwp-admin%2F&reauth=1`
- `wardenaar.com/wp-admin/`: HTTP `200`, redirects `2`, final `https://wardenaar.com/wp-login.php?redirect_to=https%3A%2F%2Fwardenaar.com%2Fwp-admin%2F&reauth=1`

