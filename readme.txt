=== Tempel ===
Contributors: studiotempel
Tags: tempel, admin, widgets, gravity forms, performance
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 7.1
Stable tag: 4.9.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI, beheer en gereedschap voor websites van Studio Tempel.

== Description ==

Tempel combineert een bedrijfsgebonden AI-schrijfassistent met beheer, beveiliging, back-ups en gereedschap voor WordPress-websites van Studio Tempel.

== Installation ==

1. Upload de pluginmap `tempel-settings` naar `/wp-content/plugins/`.
2. Activeer de plugin via het WordPress plugin-overzicht.
3. Configureer de gewenste onderdelen via `Tempel`.

== Changelog ==

= 4.9.2 =

* Koppelt een website automatisch met Tempel Hub zodra het domein daar geregistreerd staat en de domeinchallenge slaagt.
* Verwijdert het tijdelijke centrale koppelvenster: geregistreerde domeinen blijven veilig automatisch koppelbaar.
* Probeert een mislukte koppeling na 15 minuten opnieuw en direct opnieuw na iedere pluginupdate.

= 4.9.1 =
* AI-model, sleutel, centrale aan/uit-status en technische limieten worden alleen nog vanuit Tempel Hub beheerd.
* Alleen de toegestane WordPress-rollen blijven lokaal instelbaar.
* Websites die in het CRM staan kunnen na deze update eenmalig automatisch en domeingebonden koppelen.

= 4.9.0 =
* Eerste Tempel Hub-koppeling voor centraal AI-beheer zonder OpenAI-sleutels op websites.
* Eenmalige domeingebonden koppelcodes en per-site ondertekende verzoeken met replaybescherming.
* Lokale OpenAI-configuratie blijft beschikbaar als fallback tijdens de uitrol.

= 4.8.0 =
* Voegt de aparte pagina Optimalisatie toe voor een adviserend verbeterplan van bestaande pagina's.
* Analyseert gewone inhoud, geselecteerde ACF/Flexible Content, SEO-data en het bedrijfs-DNA.
* Toont voortgang, sterke punten, knelpunten, concrete verbeteringen, herschrijfvoorstellen en een SEO-voorstel zonder de pagina automatisch te wijzigen.

= 4.7.7 =
* Voegt een vast verticaal ritme toe voor secties, titels, items, toelichtingen en statusregels op de DNA-pagina.
* Geeft de titel Bedrijfs-DNA zestien pixels ruimte tot het eerste hoofdstuk.

= 4.7.6 =
* Maakt de verticale ruimte op de DNA-pagina consistent en compacter.
* Verkleint de ruimte rond de paginakop, generator, hoofdstuktitel en gesloten hoofdstukken.

= 4.7.5 =
* Brengt de DNA-generator onder in een compacte accordeon die standaard gesloten is.
* Vereenvoudigt broninformatie en voorstelinstellingen tot rustige, compacte regels.
* Houdt maximaal één DNA-hoofdstuk tegelijk geopend en maakt acties per antwoord subtieler.

= 4.7.4 =
* Toont per DNA-hoofdstuk hoeveel antwoorden door een mens zijn gecontroleerd en hoeveel alleen uit AI komen.
* Voegt per DNA-antwoord een afzonderlijke status Gecontroleerd toe.
* Maakt de controlestatus automatisch ongeldig zodra een tekst opnieuw wordt gewijzigd of door AI wordt vervangen.

= 4.7.3 =
* Maakt de DNA-pagina rustiger met compacte accordeons per merkstrategiehoofdstuk.
* Groepeert ook gegenereerde DNA-voorstellen per ingeklapt hoofdstuk.
* Vervangt nadrukkelijke AI-accenten in het bedrijfsprofiel door een functionele voortgangsweergave.

= 4.7.2 =
* Toont op de Templeton Engine-pagina welke bronnen en kaders automatisch worden meegenomen.
* Maakt de dekking van DNA, website-inhoud, ACF/Flexible Content en SEO-bronnen direct zichtbaar.

= 4.7.1 =
* Toont na iedere geslaagde opslagactie binnen Tempel een vaste, consistente melding rechtsonder.
* Geeft dezelfde melding bij live opslaan, klassieke WordPress-opslag en later toegevoegde succesmeldingen.

= 4.7.0 =
* Bouwt het bedrijfs-DNA op volgens de Studio Tempel-vragenlijst voor merkstrategie in zes stappen.
* Gebruikt de geselecteerde contentinventarisatie zichtbaar als primaire bron voor DNA-voorstellen.
* Bewaart gegenereerde voorstellen apart en laat beheerders per onderdeel overnemen, aanvullen of overslaan.
* Voegt de vaste SEO- en webredactiewerkwijze toe aan Templeton Engine 2.6.

= 4.6.0 =
* Voegt een aparte beheerpagina voor Templeton Engine 2.5 toe met vaste Studio Tempel-richtlijnen.
* Past het redactionele kader automatisch toe op alle schrijftaken, DNA-analyses en gerichte DNA-updates.
* Beheert schrijfstijl, feiten en claims, SEO, structuur, verboden formuleringen, kwaliteitscontrole en voorbeelden met live opslaan.

= 4.5.0 =
* Voegt Contentinventaris toe voor het beheren van publieke contenttypes, ACF-velden en SEO-bronnen die Templeton mag gebruiken.
* DNA-voorstellen kunnen bestaande gegevens aanvullen of volledig overschrijven.
* Ieder DNA-onderdeel kan afzonderlijk opnieuw worden onderzocht met een toelichting op wat er gewijzigd is.

= 4.4.0 =
* Slaat alle echte Tempel-instellingen automatisch op zonder losse opslaanknoppen.
* Toont subtiele live status bij opslaan, succes en fouten.
* Houdt bewuste acties zoals verzenden, opschonen, back-uppen, koppelen en verhuizen buiten automatisch opslaan.

= 4.3.4 =
* Maakt de tagskolom in het bedrijfs-DNA ruimer zodat langere tags beter leesbaar blijven.

= 4.3.3 =
* Hernoemt Steekwoorden naar Tags en plaatst titel en beschrijving boven een rustige tweekolomsindeling met tags links en tekst rechts.

= 4.3.2 =
* Toont tijdens diep DNA-onderzoek een live onderzoekslog met de afzonderlijke scanonderdelen.
* Presenteert gebruikte bronnen als duidelijke bronkaarten en maakt steekwoorden bewerkbaar als losse tags.

= 4.3.1 =
* Bronnenoverzicht vermeldt nu ook websiteprofiel, onderzochte openbare contenttypes, ACF/Flexible Content en categorieën en tags.
* Externe markt- en trendbronnen blijven apart herkenbaar en klikbaar.

= 4.3.0 =
* Voegt bij ieder onderdeel van het bedrijfs-DNA compacte, bewerkbare steekwoorden toe.
* Templeton genereert per onderdeel vier tot tien concrete tags naast de uitgebreide analyse.
* Alle volgende AI-opdrachten gebruiken zowel de DNA-tekst als de bijbehorende stuurwoorden.

= 4.2.1 =
* Voert diep DNA-onderzoek uit als achtergrondtaak met live fases, verstreken tijd en foutstatus.
* Toont Templeton Engine 2.4 tijdens het genereren.
* Laat alle DNA-tekstvelden automatisch meegroeien met hun inhoud.

= 4.2.0 =
* Verdiept DNA-onderzoek met openbare custom post types, ACF-tekst, Flexible Content, categorieën en tags.
* Leidt onderbouwde doelgroep-hypotheses af wanneer de doelgroep niet letterlijk op de website staat.
* Gebruikt afgeleide thema’s en zoekbegrippen voor gericht onderzoek naar markt, klantgedrag, regelgeving en trends.
* Voegt Kernonderwerpen en trends toe aan het blijvende bedrijfs-DNA.

= 4.1.1 =
* Maakt Back-ups, Website verhuizen en de statuskaarten volledig geschikt voor het lichte Tempel-thema.
* Centraliseert de lichte kleuren via de gedeelde Tempel-designtokens.

= 4.1.0 =
* Splits DNA en technische AI-instellingen in aparte pagina's voor editors en beheerders.
* Hernoem de bestaande technische instellingen naar Beheer.
* Laat Tempel een DNA-voorstel maken op basis van website-inhoud, doelgroepinvoer en optionele actuele webtrends.
* Voeg een licht Tempel-thema toe en schakel dit standaard in.

= 4.0.3 =
* Verwijder de waarschuwing over gevoelige gegevens van de AI & DNA-pagina.

= 4.0.2 =
* Geef labels en toelichtingen van AI & DNA een duidelijke hiërarchie en vaste tussenruimte.

= 4.0.1 =
* Herken verborgen Tempel-detailpagina's betrouwbaar aan hun scherm-ID en paginaslug.
* Laad de volledige donkere Tempel-interface en veldopmaak nu ook correct op AI & DNA.

= 4.0.0 =
* Hernoem Tempel Settings naar Tempel.
* Voeg een centraal bedrijfs-DNA toe met doelgroep, aanbod, positionering, tone of voice, woordkeuze en voorbeeldtekst.
* Gebruik het bedrijfs-DNA automatisch bij iedere AI-schrijfopdracht.
* Voeg de actie Websiteonderdeel schrijven toe als eerste basis voor toekomstige blokgeneratie.

= 3.3.1 =
* Lijn alle regels van het compacte publicatieblok links uit.
* Centreer de tekst verticaal ten opzichte van de bijbehorende iconen.

= 3.3.0 =
* Maak het publicatieblok in de klassieke editor rustiger door de labels Status, Zichtbaarheid en Gepubliceerd op visueel te verbergen.
* Behoud de WordPress-iconen, actuele waarden, bewerklinks en alle publicatieacties.
* Schakel de functie standaard in en maak haar instelbaar onder Algemeen → Uiterlijk.

= 3.2.1 =
* Behoud koppen, paragrafen, lijsten, links, nadruk en CSS-klassen bij AI-bewerking van WYSIWYG-inhoud.
* Toon HTML-antwoorden als opgemaakt en bewerkbaar voorbeeld in plaats van zichtbare HTML-code.
* Filter alle gegenereerde HTML server-side met de WordPress-veiligheidsregels voordat deze wordt teruggeplaatst.

= 3.2.0 =
* Plaats een compacte AI-knop direct bij ieder ondersteund ACF-tekstveld en bij de klassieke editor.
* Open de schrijfassistent onmiddellijk voor dat veld, met de selectie of volledige veldinhoud al ingevuld.
* Voeg knoppen automatisch toe aan nieuwe Flexible Content-layouts en repeaterregels zonder paginaverversing.

= 3.1.1 =
* Ondersteun ACF-tekstvelden, textarea's en WYSIWYG-editors, ook binnen Flexible Content en geneste repeaters.
* Toon vóór het genereren en terugplaatsen duidelijk welk ACF-veld actief is.

= 3.1.0 =
* Voeg een optionele AI-schrijfassistent toe voor Gutenberg en de klassieke editor.
* Bewaar de OpenAI API-sleutel versleuteld en gebruik deze uitsluitend server-side.
* Voeg dag-, maand- en tokenlimieten, rollencontrole en een voorbeeldstap voor invoegen toe.

= 3.0.5 =
* Toon de download- en importblokken direct onder de SFTP-verhuizing, zonder uitklapbare buitenbox.

= 3.0.4 =
* Voorkom een ongeldige pakketdownload doordat HTML-gecodeerde `&amp;`-tekens als onderdeel van de download-URL werden gebruikt.

= 3.0.3 =
* Controleer na export het daadwerkelijk gemaakte ZIP-pakket in plaats van het laatst verzamelde websitebestand; hiermee verdwijnt ZIP-fout 19.

= 3.0.2 =
* Voorkom HTTP 404 bij het maken van een verhuispakket door de formulier-URL expliciet uit het action-attribuut te lezen.

= 3.0.1 =
* Maak verhuispakketten via de beveiligde admin-post-route, zodat hostings die langdurige admin-ajax-aanvragen met HTTP 404 afbreken niet langer vastlopen.
* Houd e-mailverificatie voor backend-logins standaard uit en opt-in.

= 3.0.0 =
* Vereenvoudig het beheer tot Overzicht, Instellingen, Gereedschap en Logboek.
* Voeg een centrale UI-stijlgids, design tokens en herbruikbare componenten toe.
* Voeg live back-upstatus toe zonder de pagina te vernieuwen.
* Gebruik dezelfde voortgangstaal voor back-ups en websiteverhuizingen.
* Centraliseer toegang tot bestaande instellingen zonder opgeslagen waarden te wijzigen.
* Voorkom dat de adminnavigatie tijdens het renderen opnieuw hooks registreert.
* Voeg vaste editor- en PHP-codestandaarden toe voor verdere ontwikkeling.
* Voeg optionele e-mailverificatie voor backend-logins en een noodschakelaar voor mailproblemen toe.
* Sluit WP-Members-logins op de website expliciet uit van backend-e-mailverificatie.
* Beperk e-mailverificatie tot beheerlogins via wp-admin, wp-login.php en st-beheer.
* Dwing op alle huidige en toekomstige Tempel-schermen het donkere canvas en de vaste veldindeling af.
* Plaats de witte Mail-WYSIWYG over de volledige veldbreedte met label en toelichting erboven.
* Houd visueel verborgen detailpagina's bereikbaar via directe links en de interne navigatie.
* Voeg Info, pluginstatus en changelog samen met Status & logboek.

= 2.20.1 =
* Toon duidelijk wanneer de Dropbox App secret veilig is opgeslagen en verberg een verouderde configuratiefout.

= 2.20.0 =
* Integreer Website verhuizen met SFTP, verhuispakketten, voortgang en zichtbare back-upcontrole.
* Voeg retries, schijfruimtecontrole, ZIP- en Dropbox-verificatie en opruiming van tijdelijke back-upbestanden toe.

= 2.19.0 =
* Verstuur grote Dropbox-back-ups hervatbaar in korte achtergrondstappen en toon de uploadvoortgang.

= 2.18.1 =
* Maak veilige grote bestanden direct bekijkbaar en meerdere uploads- of cachebestanden selecteerbaar en verwijderbaar.

= 2.18.0 =
* Voeg op Status & logboek een handmatige scan toe met de 50 grootste bestanden van de WordPress-installatie.

= 2.17.6 =
* Zet de blokkade voor plugininstallaties en nieuwe beheerders optioneel na één uur automatisch weer aan; standaard ingeschakeld.

= 2.17.5 =
* Corrigeer de donkere achtergrond, veldbreedtes en planning-layout van de Back-ups-pagina.

= 2.17.4 =
* Herstel het laden en verversen van de Tempel-vormgeving op de Back-ups-pagina.

= 2.17.3 =
* Voeg een visuele Dropbox-mapbrowser toe aan de Back-ups-pagina.

= 2.17.2 =
* Vernieuw de Back-ups-pagina met een statusoverzicht, rustigere indeling en ingeklapte technische instellingen.

= 2.17.1 =
* Voeg een instelbaar back-uptijdstip, verplichte foutmail en optionele succesmail toe.

= 2.17.0 =
* Voeg optionele volledige Dropbox-back-ups toe met OAuth, planning, retentie en handmatige uitvoering.

= 2.16.0 =
* Voeg optionele .htaccess-beveiliging toe met eigen markers, databaseback-up, loopbackcontrole en verwijderknop.

= 2.15.2 =
* Sluit een melding direct wanneer de actieknop wordt aangeklikt.
* Start na een actieklik niet meteen de volgende melding op dezelfde positie.

= 2.15.1 =
* Registreer de Polylang-koppeling vroeg genoeg om de taalkeuze bij Meldingen zichtbaar te maken.

= 2.15.0 =
* Geef actieknoppen ook de algemene themaklasse .button.
* Centreer de knop automatisch wanneer het laatste WYSIWYG-element gecentreerd is.
* Lever een eenvoudige voorbeeldstylesheet met CSS-variabelen voor themastyling.

= 2.14.1 =
* Vereenvoudig het meldingsformulier tot de tabs Inhoud en Instellingen.

= 2.14.0 =
* Maak Meldingen automatisch vertaalbaar wanneer Polylang actief is.

= 2.13.5 =
* Verwijder de toelichting onder de knop Frequentie resetten.

= 2.13.4 =
* Gebruik bij start- en einddatum alleen uren en minuten, zonder seconden.

= 2.13.3 =
* Verwijder de teksten Yes en No uit alle ACF-schakelaars van Meldingen.

= 2.13.2 =
* Toon Donkere achtergrond tonen alleen wanneer de positie Midden als popup is gekozen.

= 2.13.1 =
* Toon de frequentie van iedere melding als aparte kolom in het overzicht.

= 2.13.0 =
* Voeg in het Meldingen-overzicht een directe schakelaar voor activeren en deactiveren toe.
* Toon de ingestelde positie als aparte kolom.

= 2.12.1 =
* Geef per positie paginaspecifieke meldingen voorrang op algemene meldingen.
* Toon meldingen zonder sluitknop als laatste in hun wachtrij.

= 2.12.0 =
* Sta tegelijk één middenpopup, één bovenbalk en één onderbalk toe.
* Toon meldingen met dezelfde positie na elkaar volgens de volgorde in het overzicht.

= 2.11.1 =
* Probeer de volgende melding wanneer een hoger geplaatste melding in deze browser al is afgehandeld.
* Onthoud de frequentie ook wanneer de actieknop wordt gebruikt en verbeter de foutafhandeling bij sorteren.

= 2.11.0 =
* Voeg voor meldingen boven en onder een compacte balkweergave toe.
* Plaats tekst, knop en sluitknop met minder padding zoveel mogelijk naast elkaar.

= 2.10.2 =
* Begrens afbeeldingen in meldingen standaard tot de beschikbare breedte.
* Geef te hoge meldingen een interne scrollbar en vrije ruimte langs de schermranden.

= 2.10.1 =
* Houd de schakelaar Donkere achtergrond tonen altijd zichtbaar onder Weergave.

= 2.10.0 =
* Voeg onder Herhaling een knop toe om de frequentiecookie van een melding voor alle bezoekers te resetten.

= 2.9.9 =
* Gebruik een los HTML-element voor de donkere achtergrond, zodat themastijlen deze niet kunnen verbergen.

= 2.9.8 =
* Toon de ACF-instellingen van Meldingen zonder standaard metabox.
* Deel de velden op in tabs en plaats start- en einddatum naast elkaar.
* Plaats Sluitknop tonen direct onder Donkere achtergrond tonen.

= 2.9.7 =
* Herstel de donkere popupachtergrond zodat deze het volledige browservenster bedekt.

= 2.9.6 =
* Vervang het prioriteitsveld door een sleepbare volgorde in het overzicht Meldingen.
* Geef de bovenste toepasselijke melding voorrang op de website.

= 2.9.5 =
* Verwijder de bezoekersselectie uit Meldingen; publicatie geldt voortaan voor alle bezoekers.

= 2.9.4 =
* Verwijder het veld Eigen CSS-klasse uit de instellingen van Meldingen.

= 2.9.3 =
* Zorg dat een melding in het midden altijd door de bezoeker kan worden gesloten.
* Toon de instelling voor de sluitknop alleen bij een boven- of onderbalk.

= 2.9.2 =
* Verwijder de overbodige instructietekst onder het linkveld van een melding.

= 2.9.1 =
* Registreer het beheermenu Meldingen en de ACF-velden direct wanneer WordPress en ACF hun initialisatie al hebben uitgevoerd.

= 2.9.0 =
* Voeg de optionele ACF-module Meldingen toe voor middenpopups en balken boven of onder.
* Ondersteun WYSIWYG-inhoud, één ACF-linkveld, planning, URL-regels, doelgroepen, vertraging, mobiel, prioriteit en herhaalfrequentie.
* Lever eenvoudige toegankelijke basisopmaak die vanuit het thema kan worden overschreven.

= 2.8.6 =
* Voeg onder Algemeen → Uiterlijk een standaard ingeschakelde correctie toe voor een te grote Complianz-sluitknop.
* Behoud een latere handmatige uitschakeling van deze correctie.

= 2.8.5 =
* Neem de volledige updater-library op in het releasepakket.
* Sla uitsluitend de automatische GitHub-updatecontrole over wanneer de updater-library onverhoopt ontbreekt.

= 2.8.4 =
* Plaats Tempel Settings als laatste hoofditem in het WordPress-beheermenu.

= 2.8.3 =
* Verberg het Gravity Forms-honeypotveld altijd, ook wanneer het thema de standaard formulierstijlen niet laadt.

= 2.8.2 =
* Herstel eenmalig algemene instellingen die door de Gravity Forms-opslagfout allemaal zijn uitgeschakeld.
* Laat e-mailverificatie bij dit herstel uitgeschakeld, omdat werkende uitgaande e-mail eerst gecontroleerd moet worden.
* Voeg kruistests toe zodat instellingenpagina's elkaars opties niet wijzigen.

= 2.8.1 =
* Voorkom dat opslaan op de Gravity Forms-pagina algemene instellingen uitschakelt.

= 2.8.0 =
* Voeg beveiligingsblokkades toe voor plugininstallaties, nieuwe beheerders en de ingebouwde bestandseditor.
* Deactiveer kwetsbare WPMU DEV Dashboard-versies tot en met 5.0.1 automatisch wanneer deze actief zijn.
* Stuur meldingen bij plugininstallaties, nieuwe beheerders en promoties naar beheerder.
* Voeg optionele tweestapslogin via een zescijferige e-mailcode toe voor backendgebruikers zonder Defender 2FA.
* Voeg een instelbare Gravity Forms-bewaartermijn, handmatige opschoning en een begrensd conversiedashboard toe.
* Bewaar inzendingen op exact de ingestelde grensdatum en verwijder uitsluitend oudere inzendingen.
* Bundel adresinstellingen, bewaartermijn en onzichtbare antispam op één Gravity Forms-pagina.
* Herstel de resultaatopbouw van het conversiedashboard voor meerdere geselecteerde formulieren.

= 2.7.43 =
* Bundel adresveld, antispam en bewaartermijn op één Gravity Forms-instellingenpagina.
* Voeg optionele globale onzichtbare antispam toe met honeypot, JavaScript-controle en minimale invultijd.
* Verdachte inzendingen worden als spam gemarkeerd in plaats van zichtbaar geblokkeerd.

= 2.7.42 =
* Voeg een handmatige knop toe om direct maximaal 500 oude Gravity Forms-inzendingen op te schonen.
* Voer de uurlijkse opschoning ook veilig uit bij beheerbezoek wanneer WP-Cron niet betrouwbaar draait.
* Herstel het inplannen van de opruimtaak wanneer Tempel Settings tijdens WordPress init wordt geladen.

= 2.7.41 =
* Fix: voorkom een redirectlus wanneer de codestap leeg of automatisch wordt verstuurd.

= 2.7.40 =
* Toon de login-code in de e-mail groot en op een eigen regel.

= 2.7.39 =
* Fix: een lege of automatisch verstuurde codestap toont geen onjuiste-codefout meer en kost geen loginpoging.

= 2.7.38 =
* Fix: dwingt de e-mailcode ook af wanneer Defender of een andere loginprovider de gebruiker buiten de standaard WordPress-wachtwoordcontrole authenticeert.

= 2.7.37 =
* Only bypass Tempel email verification when the active Defender runtime confirms an actually available 2FA provider for the user.

= 2.7.36 =
* Redirect to the dedicated code step after a valid password so Defender no longer counts requesting a code as a failed login attempt.

= 2.7.35 =
* Changed email verification to a true two-step login: credentials first, then a dedicated code screen with a reset link.

= 2.7.34 =
* Fixed the email-code input being hidden by the branded login screen and clarified the second login step.

= 2.7.33 =
* Send info@studiotempel.nl an email when an administrator is created or an existing user is promoted to administrator.

= 2.7.32 =
* Fixed email login verification for masked WordPress login URLs such as Defender's custom login route.

= 2.7.31 =
* Added optional six-digit email verification for direct WordPress logins when the user has no configured Defender 2FA provider.
* Excluded WooCommerce frontend logins and other non-WordPress login flows from the fallback verification.

= 2.7.30 =
* Send info@studiotempel.nl an email after a plugin is installed through WordPress.
* Added an optional global retention period and automatic permanent cleanup for Gravity Forms entries.
* Limit the conversion dashboard period to the configured retention period when it is shorter than 30 days.

= 2.7.29 =
* Automatically deactivate WPMU DEV Dashboard 5.0.1 and older once when active.
* Disable the built-in plugin and theme file editor while the security lock is enabled.

= 2.7.28 =
* Enable the security lock by default on installation and once on upgrade. Subsequent manual changes are preserved.

= 2.7.27 =
* Added an optional security lock to block plugin installations and new administrator assignments.

= 2.7.26 =
* Added a compact website status overview for WordPress, PHP, memory, cron, mail, API, HTTPS and search visibility.
* Added a privacy-conscious technical log for mail, PostcodeAPI and Gravity Forms results with filters and automatic 30-day cleanup.
* Added twice-daily health checks and safe HTTPS webhook endpoint monitoring.

= 2.7.25 =
* Enabled Tempel content duplication by default and deactivated Yoast Duplicate Post once the replacement is active.
* Added secure user switching with a return action and deactivated the separate User Switching plugin once the replacement is active.
* Hid the WordPress 7.1 site icon in the custom admin toolbar while keeping the site menu available.

= 2.7.24 =
* Validate Dutch postcode formatting during Gravity Forms submission before data is sent to external APIs.

= 2.7.23 =
* Renamed the final settings tab to Info and combined plugin status with the changelog.
* Removed the separate Status tab.

= 2.7.22 =
* Added a dedicated Changelog page to Tempel Settings.
* Consolidated the reconstructed release history in `CHANGELOG.md`.

= 2.7.21 =
* Prepared and validated the production release package.

= 2.7.20 =
* Aligned the classic menu copy action with the save button on the right side.

= 2.7.19 =
* Added bulk duplication for supported posts, custom post types and taxonomy terms.
* Improved the classic menu action layout and delete icon.
* Fixed the menu duplication nonce URL.

= 2.7.18 =
* Added optional duplication actions for posts, pages, custom post types and taxonomy terms.
* Added menu duplication with preserved item hierarchy and without assigning menu locations.

= 2.7.17 =
* Ensured login error messages from security plugins remain white on the dark login screen.

= 2.7.16 =
* Disabled bundled WordPress themes by default for existing installations.

= 2.7.15 =
* Removed the service contract settings and status widget output.

= 2.7.14 =
* Made login error messages and Defender attempt notices white on the dark login screen.

= 2.7.13 =
* Fixed the masked-login security message line break when the message is escaped before rendering.

= 2.7.12 =
* Changed the masked-login security message line break to HTML so it displays correctly.

= 2.7.11 =
* Added the StudioTempel team sign-off to the masked-login security message.

= 2.7.10 =
* Updated the WP Defender masked-login security message text.

= 2.7.9 =
* Replaced the WP Defender masked-login security message with a clearer Dutch message.

= 2.7.8 =
* Added extra spacing between the email address and password fields on the login screen.

= 2.7.7 =
* Updated the login screen to a dark background and card style with a yellow full-width login button.

= 2.7.6 =
* Updated the login screen button to full-width black styling and centered the lost password link below it.

= 2.7.5 =
* Added default Mail subject and message content for the WordPress admin URL notification.

= 2.7.4 =
* Updated the login screen styling: removed the background photo, centered the login form, switched to the yellow brand background and hid the privacy policy link.

= 2.7.3 =
* Preserve selected recipients, subject and WYSIWYG message content after sending a mail.

= 2.7.2 =
* Moved the mail placeholder tags to the top of the Mail settings page.

= 2.7.1 =
* Added mail tags for personalized user emails, including `[naam]`, `[voornaam]`, `[email]`, `[website_url]` and `[website_naam]`.
* Moved the Mail settings tab to the end of the Tempel Settings navigation.

= 2.7.0 =
* Added a Mail settings page for sending HTML email from the website to selected WordPress users.
* Added a WYSIWYG editor, user multi-select, permission checks and nonce-protected sending via `wp_mail()`.

= 2.6.10 =
* Show the saved PostcodeAPI.nu API key in the password field again.
* Added a masked API-key preview below the API-key label.

= 2.6.9 =
* Changed the untested PostcodeAPI status from warning yellow to neutral grey.
* Allowed the API test button to use a newly typed API key and endpoint before saving.

= 2.6.8 =
* Simplified the Status tab by removing duplicate helper text and letting status items use the full content width.

= 2.6.7 =
* Fixed the Status settings tab registration so WordPress loads the page with the correct permissions.

= 2.6.6 =
* Moved the plugin health/status block to a separate Status settings tab.
* Placed Status as the first settings tab.

= 2.6.5 =
* Added a Gform address field health/status block for Gravity Forms, API key, PostcodeAPI, cache and performance status.
* Added a PostcodeAPI.nu connection test button with example postcode and house number.

= 2.6.4 =
* Improved settings input, password, select and Select2 styling.
* Made settings controls wider and vertically centered for a more consistent layout.
* Improved dropdown arrow visibility.

= 2.6.3 =
* Set the Gform address field feature to disabled by default on install and update.

= 2.6.2 =
* Added this WordPress `readme.txt` so the plugin details modal can show plugin information and changelog entries.

= 2.6.1 =
* Improved dropdown and Select2 styling on the Widgets settings page.
* Renamed the Widget settings tab to Widgets.
* Kept the Gform address API-key field empty in the UI by default.
* Preserved an existing PostcodeAPI.nu API key when the API-key field is left empty while saving.
* Confirmed the default PostcodeAPI.nu endpoint uses the live endpoint: `https://api.postcodeapi.nu/v3/lookup`.

= 2.6.0 =
* Removed the Magic Login module and settings.
* Moved Performance settings to a separate settings page.
* Added Performance presets for normal sites, webshops and heavy websites.

= 2.5.35 =
* Reduced spacing between the Gform address field and Gravity Forms validation messages.
* Hid the empty internal address lookup message so it no longer reserves visual space.

= 2.5.34 =
* Made the address addition field optional for Gravity Forms required-field validation.
* Prevented the optional addition field from being marked as required in address field markup.

= 2.5.33 =
* Refined required-state handling for the Gform address subfields.
* Kept required attributes limited to postcode and house number.

= 2.5.32 =
* Improved Dutch frontend messages for the Gform address field.
* Added/updated the separate Gform address field settings page styling.
* Added safer handling around PostcodeAPI.nu messages and connection failures.

= 2.5.31 =
* Improved "address not found" messaging for the PostcodeAPI.nu lookup.

= 2.5.30 =
* Improved short and human-readable validation messages for incomplete Dutch postcodes.

= 2.5.29 =
* Refined Gform address field layout and read-only styling.

= 2.5.28 =
* Added the separate Gform address field settings page.
* Added PostcodeAPI.nu usage limit settings, cache settings and request-rate controls.
* Added API usage notices for high usage and exhausted limits.

= 2.5.24 - 2.5.27 =
* Iterated on PostcodeAPI.nu connection handling and frontend address lookup behavior.
* Improved request deduplication and error handling for address lookups.

= 2.5.18 - 2.5.23 =
* Improved Gform address field layout, placeholders and manual-input behavior.
* Added controls for hiding labels and customizing address field messages.

= 2.5.9 - 2.5.17 =
* Added and refined the Gravity Forms Dutch address lookup field.
* Added PostcodeAPI.nu based address lookup with caching and frontend updates.

= 2.5.8 =
* Added performance measures and settings for memory limits, revisions, Heartbeat, emojis, embeds and XML-RPC.

= 2.5.7 =
* Version bump and maintenance update.

= 2.5.6 =
* Version bump and maintenance update.

= 2.5.4 =
* Version bump and maintenance update.

= 2.5.1 =
* Version bump and maintenance update.

= 2.5.0 =
* Created the 2.5.0 plugin package.

= 2.4.2 =
* Updated plugin metadata/version handling.

= 2.4.0 =
* Initial 2.4 release line.

= 2.1.4 =
* Integrated the new admin settings page styling.

= 2.1.3 =
* Version bump for the Cloudways-related release line.

= 2.1.2 =
* Version bump.

= 2.1.1 =
* Fixed a case-sensitive require issue.

== Upgrade Notice ==

= 2.7.15 =
Removes the service contract settings and status widget output.
