<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Generates an original, on-brand SVG graphic for a news article - the same
 * hand-drawn style used for earlier articles (gradient background, category
 * pill, team names, a large stat/score in the corner, footer byline), but
 * parametrised so PublishNewsArticle can produce one automatically per run.
 *
 * Colours are picked from a fixed on-brand palette, hash-seeded by slug so
 * runs don't all look identical. Text is escaped with htmlspecialchars,
 * which only ever produces the standard XML-safe entities (&amp; &lt; &gt;
 * &quot; &#039;) - unlike raw HTML entities such as &middot;, these are
 * valid inside SVG/XML and won't break the <img> embed.
 */
class NewsGraphicGenerator
{
    private const GRADIENTS = [
        ['#1B458F', '#4A2440', '#7A263A'],
        ['#0F3D3E', '#1B5E52', '#2E8B57'],
        ['#231651', '#4B2E83', '#7A3E9D'],
        ['#0B2545', '#134074', '#8DA9C4'],
        ['#3E1F1F', '#7A2E2E', '#B94A48'],
        ['#0D1B2A', '#1B263B', '#415A77'],
    ];

    public function generate(
        string $slug,
        string $categoryLabel,
        string $line1,
        ?string $line2,
        string $big,
        string $footer
    ): string {
        $seed = hexdec(substr(md5($slug), 0, 6));
        $gradient = self::GRADIENTS[$seed % count(self::GRADIENTS)];

        $svg = $this->buildSvg($categoryLabel, $line1, $line2, $big, $footer, $gradient);

        $path = "news-images/{$slug}.svg";
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function buildSvg(string $categoryLabel, string $line1, ?string $line2, string $big, string $footer, array $gradient): string
    {
        [$c1, $c2, $c3] = $gradient;

        $categoryLabel = $this->escape($categoryLabel);
        $line1 = $this->escape($line1);
        $footer = $this->escape($footer);
        $big = $this->escape($big);

        $labelWidth = max(150, strlen($categoryLabel) * 10 + 40);
        $labelCenter = 64 + intdiv($labelWidth, 2);

        $line2Svg = $line2 !== null && $line2 !== ''
            ? '<text x="64" y="400" font-family="Georgia, \'Times New Roman\', serif" font-size="42" font-weight="700" fill="#ffffff" fill-opacity="0.78">'.$this->escape($line2).'</text>'
            : '';

        $bigFontSize = strlen($big) > 8 ? 64 : 140;

        $bigSvg = $big !== ''
            ? '<text x="1136" y="380" font-family="Arial, Helvetica, sans-serif" font-size="'.$bigFontSize.'" font-weight="800" fill="#ffffff" text-anchor="end">'.$big.'</text>'
            : '';

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" role="img" aria-label="{$line1} {$categoryLabel} graphic">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="{$c1}"/>
              <stop offset="55%" stop-color="{$c2}"/>
              <stop offset="100%" stop-color="{$c3}"/>
            </linearGradient>
            <radialGradient id="glow" cx="50%" cy="35%" r="65%">
              <stop offset="0%" stop-color="#ffffff" stop-opacity="0.16"/>
              <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
            </radialGradient>
          </defs>

          <rect width="1200" height="630" fill="url(#bg)"/>
          <rect width="1200" height="630" fill="url(#glow)"/>

          <circle cx="600" cy="330" r="230" fill="none" stroke="#ffffff" stroke-opacity="0.14" stroke-width="3"/>
          <circle cx="600" cy="330" r="6" fill="#ffffff" fill-opacity="0.35"/>
          <line x1="600" y1="80" x2="600" y2="580" stroke="#ffffff" stroke-opacity="0.12" stroke-width="3"/>

          <rect x="64" y="56" width="{$labelWidth}" height="40" rx="7" fill="#0A1620" fill-opacity="0.4"/>
          <text x="{$labelCenter}" y="82" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="700" letter-spacing="1.5" fill="#ffffff" text-anchor="middle">{$categoryLabel}</text>

          <text x="64" y="340" font-family="Georgia, 'Times New Roman', serif" font-size="52" font-weight="700" fill="#ffffff">{$line1}</text>
          {$line2Svg}

          {$bigSvg}

          <rect x="64" y="546" width="1072" height="1.5" fill="#ffffff" fill-opacity="0.18"/>
          <text x="64" y="588" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="600" letter-spacing="1" fill="#ffffff" fill-opacity="0.8">{$footer}</text>
        </svg>
        SVG;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
