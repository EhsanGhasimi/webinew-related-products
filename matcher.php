<?php
defined('ABSPATH') || exit;
final class WRP_Matcher {
    public static function tokens($text) {
        $text = html_entity_decode(strip_tags(preg_replace('/\[[^\]]*\]/u', ' ', $text)), ENT_QUOTES, 'UTF-8');
        $text = strtr($text, ['ي'=>'ی','ى'=>'ی','ك'=>'ک', '‌'=>' ', 'ۀ'=>'ه', 'ة'=>'ه', 'أ'=>'ا', 'إ'=>'ا', 'آ'=>'ا']);
        $text = strtolower(preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text));
        $stop = array_flip(explode(' ', 'از به در با و یا که را این ان برای یک ها های می شود شده است هستند کنید خرید قیمت محصول محصولات بهترین تولید کننده تولیدکننده شرکت معرفی راهنمای مدل درباره بیشتر همچنین استفاده مورد انواع کیفیت ایران تماس بیمارستانی'));
        $out = [];
        foreach (preg_split('/[^\p{L}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (preg_match('/^.{2,}$/u', $word) && !isset($stop[$word])) $out[$word] = min(8, ($out[$word] ?? 0) + 1);
        }
        return $out;
    }
    public static function rank($title, $body, $docs, $limit = 7) {
        $title = self::tokens($title);
        $body = self::tokens($body);
        $df = [];
        foreach ($docs as $tokens) foreach ($tokens as $word => $count) $df[$word] = ($df[$word] ?? 0) + 1;
        $scores = [];
        foreach ($docs as $id => $tokens) {
            $score = 0; $hits = 0; $title_hits = 0;
            foreach ($tokens as $word => $count) {
                if (!isset($title[$word]) && !isset($body[$word])) continue;
                $hits++;
                if (isset($title[$word])) $title_hits++;
                $weight = log(1 + count($docs) / ($df[$word] ?? 1));
                $score += $weight * ((isset($title[$word]) ? 8 : 0) + min(3, $body[$word] ?? 0));
            }
            // A title topic or multiple body terms is required; no random fallback.
            if ($title_hits > 0 || $hits >= 2) $scores[$id] = $score;
        }
        uksort($scores, function ($a, $b) use ($scores) { return ($scores[$b] <=> $scores[$a]) ?: ($a <=> $b); });
        return array_slice(array_keys($scores), 0, $limit);
    }
}
