<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    public static function title(?string $title): string
    {
        return Str::limit(trim((string) $title), 60, '');
    }

    public static function description(?string $excerpt, ?string $body): string
    {
        $text = self::plain($excerpt ?: (string) $body);

        return Str::limit($text, 157, '...');
    }

    public static function keywords(?string $title, ?string $body, ?string $category = null, int $limit = 8): string
    {
        $stopWords = [
            'about', 'after', 'also', 'always', 'because', 'been', 'before', 'being', 'between', 'both',
            'could', 'does', 'doing', 'down', 'each', 'even', 'every', 'from', 'have', 'here', 'into',
            'just', 'like', 'make', 'many', 'more', 'most', 'much', 'must', 'only', 'other', 'over',
            'same', 'should', 'some', 'such', 'than', 'that', 'their', 'them', 'then', 'there', 'these',
            'they', 'this', 'those', 'through', 'under', 'until', 'upon', 'very', 'want', 'were', 'what',
            'when', 'where', 'which', 'while', 'will', 'with', 'would', 'your', 'start', 'using', 'used',
            'dont', 'doesnt', 'cant', 'well', 'good', 'best', 'easy', 'simple', 'small', 'first', 'time',
        ];

        $scores = [];

        // Words in the title count more than words in the content
        foreach ([[(string) $title, 5], [(string) $body, 1]] as [$text, $weight]) {
            preg_match_all('/\p{L}{4,}/u', mb_strtolower(self::plain($text)), $matches);

            foreach ($matches[0] as $word) {
                if (in_array($word, $stopWords, true)) {
                    continue;
                }
                $scores[$word] = ($scores[$word] ?? 0) + $weight;
            }
        }

        arsort($scores);
        $keywords = array_slice(array_keys($scores), 0, $limit);

        if ($category) {
            array_unshift($keywords, mb_strtolower($category));
        }

        return Str::limit(implode(', ', array_unique($keywords)), 255, '');
    }

    // HTML -> plain text (block tags become spaces so words don't stick together)
    private static function plain(?string $html): string
    {
        $html = preg_replace('/<\/(p|h[1-6]|li|div|blockquote|pre)>|<br\s*\/?>/i', ' ', (string) $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}