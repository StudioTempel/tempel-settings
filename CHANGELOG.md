# Changelog

Alle noemenswaardige wijzigingen aan Tempel Settings. De geschiedenis tot en met 2.7.21 is gereconstrueerd uit releasebestanden, de bestaande WordPress-changelog en Git-commits.

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
