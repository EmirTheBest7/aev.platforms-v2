<?php

declare(strict_types=1);

namespace Core\Helpers;

/**
 * Preserves the legacy seasonal favicon rule: the white tile (season1) in
 * spring and autumn, the blue/yellow-bordered tile (season3) in summer and
 * winter. Boundaries are the legacy ones (Mar 20, Jun 20, Sep 22, Dec 21).
 */
final class SeasonalIcons
{
    public static function folder(\DateTimeInterface $date): string
    {
        $md = (int) $date->format('md');

        return match (true) {
            $md >= 320 && $md < 620 => 'season1',
            $md >= 620 && $md < 922 => 'season3',
            $md >= 922 && $md < 1221 => 'season1',
            default => 'season3',
        };
    }
}
