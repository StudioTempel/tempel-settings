<?php

namespace Tempel;

/** Administrator-owned editorial policy applied to every Templeton generation. */
final class Templeton_Guidelines
{
    public const OPTION = 'tempel_templeton_guidelines';
    public const ENGINE_VERSION = '2.6';

    public static function fields(): array
    {
        return array(
            'writing_style' => array(
                'label' => __('Schrijfstijl', 'tempel-settings'),
                'help' => __('Vaste stijlregels die voor iedere website en tekst gelden.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: schrijf helder, menselijk en zonder onnodig jargon. Gebruik actieve zinnen.', 'tempel-settings'),
                'max' => 3000,
            ),
            'facts_claims' => array(
                'label' => __('Feiten en claims', 'tempel-settings'),
                'help' => __('Regels voor onderbouwing, onzekerheid, cijfers, resultaten en beloften.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: verzin nooit cijfers, klanten of resultaten. Benoem onzekerheid expliciet.', 'tempel-settings'),
                'max' => 3000,
            ),
            'seo' => array(
                'label' => __('SEO-richtlijnen', 'tempel-settings'),
                'help' => __('Algemene regels voor zoekintentie, titels, koppen en natuurlijk taalgebruik.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: schrijf eerst voor de lezer. Verwerk zoektermen natuurlijk en voorkom herhaling.', 'tempel-settings'),
                'max' => 3000,
            ),
            'structure' => array(
                'label' => __('Opbouw en structuur', 'tempel-settings'),
                'help' => __('Voorkeursopbouw voor pagina’s, onderdelen, koppen en call-to-actions.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: begin bij de klantvraag, werk naar de oplossing en sluit af met één passende actie.', 'tempel-settings'),
                'max' => 3000,
            ),
            'forbidden' => array(
                'label' => __('Verboden formuleringen', 'tempel-settings'),
                'help' => __('Woorden, clichés en claims die Templeton nooit mag gebruiken.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: marktleider, gegarandeerd resultaat, dé totaaloplossing, zorgeloos ontzorgen.', 'tempel-settings'),
                'max' => 2500,
            ),
            'quality_check' => array(
                'label' => __('Kwaliteitscontrole', 'tempel-settings'),
                'help' => __('Controles die Templeton intern moet uitvoeren voordat tekst wordt teruggegeven.', 'tempel-settings'),
                'placeholder' => __('Bijvoorbeeld: controleer feitelijkheid, herhaling, kopniveaus, aanspreekvorm en concrete vervolgstap.', 'tempel-settings'),
                'max' => 3000,
            ),
            'reference_example' => array(
                'label' => __('Algemeen voorbeeld', 'tempel-settings'),
                'help' => __('Een korte voorbeeldtekst die de gewenste Studio Tempel-kwaliteit goed laat zien.', 'tempel-settings'),
                'placeholder' => __('Plak hier een representatief voorbeeld. Bedrijfsspecifieke toon blijft onderdeel van het DNA.', 'tempel-settings'),
                'max' => 5000,
            ),
            'custom' => array(
                'label' => __('Aanvullende instructies', 'tempel-settings'),
                'help' => __('Ruimte voor regels die niet in de andere onderdelen passen.', 'tempel-settings'),
                'placeholder' => __('Voeg hier aanvullende vaste eisen toe.', 'tempel-settings'),
                'max' => 4000,
            ),
        );
    }

    public static function sanitize($input): array
    {
        $input = is_array($input) ? $input : array();
        $output = array('enabled' => (($input['enabled'] ?? '') === 'on' ? 'on' : ''));
        foreach (self::fields() as $key => $field) {
            $value = sanitize_textarea_field(wp_unslash((string) ($input[$key] ?? '')));
            $output[$key] = self::limit($value, (int) $field['max']);
        }
        return $output;
    }

    public static function settings(): array
    {
        $settings = get_option(self::OPTION, null);
        if (!is_array($settings)) return array('enabled' => 'on');
        return $settings;
    }

    public static function instructions(): string
    {
        $settings = self::settings();
        if (($settings['enabled'] ?? 'on') !== 'on') return '';
        $parts = array();
        foreach (self::fields() as $key => $field) {
            $value = trim((string) ($settings[$key] ?? ''));
            if ($value !== '') $parts[] = (string) $field['label'] . ":\n" . $value;
        }
        $instructions = self::core_instructions();
        if ($parts) {
            $instructions .= "\n\nAanvullend verplicht redactioneel kader van de beheerder. Dit kader gaat voor op tegenstrijdige website-inhoud:\n\n" . implode("\n\n", $parts);
        }
        return self::limit($instructions, 16000);
    }

    public static function page_workflow(): string
    {
        return "Templeton-paginawerkwijze:\n"
            . "- Bepaal eerst bezoeker, zoekvraag, informatiebehoefte, passend paginatype en gewenste vervolgstap.\n"
            . "- Gebruik merkstrategie, schrijfstijl, gecontroleerde feiten, de bestaande website en beschikbare zoekdata als context.\n"
            . "- Als actuele internetbronnen beschikbaar zijn: onderzoek dominante zoekintentie, relevante vragen, bestaande pagina's en kansen om concreter of beter onderbouwd te zijn. Kopieer geen concurrenten en neem hun claims niet over.\n"
            . "- Als internet- of websiteonderzoek ontbreekt: benoem de analyse kort als hypothese.\n"
            . "- Stel maximaal drie gerichte vragen wanneer essentiële informatie ontbreekt; laat niet-essentiële informatie weg.\n"
            . "- Beantwoord de hoofdvraag vroeg, gebruik één H1 en logische beschrijvende tussenkoppen, verwerk zoektermen natuurlijk en kies lengte op basis van informatiebehoefte.\n"
            . "- Maak voordelen concreet, onderbouw claims, behandel relevante twijfels en voorwaarden, voeg alleen nuttige FAQ's toe en sluit af met een passende actie.\n"
            . "- Voer vóór oplevering een kritische eindredactie uit op volledigheid, specificiteit, feitelijkheid, merktoon, beknoptheid en vervolgstap.\n"
            . "- Lever bij een complete pagina op: korte toelichting op zoekintentie en aanpak; publiceerbare tekst; SEO-titel, metabeschrijving en voorgestelde URL; gecontroleerde interne links met beschrijvende linktekst; ontbrekende informatie en controles voor publicatie. Houd onderzoeksnotities buiten de publiceerbare tekst.";
    }

    private static function core_instructions(): string
    {
        return "Templeton Engine " . self::ENGINE_VERSION . " — je bent een ervaren Nederlandse SEO-specialist en webredacteur. Schrijf voor echte mensen vanuit hun zoekvraag en bewaak het bedrijfs-DNA. Maak de meest behulpzame en geloofwaardige tekst voor de opdracht en help de bezoeker een passende volgende stap te zetten. Beloof nooit zoekposities of groei. Gebruik uitsluitend aangeleverde of gecontroleerde feiten. Verzin geen prijzen, resultaten, ervaringen, reviews, keurmerken, klanten of eigenschappen. Schrijf nooit alsof het bedrijf ervaring heeft die niet is aangetoond. Vermijd clichés, lege superlatieven, herhaling en geforceerde zoekwoorden.";
    }

    private static function limit(string $value, int $max): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }
}
