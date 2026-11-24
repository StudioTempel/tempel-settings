# Tempel admin UI

Deze stijlgids is de bron voor alle beheerpagina's van Tempel. Nieuwe
schermen gebruiken uitsluitend de tokens en componenten hieronder. Bestaande
schermen worden stapsgewijs omgezet; functionaliteit en opgeslagen instellingen
blijven daarbij ongewijzigd.

## Principes

1. Toon eerst de huidige status en daarna pas de instelling.
2. Eén primaire actie per scherm of processtap.
3. Verberg technische opties onder **Geavanceerd**.
4. Gebruik gewone, korte Nederlandse labels.
5. Kleur ondersteunt de betekenis, maar is nooit de enige statusindicatie.
6. Alle bediening is bruikbaar met toetsenbord en op 320% zoom.

## Design tokens

Alle tokens krijgen in CSS het voorvoegsel `--tempel-ui-`.

### Kleur

| Token | Waarde | Gebruik |
| --- | --- | --- |
| `color-canvas` | `#000000` | Pagina-achtergrond |
| `color-surface` | `#171717` | Kaarten en panelen |
| `color-surface-raised` | `#222222` | Verhoogde of geopende onderdelen |
| `color-border` | `#383838` | Standaard rand en scheiding |
| `color-text` | `#ffffff` | Primaire tekst |
| `color-text-muted` | `#a3a3a3` | Toelichting en metadata |
| `color-accent` | `#f3f438` | Primaire actie en actieve toestand |
| `color-accent-text` | `#000000` | Tekst op accentkleur |
| `color-success` | `#28d17c` | Geslaagd en gezond |
| `color-warning` | `#f0b429` | Aandacht vereist |
| `color-danger` | `#ff5a3d` | Fout of destructieve actie |
| `color-info` | `#3498db` | Neutrale procesinformatie |

Gebruik voor lichte statusvlakken afgeleide transparante varianten van deze vier
statuskleuren. Voeg geen nieuwe statuskleur toe per module.

### Typografie

De beheerinterface gebruikt de WordPress systeemfont-stack. De schaal bestaat uit:

| Token | Grootte / regelhoogte | Gebruik |
| --- | --- | --- |
| `text-xs` | `12px / 1.4` | Badge en technische metadata |
| `text-sm` | `14px / 1.5` | Toelichting en tabelmetadata |
| `text-md` | `16px / 1.5` | Standaard tekst, labels en knoppen |
| `text-lg` | `20px / 1.35` | Kaarttitel |
| `text-xl` | `28px / 1.2` | Sectietitel |
| `text-2xl` | `36px / 1.1` | Paginatitel |

Gebruik alleen gewichten `400`, `600` en `700`.

### Ruimte

De basismaat is 4px:

`space-1: 4px`, `space-2: 8px`, `space-3: 12px`, `space-4: 16px`,
`space-5: 24px`, `space-6: 32px`, `space-7: 48px`, `space-8: 64px`.

Vrije waarden zoals 15, 18, 22, 25, 30 en 35px worden niet in nieuwe
componenten gebruikt.

### Vorm en beweging

- Klein element: `radius-sm: 8px`
- Veld, knop en status: `radius-md: 12px`
- Kaart of dialoog: `radius-lg: 16px`
- Pil of schakelaar: `radius-pill: 999px`
- Focusring: `0 0 0 3px rgba(243, 244, 56, .35)`
- Snelle overgang: `150ms ease`
- Normale overgang: `220ms ease`

Respecteer altijd `prefers-reduced-motion`.

## Layout

- Maximale inhoudsbreedte: `1120px`.
- Pagina-padding: 32px, op smalle schermen 20px.
- Secties staan 48px uit elkaar.
- Kaarten gebruiken standaard 24px padding.
- Instellingsrijen tonen het label links en de bediening rechts. Alle schakelaars
  staan tegen de rechterzijde van de bedieningskolom.
- Op smalle schermen vallen label en bediening terug naar één kolom.
- Twee velden naast elkaar worden alleen gebruikt voor korte, logisch gekoppelde
  waarden zoals start- en einddatum.
- Iedere nieuwe Tempel-beheerpagina gebruikt automatisch de centrale thema-tokens.
  Het lichte thema is standaard; het donkere thema blijft via Beheer → Uiterlijk beschikbaar.

## Componenten

### Paginaheader

Bevat een paginatitel, één zin toelichting en optioneel één primaire actie. Geen
grote marketingachtige hero in beheerpagina's.

### Statussamenvatting

Een compacte rij of kaart met statusicoon, label, korte waarde en optionele link.
De vaste toestanden zijn `neutral`, `running`, `success`, `warning` en `error`.

### Kaart

Eén basiselement voor instellingen, processtappen en samenvattingen. Varianten
verschillen alleen in inhoud en statusrand; niet in willekeurige padding of
afronding.

### Instellingsrij

Bestaat uit label, korte uitleg en één bediening. Afhankelijke velden verschijnen
pas wanneer de hoofdschakelaar actief is. Technische hulptekst staat niet permanent
in beeld.

### Knoppen

- `primary`: één hoofdactie per context.
- `secondary`: veilige nevengeschikte actie.
- `tertiary`: tekstactie zonder gevuld vlak.
- `danger`: verwijderen of onomkeerbare wijziging.

Alle standaardknoppen zijn 40px hoog. Alleen iconen krijgen een toegankelijke
naam. Destructieve acties vragen bevestiging met een omschrijving van het gevolg.

### Formuliervelden

Alle tekstvelden, selects, datums en tijden zijn 44px hoog, gebruiken dezelfde
rand, achtergrond, focusring en foutweergave. Helptekst staat direct onder het
veld. Fouten staan bij het betreffende veld en daarnaast bovenaan als samenvatting.

### Schakelaar

Alleen voor een instelling die direct aan of uit kan. De zichtbare tekst is het
veldlabel; er staan geen losse labels `Yes` en `No` in de schakelaar.

### Melding

Varianten: `info`, `success`, `warning`, `error`. Iedere melding heeft een korte
titel, optionele toelichting en maximaal één actie.

### Voortgang

Toont fase, percentage, actuele activiteit en eventueel verstreken tijd. De
toestanden zijn `queued`, `running`, `paused`, `success` en `error`. Een pagina die
opnieuw opent, leest dezelfde taakstatus en hervat de live weergave.

### Tabel en lege toestand

Tabellen worden alleen gebruikt voor vergelijkbare records zoals bestanden of
logregels. Acties staan rechts. Op mobiel wordt een record een kaart. Een lege
toestand vertelt wat er ontbreekt en toont alleen een actie wanneer die nuttig is.

### Dialoog

Gebruik voor bevestiging, mapkeuze en destructieve acties. Geen formulier in een
dialoog wanneer een gewone pagina duidelijker is.

## Schermindeling

De plugin gebruikt vier hoofdbestemmingen:

1. **Overzicht** – gezondheid, waarschuwingen en recente taken.
2. **Instellingen** – Beveiliging, Formulieren, Beheer en Uiterlijk.
3. **Gereedschap** – Back-ups, Verhuizen, Bestanden en tests.
4. **Logboek** – lopende en afgeronde gebeurtenissen.

Subnavigatie binnen een bestemming wordt gebruikt wanneer er meer dan drie
duidelijke onderdelen zijn. Lange pagina's met veel losstaande instellingen
worden vermeden.

## Technische invoering

1. Voeg `dist/css/admin-tokens.css` en `dist/css/admin-components.css` toe.
2. Bouw herbruikbare PHP viewcomponenten voor header, kaart, veld, status, melding,
   voortgang en dialoog.
3. Zet eerst Overzicht, Back-ups en Verhuizen om.
4. Zet daarna de instellingenpagina's om.
5. Verwijder pas daarna oude selectors uit `widget-settings.css` en
   `settings-overrides.css`.

Tijdens de overgang mogen oude en nieuwe CSS naast elkaar bestaan, maar nieuwe
schermen krijgen geen aanvullende eenmalige stijlen in `settings-overrides.css`.
