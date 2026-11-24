# Changelog

## 4.9.2 — 2026-11-24

- Koppelt een website automatisch met Tempel Hub zodra het domein daar geregistreerd staat en de domeinchallenge slaagt.
- Verwijdert het tijdelijke centrale koppelvenster: geregistreerde domeinen blijven veilig automatisch koppelbaar.
- Probeert een mislukte koppeling na 15 minuten opnieuw en direct opnieuw na iedere pluginupdate.

## 4.9.1 — 2026-11-23

- Verwijdert lokale instellingen voor AI aan/uit, API-sleutel, model en gebruikslimieten; deze komen uitsluitend uit Tempel Hub.
- Alleen de toegestane WordPress-rollen blijven bewust lokaal instelbaar.
- Voegt een tijdelijke, centraal schakelbare automatische koppeling toe voor websites die al in het CRM geregistreerd staan.
- De automatische koppeling gebruikt een willekeurige domeinchallenge; er staat geen gedeeld geheim in de pluginrelease.

## 4.9.0 — 2026-11-23

- Eerste koppeling met Tempel Hub in het CRM voor centraal beheer van AI-toegang, Templeton-regels en gebruikslimieten.
- Websites koppelen via een eenmalige code die alleen werkt voor het geregistreerde domein.
- Alle AI-verzoeken worden per site ondertekend met HMAC, tijdstempel en een unieke nonce; hergebruik wordt geblokkeerd.
- De centrale OpenAI-sleutel wordt nooit naar WordPress verstuurd; lokaal ingestelde sleutels blijven tijdelijk als fallback werken.
- De AI-instellingen tonen de Hub-status en bieden koppelen en ontkoppelen zonder handmatige configuratiebestanden.

## 4.8.0 — 2026-11-23

- Nieuwe beheerpagina `Optimalisatie` om bestaande pagina's adviserend te scannen.
- De analyse gebruikt gewone pagina-inhoud, geselecteerde ACF/Flexible Content, bestaande SEO-data en het bedrijfs-DNA.
- Resultaten bevatten sterke punten, knelpunten, concrete acties, herschrijfvoorstellen en SEO-advies; er wordt niets automatisch gepubliceerd of overschreven.
- De analyse draait als achtergrondtaak met live voortgang en veilige tekstweergave.

## 4.7.7 — 2026-11-23

- Het DNA-scherm heeft nu één gedocumenteerd afstandssysteem: 24 px tussen secties, 16 px tussen titel en inhoud, 12 px tussen items, 8 px bij toelichtingen en 4 px tussen statusregels.
- De ruimte onder `Bedrijfs-DNA` is vergroot naar 16 px.

## 4.7.6 — 2026-11-23

- De DNA-pagina gebruikt een consequent verticaal ritme van 24 px tussen hoofdblokken en 12 px tussen hoofdstukken.
- Gesloten generator- en hoofdstukregels hebben compactere binnenruimte.

## 4.7.5 — 2026-11-23

- De uitgebreide DNA-generator is standaard ingeklapt en opent alleen wanneer een nieuw voorstel nodig is.
- Grote keuze- en bronkaarten zijn vervangen door compacte bediening.
- Slechts één DNA-hoofdstuk blijft tegelijk open en acties per antwoord zijn visueel terughoudender.

## 4.7.4 — 2026-11-23

- Elk DNA-antwoord kan afzonderlijk als menselijk gecontroleerd worden gemarkeerd.
- Hoofdstukken tonen naast de invulstatus de verdeling tussen gecontroleerde en uitsluitend door AI ingevulde antwoorden.
- AI-overname en latere tekstwijzigingen trekken een eerdere controlestatus automatisch in.

## 4.7.3 — 2026-11-23

- De zes DNA-hoofdstukken zijn compacte accordeons met een subtiele invulstatus.
- DNA-voorstellen worden per hoofdstuk gegroepeerd en pas na openen getoond.
- De bedrijfs-DNA-kop en hoofdstuknavigatie gebruiken minder nadrukkelijke AI-vormgeving.

## 4.7.2 — 2026-11-23

- Voegt aan de Templeton Engine-pagina een dynamisch overzicht toe van alle gebruikte bronnen en kaders.
- Toont de dekking van DNA-antwoorden, website-inhoud, ACF/Flexible Content en SEO-data.

## 4.7.1 — 2026-11-23

- Toont op alle Tempel-pagina’s een vaste opslagmelding rechtsonder.
- Ondersteunt live opslaan, klassieke WordPress-opslagmeldingen en dynamisch toegevoegde succesmeldingen.

## 4.7.0 — 2026-11-23

- Herbouwt de DNA-pagina rond de zes blokken uit de Studio Tempel-vragenlijst voor merkstrategie.
- Koppelt de geselecteerde contentinventarisatie zichtbaar aan het DNA-onderzoek.
- Introduceert een veilige voorstelworkflow voor bestaande klanten: per antwoord overnemen, aanvullen of overslaan, of alleen lege velden vullen.
- Voegt de vaste SEO-paginawerkwijze toe aan Templeton Engine 2.6.

## 4.6.0 — 2026-11-23

- Voegt een aparte beheerpagina voor Templeton Engine 2.5 toe met vaste Studio Tempel-richtlijnen.
- Past het redactionele kader automatisch toe op alle schrijftaken, DNA-analyses en gerichte DNA-updates.
- Beheert schrijfstijl, feiten en claims, SEO, structuur, verboden formuleringen, kwaliteitscontrole en voorbeelden met live opslaan.

## 4.5.0 — 2026-11-23

- Voegt Contentinventaris toe voor het beheren van publieke contenttypes, ACF-velden en SEO-bronnen die Templeton mag gebruiken.
- DNA-voorstellen kunnen bestaande gegevens aanvullen of volledig overschrijven.
- Ieder DNA-onderdeel kan afzonderlijk opnieuw worden onderzocht met een toelichting op wat er gewijzigd is.

## 4.4.0 — 2026-11-23

- Slaat alle echte Tempel-instellingen automatisch op zonder losse opslaanknoppen.
- Toont subtiele live status bij opslaan, succes en fouten.
- Houdt bewuste acties zoals verzenden, opschonen, back-uppen, koppelen en verhuizen buiten automatisch opslaan.

## 4.3.4 — 2026-11-23

- Maakt de tagskolom in het bedrijfs-DNA ruimer zodat langere tags beter leesbaar blijven.

## 4.3.3 — 2026-11-23

- Hernoemt Steekwoorden naar Tags en plaatst titel en beschrijving boven een rustige tweekolomsindeling met tags links en tekst rechts.

## 4.3.2 — 2026-11-23

- Toont tijdens diep DNA-onderzoek een live onderzoekslog met de afzonderlijke scanonderdelen.
- Presenteert gebruikte bronnen als duidelijke bronkaarten en maakt steekwoorden bewerkbaar als losse tags.

## 4.3.1 — 2026-11-23

- Het bronnenoverzicht vermeldt naast externe bronnen ook het websiteprofiel, aantallen onderzochte publieke contenttypes, ACF/Flexible Content en gebruikte categorieën en tags.
- Lokale broncategorieën tonen alleen transparante samenvattingen en aantallen; volledige veldinhoud en gevoelige gegevens worden niet in het overzicht herhaald.
- Externe markt- en trendbronnen blijven afzonderlijk herkenbaar en klikbaar.

## 4.3.0 — 2026-11-23

- Ieder DNA-onderdeel heeft een eigen compacte regel met bewerkbare steekwoorden.
- Templeton Engine genereert per onderdeel vier tot tien concrete, kommagescheiden tags en vermijdt algemene nietszeggende termen.
- De tags worden genormaliseerd, ontdubbeld en samen met de uitgebreide DNA-tekst aan iedere volgende AI-opdracht meegegeven.

## 4.2.1 — 2026-11-23

- Diep DNA-onderzoek draait als achtergrondtaak met live voortgangsfases, verstreken tijd en duidelijke foutstatus.
- Tijdens het genereren wordt Templeton Engine 2.4 als actieve engine vermeld.
- DNA-tekstvelden groeien automatisch mee tijdens typen en na het invullen van een voorstel.

## 4.2.0 — 2026-11-23

- DNA-onderzoek uitgebreid naar openbare custom post types, ACF-tekst, Flexible Content, categorieën en tags.
- Doelgroepanalyse vormt voortaan onderbouwde hypotheses uit aanbod, taal, problemen, voordelen, cases en call-to-actions wanneer de doelgroep niet expliciet wordt genoemd.
- Gericht webonderzoek gebruikt afgeleide thema’s, zoekbegrippen, doelgroepbehoeften en regio met een uitgebreidere zoekcontext.
- Het bedrijfs-DNA bevat voortaan ook een blijvend veld voor kernonderwerpen en relevante trends.

## 4.1.1 — 2026-11-23

- Back-ups, Website verhuizen en de statuskaarten volgen nu overal het lichte Tempel-thema.
- Oude hardgecodeerde donkere oppervlakken worden in licht thema centraal overschreven via de gedeelde designtokens.

## 4.1.0 — 2026-11-23

- DNA is een afzonderlijke pagina voor beheerders en editors; API-sleutel, rollen en limieten blijven uitsluitend onder Beheer beschikbaar.
- De eerdere technische Instellingen-sectie heet voortaan Beheer.
- Een gecontroleerde DNA-generator analyseert lokale openbare website-inhoud, een optionele redacteursinstructie en desgewenst actuele markttrends via OpenAI-web search.
- Gegenereerd DNA wordt eerst als bewerkbaar voorstel ingevuld en nooit automatisch opgeslagen; gebruikte trendbronnen verschijnen als klikbare links.
- Een licht Tempel-thema toegevoegd en eenmalig standaard ingeschakeld.

## 4.0.3 — 2026-11-23

- Verwijdert de waarschuwing “Deel geen gevoelige gegevens” van AI & DNA voor een rustigere interface.

## 4.0.2 — 2026-11-23

- Herstelt de typografische hiërarchie van AI & DNA: veldlabels en toelichtingen staan weer los van elkaar met consistente ruimte, kleur en regelhoogte.

## 4.0.1 — 2026-11-23

- Herstelt de ontbrekende Tempel-stylesheets op AI & DNA en andere verborgen detailpagina's met een afwijkend WordPress-scherm-ID.
- De centrale schermherkenning controleert voortaan zowel het scherm-ID als de geregistreerde Tempel-paginaslug.

## 4.0.0 — 2026-11-23

- De plug-in en het beheermenu heten voortaan kortweg Tempel.
- Tempel AI heeft een centraal bedrijfs-DNA voor bedrijf, doelgroep, aanbod, positionering, tone of voice, woordkeuze en een representatieve voorbeeldtekst.
- Iedere AI-opdracht gebruikt dit DNA automatisch als veilige inhoudelijke en stilistische context.
- De nieuwe actie Websiteonderdeel schrijven vormt de eerste basis voor latere ACF-blokgeneratie.

## 3.3.1 — 2026-11-23

- Alle regels in het compacte publicatieblok staan nu op één vaste linkerlijn.
- Tekst en bediening zijn verticaal uitgelijnd met de bijbehorende WordPress- en Yoast-iconen.

## 3.3.0 — 2026-11-23

- Nieuw standaard ingeschakeld compact publicatieblok voor de klassieke WordPress-editor.
- De zichtbare labels Status, Zichtbaarheid en Gepubliceerd op worden rustig verborgen; de bijbehorende iconen, actuele waarden, bewerklinks en acties blijven intact.
- De labels blijven beschikbaar voor schermlezers en de functie kan onder Algemeen → Uiterlijk worden uitgeschakeld.

## 3.2.1 — 2026-11-23

- WYSIWYG-inhoud wordt nu met zijn veilige HTML-structuur naar OpenAI gestuurd, zodat koppen, paragrafen, lijsten, links, strong/em en CSS-klassen behouden kunnen blijven.
- Het voorbeeldvenster rendert de opmaak en blijft vóór het terugplaatsen bewerkbaar.
- Alle teruggestuurde HTML wordt server-side met `wp_kses_post()` gefilterd; scripts, event-attributen en andere onveilige markup worden niet toegelaten.
- Gewone ACF-tekstvelden blijven bewust uitsluitend platte tekst gebruiken.
- Wanneer een redacteur de overgenomen brontekst handmatig wijzigt, schakelt die ene opdracht veilig terug naar platte tekst om verouderde verborgen HTML te voorkomen.

## 3.2.0 — 2026-11-23

- De AI-schrijfassistent is veranderd van een los gereedschap in een veldgebonden redactiehulp.
- Ondersteunde ACF-velden krijgen direct naast hun label een compacte AI-knop.
- De klassieke editor krijgt een AI-knop naast de bestaande mediaknoppen.
- Een veldknop opent de module direct met geselecteerde tekst, of anders de volledige veldinhoud, en onthoudt exact waar het resultaat terug moet komen.
- Nieuw toegevoegde Flexible Content-layouts en repeaterregels krijgen hun AI-knoppen automatisch zonder de pagina te herladen.
- De losse knop rechtsonder blijft alleen als reserve zichtbaar wanneer er geen ondersteund veld of klassieke editor aanwezig is.

## 3.1.1 — 2026-11-23

- De AI-schrijfassistent herkent nu ACF-tekstvelden, textarea's en iedere afzonderlijke ACF-WYSIWYG.
- Velden binnen Flexible Content-layouts, repeaters en geneste combinaties worden via hun actieve DOM-veld gevolgd.
- De assistent toont de layout- en veldnaam en zet de gecontroleerde tekst uitsluitend terug in dat gekozen veld.
- ACF ontvangt na het terugplaatsen de gebruikelijke input-, change- en TinyMCE-events, zodat gewijzigde waarden correct worden opgeslagen.

## 3.1.0 — 2026-11-23

- Een optionele AI-schrijfassistent toegevoegd aan Gutenberg en de klassieke editor.
- Redacteuren kunnen tekst genereren, herschrijven, verbeteren, verkorten, uitbreiden en vertalen en zien altijd eerst een voorbeeld.
- De persoonlijke OpenAI API-sleutel wordt versleuteld opgeslagen en blijft volledig server-side.
- Rollencontrole, nonces, berichtrechten, maximale tekstlengte en dag-, maand- en tokenlimieten toegevoegd.
- AI-verbruik wordt lokaal als aantallen en tokens bijgehouden; ingevoerde teksten en antwoorden worden niet gelogd.

## 3.0.5 — 2026-11-12

- De losse download- en importblokken staan nu direct onder de SFTP-verhuizing. De uitklapbare buitenbox en de dubbele titel “Handmatig met een verhuispakket” zijn verwijderd.

## 3.0.4 — 2026-11-12

- Downloadlinks voor verhuispakketten worden nu als gewone URL opgebouwd. De JSON-respons bevat daardoor echte `&`-scheidingstekens in plaats van HTML-code `&amp;`, zodat WordPress de token en nonce correct ontvangt.

## 3.0.3 — 2026-11-11

- Herstelt ZIP-fout 19 bij handmatige exports. Het pad van het pakket werd tijdens de bestandsronde overschreven met het pad van het laatst gevonden websitebestand, waardoor de eindcontrole geen ZIP maar een gewoon bronbestand probeerde te openen.
- Een regressietest bewaakt dat bronbestandspaden het pakketpad niet opnieuw kunnen overschrijven.

## 3.0.2 — 2026-11-11

- De pakketknop leest de serverroute nu expliciet uit het HTML-`action`-attribuut. Het verborgen WordPress-actieveld kan daardoor niet langer per ongeluk als URL worden gebruikt en een onmiddellijke HTTP 404 veroorzaken.

## 3.0.1 — 2026-11-11

- Het opbouwen van een verhuispakket gebruikt voortaan de ingelogde `admin-post.php`-route in plaats van `admin-ajax.php`. Dit voorkomt de HTTP 404 die sommige hostings of beveiligingslagen bij langdurige AJAX-aanvragen teruggeven.
- De route blijft uitsluitend beschikbaar voor beheerders en vereist een actiegebonden WordPress-nonce.

## 3.0.0 — handmatige pakketdownload

- E-mailverificatie bij backendlogin blijft voor nieuwe installaties en upgrades voorlopig opt-in; bestaande handmatige keuzes worden niet gewijzigd.
- De SVG-sanitizer is bijgewerkt van de kwetsbare 0.16.0 naar beveiligingsrelease 1.1.1 en weigert voortaan bestanden wanneer sanitisatie niet aantoonbaar slaagt.
- De releasebouwer controleert voortaan expliciet of Composer-autoload, SVG-sanitizer, updater en SFTP-runtime werkelijk in de uiteindelijke zip zitten.
- De handmatige export blijft op de verhuispagina en toont live de fasen database, bestanden, afronden en controleren.
- Een downloadknop verschijnt pas nadat het sitepakket succesvol als ZIP is geopend en gecontroleerd.
- Voorbereide pakketten krijgen een persoonlijke, eenmalige downloadlink en worden uiterlijk na één uur automatisch verwijderd.
- ZIP-controlefouten vermelden nu de technische foutcode en controleren eerst of het archief werkelijk is opgeslagen.
- Het overbodige blok ‘Veelgebruikte acties’ is van het overzicht verwijderd.
- Achtergebleven, exact herkenbare Tempel-verhuisbestanden kunnen vanuit ‘Grote bestanden’ veilig worden geselecteerd en verwijderd; overige bestanden in de WordPress-hoofdmap blijven beschermd.
- Widgettoggles staan op één vaste rechterrand, formulierselectie gebruikt de volledige breedte en posttypevelden verschijnen alleen wanneer posttypeconversies zijn ingeschakeld.

Alle noemenswaardige wijzigingen aan Tempel Settings. De geschiedenis tot en met 2.7.21 is gereconstrueerd uit releasebestanden, de bestaande WordPress-changelog en Git-commits.

## [3.0.0] - 2026-10-30

- De beheerinterface teruggebracht tot de hoofdbestemmingen Overzicht, Instellingen, Gereedschap en Logboek.
- Een rustig Overzicht toegevoegd met beveiligings-, back-up- en monitorstatus en veelgebruikte acties.
- Een centrale stijlgids, vaste design tokens en herbruikbare componenten voor kaarten, statussen, velden, knoppen en voortgang toegevoegd.
- Alle Tempel Settings-schermen aangesloten op dezelfde typografie, ruimte- en kleurvariabelen.
- Live back-upstatus toegevoegd; fase, melding en uploadpercentage worden automatisch bijgewerkt zonder paginaverversing.
- De bestaande live voortgang van Website verhuizen visueel aangesloten op hetzelfde componentsysteem.
- De adminpaginaregistratie centraal hergebruikt, zodat navigatieweergave niet opnieuw adminhooks registreert.
- Toegang tot bestaande instellingen gecentraliseerd in `SettingsRepository`, met volledige compatibiliteit voor de bestaande option-namen.
- Een EditorConfig, PHPCS-configuratie en admin-UI-stijlgids toegevoegd voor consistente verdere ontwikkeling.
- E-mailverificatie voor backend-logins blijft optioneel en wordt niet automatisch ingeschakeld.
- Een noodschakelaar via `TEMPEL_SETTINGS_DISABLE_EMAIL_2FA` toegevoegd voor herstel wanneer WordPress-mail niet aankomt.
- WP-Members-logins op de website expliciet uitgesloten van de backend-e-mailverificatie.
- E-mailverificatie beperkt tot beheerlogins via `wp-admin`, de beheerbestemming van `wp-login.php` en `st-beheer`; frontendbestemmingen worden overgeslagen.
- Alle Tempel-beheerpagina's automatisch aan één donker canvas gekoppeld, zodat nieuwe gereedschappen niet meer naar een lichte WordPress-achtergrond kunnen terugvallen.
- Instellingsrijen gestandaardiseerd met labels links en schakelaars en overige bediening rechts.
- De Mail-WYSIWYG over de volledige veldbreedte geplaatst met label en toelichting erboven; de vertrouwde witte editorstijl blijft behouden.
- Verborgen detailpagina's blijven voortaan bij WordPress geregistreerd; directe links zoals Widgets geven daardoor geen onterechte toestemmingsfout meer.
- Info samengevoegd met Status & logboek; de oude Info-URL stuurt voortaan door naar de gecombineerde pagina.
- Handmatige verhuispakketten ondersteunen voortaan databases met foreign keys; tijdens de atomaire import worden relaties veilig naar tijdelijke tabellen omgezet en daarna hersteld.
- Het SFTP-formulier compacter ingedeeld met host en poort, gebruikersnaam en wachtwoord naast elkaar en de servermap als laatste veld.
- De servermapbrowser opent voortaan als popup en vult de gekozen WordPress-map direct in; de extra getypte bevestiging `VERHUIZEN` is vervallen.
- De verhuispagina vereenvoudigd door de grote introductie en dubbele back-upstatus te verwijderen en de handmatige stappen onder elkaar te plaatsen.
- De overbodige bron- en doelstaplabels verwijderd en het verschil verduidelijkt tussen de PHP-browseruploadlimiet en de pakketlimiet van 2 GiB uitgepakt.
- Dropbox-back-ups en handmatige exports gebruiken voortaan hetzelfde volledige Tempel-sitepakket met volledige WordPress-root, leesbare SQL-dump en veilige migratiedata.
- Nieuwe Tempel-sitepakketten kunnen rechtstreeks vanuit Dropbox op de verhuispagina worden gekozen, zonder de browseruploadlimiet.
- De pakketlimiet verhoogd naar 20 GiB uitgepakt voor grotere websites.
- Vóór pakketimport wordt vrije privéschijfruimte gecontroleerd en tijdelijke Dropbox-downloads krijgen expliciet alleen eigenaarstoegang.
- Beheerders krijgen een niet te negeren waarschuwing wanneer PHP de kritieke RCE-fixes van oktober 2026 nog mist.

## [2.20.1] - 2026-10-30

- De Dropbox-pagina laat nu duidelijk zien wanneer de App secret is opgeslagen, zonder de geheime waarde terug in het formulier te plaatsen.
- Een oude melding over ontbrekende appgegevens verdwijnt zodra App key en App secret wel aanwezig zijn.
- De verbindingsstatus maakt onderscheid tussen ontbrekende appgegevens en een account dat alleen nog gekoppeld moet worden.

## [2.20.0] - 2026-10-30

- De losse Stager-functionaliteit geïntegreerd als de pagina Website verhuizen in Tempel Settings.
- Rechtstreeks publiceren via SFTP, een servermapbrowser en handmatige verhuispakketten behouden.
- De recente Dropbox-back-upstatus zichtbaar gemaakt vóór een verhuizing.
- Expliciete back-upbevestiging en het woord VERHUIZEN blijven verplicht voor destructieve acties.
- De losse Stager-plugin wordt bij overlap veilig gedeactiveerd.
- Tijdelijke Dropbox-storingen krijgen maximaal vijf automatische herstelpogingen met oplopende wachttijd.
- Vrije tijdelijke schijfruimte wordt vóór het samenstellen van een back-up gecontroleerd.
- Lokale ZIP-bestanden en de uiteindelijke bestandsgrootte in Dropbox worden gecontroleerd.
- Achtergebleven tijdelijke back-upmappen worden automatisch opgeruimd.
- Een fout bij het verwijderen van oude Dropbox-bestanden verandert een geslaagde upload niet meer in een mislukte back-up.

## [2.19.0] - 2026-10-16

- Grote Dropbox-back-ups worden in korte, hervatbare achtergrondstappen verstuurd.
- Uploadpositie en voortgang worden bewaard, zodat een volgende taak verdergaat waar de vorige stopte.
- Vastgelopen oude back-uptaken worden herkend en met een duidelijke foutstatus vrijgegeven.
- Tijdens een actieve back-up kan niet per ongeluk een tweede back-up worden gestart.

## [2.18.1] - 2026-10-16

- Veilige voorbeelden en downloads toegevoegd aan het overzicht met grote bestanden.
- Meervoudige selectie en definitief verwijderen toegevoegd voor bestanden in uploads- en cachemappen.
- WordPress-, thema- en pluginbestanden blijven vergrendeld tegen verwijdering vanuit dit overzicht.

## [2.18.0] - 2026-10-16

- Handmatige scan van de volledige WordPress-installatie toegevoegd aan Status & logboek.
- Toont de 50 grootste bestanden met grootte, relatief pad en wijzigingsdatum.
- De scan bewaart alleen het laatste resultaat, volgt geen symlinks en stopt veilig na twintig seconden.

## [2.17.6] - 2026-10-16

- Automatische heractivering toegevoegd voor de blokkade van plugininstallaties en nieuwe beheerders.
- De blokkade wordt één uur na handmatig uitschakelen opnieuw actief; deze beveiliging staat standaard aan.
- Een WordPress-crontaak en controle bij paginalaad zorgen samen dat de blokkade ook na gemiste cronuitvoering wordt hersteld.

## [2.17.5] - 2026-10-16

- Back-ups-pagina een zelfstandig donker canvas gegeven zodat witte labels altijd voldoende contrast hebben.
- Planningvelden responsief gemaakt en overlopende minimumveldwidthtes gecorrigeerd.
- Tekst-, e-mail-, wachtwoord-, nummer-, tijd- en keuzevelden op deze pagina uniform vormgegeven.

## [2.17.4] - 2026-10-16

- Back-ups-tab toegevoegd aan de lijst met Tempel Settings-adminschermen waarvoor de volledige vormgeving wordt geladen.
- Versienummers van de instellingenstyles op bestandstijd gebaseerd, zodat browsers aangepaste CSS direct verversen.

## [2.17.3] - 2026-10-16

- Visuele Dropbox-mapbrowser toegevoegd met mapnavigatie en een knop om de huidige map te kiezen.
- Handmatige invoer van het Dropbox-pad als reserve behouden.
- Tijdelijk toegangstoken hergebruikt om bladeren door mappen sneller te maken.

## [2.17.2] - 2026-10-16

- Back-ups-pagina opnieuw vormgegeven met een compact statusoverzicht en duidelijke hoofdactie.
- Planning, e-mailmeldingen en Dropbox-koppeling in afzonderlijke onderdelen geplaatst.
- Technische Dropbox-instellingen na het koppelen standaard ingeklapt.

## [2.17.1] - 2026-10-16

- Instelbaar lokaal tijdstip voor geplande Dropbox-back-ups toegevoegd.
- Foutmeldingen per e-mail toegevoegd voor iedere mislukte back-up.
- Optionele succesmail met bestandsnaam en grootte toegevoegd.

## [2.17.0] - 2026-10-16

- Optionele volledige WordPress-back-ups naar Dropbox toegevoegd.
- Wekelijkse of maandelijkse planning, handmatige uitvoering en instelbare retentie toegevoegd.
- Dropbox OAuth met refresh-token en upload in delen voor grote back-upbestanden toegevoegd.
- Back-upstatus en eerstvolgende uitvoering zichtbaar gemaakt in een eigen Back-ups-tab.

## [2.16.0] - 2026-09-17

- Optionele bescherming voor concrete gevoelige bestandsnamen in een eigen .htaccess-blok, standaard uit.
- Databaseback-up, bestandsvergrendeling, homepage/login-loopbackcontrole en herstelknop toegevoegd.
- Geen algemene YAML-blokkade; multisite en ontbrekende .htaccess worden veilig overgeslagen.
- Nginx-blokkades en functionele WooCommerce/formuliertests blijven op de doelhosting nodig.

## [2.15.2] - 2026-09-15

- Sluit de zichtbare melding en achtergrond direct wanneer de actieknop wordt aangeklikt, zodat een geopende widget niet achter de popup terechtkomt.
- Toont na een actieklik niet direct de volgende melding uit dezelfde positiewachtrij.

## [2.15.1] - 2026-09-15

- Registreert de Polylang-filter direct bij het laden van Tempel Settings, voordat Polylang zijn lijst met vertaalbare berichttypen opbouwt.
- Hierdoor wordt de taalkeuze daadwerkelijk zichtbaar bij Meldingen wanneer de module actief is.

## [2.15.0] - 2026-09-15

- Geeft de actieknop ook de generieke klasse `.button`, zodat bestaande themastijlen kunnen worden hergebruikt.
- Centreert de actieknop automatisch wanneer het laatste element van de WYSIWYG-inhoud gecentreerd is.
- Voegt een eenvoudige voorbeeldstylesheet met CSS-variabelen toe voor aanpassingen per website.

## [2.14.1] - 2026-09-15

- Vereenvoudigt het meldingsformulier tot twee tabs: **Inhoud** en **Instellingen**.
- Weergave, planning, locatie en herhaling staan voortaan samen onder Instellingen.

## [2.14.0] - 2026-09-15

- Maakt het berichttype **Meldingen** automatisch vertaalbaar wanneer Polylang actief is.
- De bestaande frontendquery respecteert daardoor de huidige Polylang-taal.

## [2.13.5] - 2026-09-15

- Verwijdert de overbodige toelichting onder de knop **Frequentie resetten**.

## [2.13.4] - 2026-09-15

- Gebruikt bij start- en einddatum alleen uren en minuten; seconden worden niet meer weergegeven of teruggegeven.

## [2.13.3] - 2026-09-15

- Verwijdert de zichtbare teksten **Yes** en **No** uit alle ACF-schakelaars van Meldingen.

## [2.13.2] - 2026-09-15

- Toont **Donkere achtergrond tonen** alleen wanneer **Midden als popup** als positie is gekozen.

## [2.13.1] - 2026-09-15

- Toont de frequentie als aparte kolom in het Meldingen-overzicht: Iedere keer, Eenmalig of Iedere X dagen.

## [2.13.0] - 2026-09-15

- Voegt in het Meldingen-overzicht een directe aan/uit-schakelaar toe, vergelijkbaar met het formulieroverzicht van Gravity Forms.
- Toont de ingestelde positie als aparte kolom: Popup, Boven of Onder.

## [2.12.1] - 2026-09-15

- Geeft per positie paginaspecifieke meldingen voorrang op algemene meldingen; binnen gelijke groepen blijft de sleepvolgorde gelden.
- Plaatst meldingen zonder sluitknop als laatste in hun positiewachtrij, zodat zij eerdere meldingen niet blokkeren.

## [2.12.0] - 2026-09-15

- Staat tegelijk maximaal één middenpopup, één bovenbalk en één onderbalk toe.
- Meldingen op dezelfde positie worden na sluiten als wachtrij afgehandeld volgens de volgorde in het beheeroverzicht.
- Een balk steelt niet langer automatisch de toetsenbordfocus; een middenpopup focust wel zijn sluitknop.

## [2.11.1] - 2026-09-15

- Rendert alle servermatig toepasselijke meldingen, zodat de browser de volgende kan tonen wanneer een hoger geplaatste melding al via cookie is afgehandeld.
- Slaat de frequentiecookie ook op wanneer een bezoeker de actieknop gebruikt.
- Maakt de donkere achtergrond klikbaar wanneer een sluitknop aanwezig is en verbetert de foutafhandeling van sleepbaar sorteren.

## [2.11.0] - 2026-09-15

- Voegt voor boven- en onderbalken de optie **Compacte balk** toe.
- De compacte weergave zet tekst, actieknop en sluitknop met minder padding naast elkaar en laat ze op kleine schermen omslaan.

## [2.10.2] - 2026-09-15

- Begrens afbeeldingen in meldingen standaard tot 100% van de beschikbare inhoudsbreedte.
- Geeft een melding die hoger is dan het scherm een interne verticale scrollbar, met 20 pixels ruimte boven en onder en 12 pixels op mobiel.

## [2.10.1] - 2026-09-15

- Houdt de schakelaar **Donkere achtergrond tonen** altijd zichtbaar onder **Weergave**; de achtergrond wordt alleen bij een middenpopup toegepast.

## [2.10.0] - 2026-09-15

- Voegt onder **Herhaling** een knop toe waarmee de cookieversie van een melding wordt gereset.
- Een eenmalige melding kan daarna opnieuw verschijnen; bij een dagentermijn begint de termijn opnieuw nadat de melding opnieuw is getoond of gesloten.

## [2.9.9] - 2026-09-15

- Vervangt de pseudo-elementachtergrond door een zelfstandig HTML-element direct onder `body`, zodat thema-CSS en stacking contexts de donkere laag niet meer achter de pagina plaatsen.

## [2.9.8] - 2026-09-15

- Maakt de lokale ACF-veldgroep voor Meldingen `seamless`, zonder standaard metabox.
- Deelt de instellingen op in de tabs **Inhoud**, **Weergave**, **Planning**, **Locatie** en **Herhaling**.
- Plaatst de sluitknopoptie onder de donkere achtergrond en zet start- en einddatum naast elkaar.

## [2.9.7] - 2026-09-15

- Herstelt de donkere popupachtergrond: de laag wordt nu op het volledige browservenster geplaatst, direct onder de melding.

## [2.9.6] - 2026-09-15

- Vervangt het losse prioriteitsveld door een sleepbare volgorde in het overzicht **Meldingen**.
- De bovenste toepasselijke melding in het overzicht krijgt voorrang op de website.

## [2.9.5] - 2026-09-15

- Verwijdert de bezoekersselectie uit Meldingen; actieve meldingen gelden voor zowel ingelogde als uitgelogde bezoekers.

## [2.9.4] - 2026-09-15

- Verwijdert het veld **Eigen CSS-klasse** en de bijbehorende frontendverwerking uit Meldingen.

## [2.9.3] - 2026-09-15

- Middenpopups krijgen altijd een sluitknop, zodat bezoekers nooit in een niet-sluitbare popup vastzitten.
- De instelling voor de sluitknop wordt alleen bij boven- en onderbalken getoond.

## [2.9.2] - 2026-09-15

- Verwijdert de overbodige instructietekst onder het ACF-linkveld van een melding.

## [2.9.1] - 2026-09-15

- Herstelt het ontbrekende beheermenu **Meldingen** wanneer Tempel Settings zelf tijdens de WordPress `init`-actie wordt geladen.
- Registreert de lokale ACF-velden ook direct wanneer ACF zijn eigen initialisatie al heeft voltooid.

## [2.9.0] - 2026-09-15

- Optionele module **Meldingen** toegevoegd, standaard uitgeschakeld via Tempel Settings → Algemeen.
- Meldingen worden beheerd als eigen berichttype met lokaal geregistreerde ACF-velden.
- Ondersteuning toegevoegd voor WYSIWYG-inhoud en één ACF-linkveld met knoptekst, URL en doelvenster.
- Posities toegevoegd voor middenpopup, bovenbalk en onderbalk, met eenvoudige overschrijfbare basis-CSS.
- Planning, URL-opname en -uitsluiting, doelgroepen, vertraging, mobiele zichtbaarheid, prioriteit en cookiegestuurde frequentie toegevoegd.
- Maximaal één toepasselijke melding wordt per pagina getoond.

## [2.8.6] - 2026-09-11

- Onder **Algemeen → Uiterlijk** een instelling toegevoegd om de sluitknop van de Complianz-cookiebanner tot 20 × 20 pixels te begrenzen.
- De Complianz-correctie staat standaard aan op nieuwe en bestaande installaties.
- Een eenmalige migratiemarkering zorgt dat een latere handmatige uitschakeling behouden blijft.

## [2.8.5] - 2026-09-02

- De volledige `vendor`-map met de GitHub updater-library wordt voortaan opgenomen in het releasepakket.
- De updater controleert defensief of `PucFactory` beschikbaar is; bij een onvolledig pakket blijft de rest van Tempel Settings werken.
- Een regressietest toegevoegd die de updater bewust zonder `vendor`-map laadt.

## [2.8.4] - 2026-09-02

- Tempel Settings wordt na registratie van alle beheermenu's naar de onderste positie verplaatst.
- De bestaande volgorde van de Tempel Settings-submenu's blijft behouden.

## [2.8.3] - 2026-09-01

- Het Gravity Forms-honeypotveld wordt altijd visueel verborgen, ook wanneer een thema de standaard Gravity Forms-CSS niet laadt.
- Het verborgen veld blijft technisch aanwezig en onderdeel van de spamcontrole.

## [2.8.2] - 2026-09-01

- Herstelt eenmalig alle algemene schakelaars wanneer deze door de Gravity Forms-opslagfout gezamenlijk zijn uitgeschakeld.
- E-mailverificatie blijft bij het herstel bewust uitgeschakeld totdat betrouwbare uitgaande e-mail is gecontroleerd.
- Kruistests toegevoegd voor Algemeen, Gravity Forms en Performance, zodat de pagina's elkaars instellingen niet wijzigen.

## [2.8.1] - 2026-09-01

- Voorkomt dat het opslaan van de Gravity Forms-instellingen opties op de pagina Algemeen uitschakelt.
- Regressietests toegevoegd voor het behouden van branding, security lock, e-mailverificatie en algemene functies.

## [2.8.0] - 2026-09-01

- Beveiligingsblokkades toegevoegd voor plugininstallaties, nieuwe beheerders en de ingebouwde plugin- en thema-editor.
- Kwetsbare WPMU DEV Dashboard-versies tot en met 5.0.1 worden automatisch gedeactiveerd wanneer deze actief zijn.
- E-mailmeldingen toegevoegd voor plugininstallaties, nieuwe beheerders en promoties naar beheerder.
- Optionele tweestapslogin via een zescijferige e-mailcode toegevoegd voor backendgebruikers zonder geconfigureerde Defender 2FA.
- Instelbare Gravity Forms-bewaartermijn, uurlijkse en handmatige opschoning en een daarop begrensd conversiedashboard toegevoegd.
- De bewaartermijn gebruikt een exclusieve grens: inzendingen op exact de ingestelde termijn blijven staan; alleen oudere inzendingen worden verwijderd.
- Gravity Forms-adresinstellingen, bewaartermijn en onzichtbare antispam samengevoegd op één instellingenpagina.
- Resultaatopbouw van het conversiedashboard hersteld voor meerdere geselecteerde formulieren.

## [2.7.43] - 2026-09-01

- Adresveld, bewaartermijn, handmatig opschonen en antispam samengevoegd op één pagina **Gravity Forms**.
- Optionele globale onzichtbare antispam toegevoegd voor alle Gravity Forms-formulieren.
- De antispam combineert de Gravity Forms-honeypot met een ondertekende invultijd en JavaScript-controle.
- Verdachte inzendingen gaan naar spam, zodat ze controleerbaar blijven en bezoekers geen captcha zien.

## [2.7.42] - 2026-09-01

- Handmatige knop toegevoegd om direct maximaal 500 inzendingen ouder dan de bewaartermijn permanent te verwijderen.
- Veilige uurlijkse beheerfallback toegevoegd voor websites waarop WP-Cron niet betrouwbaar draait.
- Planning hersteld: de cron-taak wordt direct ingepland wanneer de retentiemodule tijdens WordPress `init` wordt geladen.
- Na handmatig opschonen wordt het aantal verwijderde inzendingen gemeld.

## [2.7.41] - 2026-08-31

- Voorkomt een redirectlus wanneer de codestap leeg of automatisch wordt verstuurd.
- De lege codestap blijft zonder fouttekst staan en kost geen poging.

## [2.7.40] - 2026-08-31

- De zescijferige login-code staat in HTML-mail groot, vet en op een eigen regel.

## [2.7.39] - 2026-08-31

- Een lege of automatisch verstuurde codestap keert zonder foutmelding terug naar het codeformulier.
- Een lege code kost geen loginpoging meer.

## [2.7.38] - 2026-08-31

- Dwingt de e-mailcode ook af wanneer Defender of een andere loginprovider de gebruiker buiten de standaard WordPress-wachtwoordcontrole authenticeert.

## [2.7.37] - 2026-08-31
- Defender-detectie aangescherpt: alleen een actieve en werkelijk beschikbare Defender 2FA-methode mag de Tempel-code overslaan.
- Achtergebleven Defender-gebruikersmetadata kan daardoor niet meer leiden tot een directe login zonder tweede stap.

## [2.7.36] - 2026-08-31
- Na een correct wachtwoord wordt nu doorgestuurd naar een losse codestap in plaats van een authenticatiefout te tonen.
- Defender telt het aanvragen van de e-mailcode daardoor niet meer als mislukte loginpoging.
- De losse codestap verbergt de eerdere inlogvelden consequent en behoudt de oorspronkelijke beheerredirect.

## [2.7.35] - 2026-08-31
- E-mailverificatie omgebouwd naar een echte tweestapslogin: eerst inloggegevens, daarna uitsluitend de code.
- Een tijdelijke eenmalige login-uitdaging vervangt het opnieuw invoeren of bewaren van het wachtwoord.
- De codestap bevat een link om opnieuw met de gewone login te beginnen.

## [2.7.34] - 2026-08-31
- Het invoerveld voor de e-mailcode zichtbaar gemaakt binnen het gebrande Tempel-loginformulier.
- Een duidelijke placeholder en instructie toegevoegd voor de tweede loginpoging.

## [2.7.33] - 2026-08-31
- E-mailmelding naar info@studiotempel.nl toegevoegd wanneer een nieuwe beheerder wordt aangemaakt.
- E-mailmelding toegevoegd wanneer een bestaande gebruiker naar beheerder wordt gepromoveerd.
- Dubbele meldingen tijdens één WordPress-gebruikersactie worden voorkomen.

## [2.7.32] - 2026-08-31
- E-mailverificatie hersteld voor gemaskeerde WordPress-loginroutes, waaronder de aangepaste Defender-login-URL.
- WooCommerce frontend-login blijft uitgesloten.

## [2.7.31] - 2026-08-31
- Optionele zescijferige e-mailverificatie toegevoegd voor directe WordPress-logins van gebruikers zonder geconfigureerde Defender 2FA-methode.
- WooCommerce frontend-logins, REST, XML-RPC en andere niet-WordPress-loginroutes worden niet door de fallback geraakt.
- Login-codes verlopen na tien minuten en worden na vijf onjuiste pogingen ingetrokken.

## [2.7.30] - 2026-08-31
- E-mailmelding naar info@studiotempel.nl toegevoegd na installatie van een plugin via WordPress.
- Optionele globale bewaartermijn toegevoegd voor Gravity Forms-inzendingen, met definitieve automatische opschoning van maximaal 500 oude inzendingen per uur.
- De periode van het conversiedashboard wordt verkort tot de ingestelde bewaartermijn wanneer deze korter is dan 30 dagen.

## [2.7.29] - 2026-08-29
- Actieve WPMU DEV Dashboard-versies tot en met 5.0.1 worden eenmalig automatisch gedeactiveerd.
- De ingebouwde plugin- en thema-editor wordt uitgeschakeld zolang de beveiligingsblokkade actief is.

## [2.7.28] - 2026-08-28
- Beveiligingsblokkade standaard ingeschakeld bij installatie en eenmalig bij bijwerken. Daarna blijft handmatig uitschakelen mogelijk.

## [2.7.27] - 2026-08-28
- Optionele beveiligingscheckbox toegevoegd om plugininstallaties en het aanmaken of promoveren van beheerders te blokkeren.
- Bestaande beheerders en pluginupdates blijven beschikbaar; de blokkade is via Tempel Settings uit te zetten.

## [2.7.26] - 2026-08-21
- Compact statusoverzicht toegevoegd voor WordPress, PHP, geheugen, cron, e-mail, API, HTTPS en zoekmachinezichtbaarheid.
- Privacyveilig technisch logboek toegevoegd voor e-mail, PostcodeAPI en Gravity Forms, met filters, maximaal 500 regels en automatische opschoning na 30 dagen.
- Tweemaal daagse gezondheidstests en veilige HTTPS-monitoring voor maximaal vijf webhook-endpoints toegevoegd.

## [2.7.25] - 2026-08-20
- Tempel contentduplicatie wordt eenmalig overal ingeschakeld en vervangt Yoast Duplicate Post.
- Veilige gebruikerswisseling met terugkeeractie toegevoegd als vervanging voor de losse User Switching-plugin.
- Het nieuwe WordPress 7.1-site-icoon verborgen in de aangepaste toolbar, met behoud van het sitemenu.

## [2.7.24] - 2026-08-17
- Nederlandse postcodes worden bij het verzenden van Gravity Forms server-side gevalideerd voordat gegevens naar externe API's gaan.

## [2.7.23] - 2026-08-13
- De laatste instellingstab hernoemd naar Info.
- De pluginstatus en changelog samengevoegd op de Info-pagina.
- De losse Status-tab verwijderd.

## [2.7.22] - 2026-08-13
- Een aparte Changelog-pagina toegevoegd aan Tempel Settings.
- De historische wijzigingen samengebracht in één onderhoudbaar changelogbestand.

## [2.7.21] - 2026-08-13
- Het productie-releasepakket gevalideerd en klaargemaakt.

## [2.7.20] - 2026-08-12
- De knop Menu kopiëren rechts uitgelijnd en gelijkgetrokken met de opslaanknop.

## [2.7.19] - 2026-08-12
- Bulkduplicatie toegevoegd voor berichten, custom post types en taxonomietermen.
- De acties onder klassieke menu's verbeterd en het verwijdericoon hersteld.
- De beveiligde URL voor menu dupliceren gecorrigeerd.

## [2.7.18] - 2026-08-12
- Optionele duplicatie toegevoegd voor berichten, pagina's, custom post types en taxonomietermen.
- Klassieke menu's kunnen worden gekopieerd met behoud van volgorde en submenu's, zonder automatisch een menulocatie toe te wijzen.

## [2.7.17] - 2026-08-12
- Login-foutmeldingen van beveiligingsplugins blijven wit op het donkere loginscherm.

## [2.7.16] - 2026-07-10
- Gebundelde WordPress-thema's worden standaard overgeslagen, ook voor bestaande installaties.

## [2.7.15] - 2026-07-09
- Instellingen en statusweergave voor servicecontracten verwijderd.

## [2.7.14] - 2026-07-09
- Login-foutmeldingen en Defender-pogingsmeldingen wit gemaakt op het donkere loginscherm.

## [2.7.13] - 2026-07-08
- Regelafbreking in de beveiligingsmelding voor gemaskeerde login-URL's hersteld.

## [2.7.12] - 2026-07-08
- De regelafbreking in de gemaskeerde-loginmelding omgezet naar HTML.

## [2.7.11] - 2026-07-08
- StudioTempel-ondertekening toegevoegd aan de gemaskeerde-loginmelding.

## [2.7.10] - 2026-07-08
- Tekst van de WP Defender-melding voor gemaskeerde login-URL's bijgewerkt.

## [2.7.9] - 2026-07-08
- De standaard WP Defender-melding vervangen door een duidelijkere Nederlandse tekst.

## [2.7.8] - 2026-07-08
- Extra ruimte toegevoegd tussen e-mailadres en wachtwoord op het loginscherm.

## [2.7.7] - 2026-07-08
- Het loginscherm vernieuwd met donkere achtergrond, kaartopmaak en gele knop.

## [2.7.6] - 2026-07-08
- De loginknop schermbreed en zwart gemaakt; wachtwoordlink gecentreerd.

## [2.7.5] - 2026-07-08
- Standaard onderwerp en bericht toegevoegd voor de e-mail over de WordPress-admin-URL.

## [2.7.4] - 2026-07-08
- Het loginscherm gecentreerd, de foto verwijderd, gele huisstijl toegepast en privacylink verborgen.

## [2.7.3] - 2026-07-08
- Geselecteerde ontvangers, onderwerp en berichtinhoud blijven behouden na het versturen van mail.

## [2.7.2] - 2026-07-08
- Beschikbare mailtags bovenaan de Mail-pagina geplaatst.

## [2.7.1] - 2026-07-08
- Personalisatietags toegevoegd voor naam, e-mail en websitegegevens.
- De Mail-tab als laatste in de navigatie geplaatst.

## [2.7.0] - 2026-07-08
- Een Mail-pagina toegevoegd voor beveiligde HTML-mail aan geselecteerde WordPress-gebruikers.
- WYSIWYG-editor, ontvangerselectie, rechtencontrole en nonce-beveiliging toegevoegd.

## [2.6.10] - 2026-07-01
- De opgeslagen PostcodeAPI-sleutel weer zichtbaar gemaakt in het wachtwoordveld.
- Een gemaskeerde voorvertoning van de API-sleutel toegevoegd.

## [2.6.9] - 2026-07-01
- Een niet-geteste PostcodeAPI-verbinding neutraal grijs gemaakt.
- De verbindingstest kan een nog niet opgeslagen sleutel en endpoint gebruiken.

## [2.6.8] - 2026-07-01
- De Status-tab vereenvoudigd en statusregels de volledige breedte gegeven.

## [2.6.7] - 2026-07-01
- Registratie en rechten van de Status-tab gecorrigeerd.

## [2.6.6] - 2026-07-01
- Pluginstatus naar een eigen Status-tab verplaatst en als eerste tab geplaatst.

## [2.6.5] - 2026-07-01
- Statuscontroles toegevoegd voor Gravity Forms, PostcodeAPI, cache en performance.
- Een PostcodeAPI-verbindingstest toegevoegd.

## [2.6.4] - 2026-07-01
- Formuliervelden en Select2-opmaak in de instellingen verbeterd.

## [2.6.3] - 2026-07-01
- Het Gravity Forms-adresveld standaard uitgeschakeld bij installatie en update.

## [2.6.2] - 2026-07-01
- WordPress `readme.txt` toegevoegd voor plugininformatie en release-opmerkingen.

## [2.6.1] - 2026-07-01
- Widgetinstellingen, API-sleutelopslag en het standaard PostcodeAPI-endpoint verbeterd.

## [2.6.0] - 2026-07-01
- Magic Login verwijderd.
- Performance naar een eigen pagina verplaatst met presets voor verschillende sitetypen.

## [2.5.35] - 2026-07-01
- Ruimte rond Gravity Forms-validatiemeldingen verminderd en lege lookupmeldingen verborgen.

## [2.5.28–2.5.34] - 2026-06-30
- Het Nederlandse adresveld, vereiste subvelden, foutmeldingen en read-only-opmaak verfijnd.
- Een aparte adresinstellingenpagina en limieten voor gebruik, cache en aanvragen toegevoegd.

## [2.5.18–2.5.27] - 2026-06-30
- Layout, placeholders, handmatige invoer en foutafhandeling van het adresveld verbeterd.
- Dubbele adresaanvragen voorkomen en verbinding met PostcodeAPI robuuster gemaakt.

## [2.5.9–2.5.17] - 2026-06-30
- Het Gravity Forms-adresveld met PostcodeAPI-lookup, caching en frontendupdates opgebouwd en verfijnd.

## [2.5.8] - 2026-06-11
- Performance-instellingen toegevoegd voor geheugen, revisies, Heartbeat, emoji's, embeds en XML-RPC.

## [2.5.0–2.5.7] - 2026-06-03
- De 2.5-releaselijn opgebouwd en als installeerbare pluginpakketten uitgebracht.
- Onderhouds- en compatibiliteitscorrecties doorgevoerd.

## [2.4.2] - 2026-06-03
- Een fatale fout in de 2.4-releaselijn opgelost.

## [2.4.1] - 2026-06-03
- Checkup-functionaliteit en versiegegevens gecorrigeerd.

## [2.4.0] - 2026-06-03
- De 2.4-releaselijn geïntroduceerd.

## [2.1.5] - 2024-09-26
- Een typehint gecorrigeerd die fouten veroorzaakte op PHP 7.4.

## [2.1.4] - 2024-09-12
- Nieuwe vormgeving voor de admin-instellingenpagina geïntegreerd.
- Sitescan hernoemd naar Checkup.

## [2.1.3] - 2024-08-28
- Cloudways-gerelateerde verbeteringen uitgebracht.

## [2.1.2] - 2024-08-28
- Een lege melding op het loginscherm opgelost.
- Admin- en loginvormgeving bijgewerkt.

## [2.1.1] - 2024-08-27
- Automatische updates en hoofdlettergevoelige bestandsimports hersteld.
- De pluginstructuur opgeschoond.

## [2.1.0] - 2024-08-23
- Een opgeschoonde map- en bestandsstructuur geïntroduceerd.

## [2.0.0–2.0.9] - 2024-06-25
- Pluginstructuur, standaardinstellingen en installatieproces grondig vernieuwd.
- De GitHub-release-updater toegevoegd en gecorrigeerd.
- Vendor-assets, toolbarstijlen en dependencyversies bijgewerkt.

## [1.8.1] - 2024-02-23
- Versiegegevens en documentatie bijgewerkt.

## [1.8] - 2024-02-23
- Hosting en releasebeheer naar GitHub verplaatst.

## [1.7] - 2024-02-23
- Pluginbasis, branding en assetstructuur vernieuwd.

## [1.6] - 2024-01-09
- Het supportwidget toegevoegd.

## [1.5] - 2023-10-03
- De eerste gedocumenteerde pluginversie met classes, snelle pagina-aanmaak en mappenstructuur uitgebracht.

