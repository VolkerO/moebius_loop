<?php

declare(strict_types=1);

namespace Moebius\Aggregation;

use Moebius\Durchgang;

/**
 * Behaelt, was in mindestens einem festgelegten Anteil der Durchgaenge vorkam.
 *
 * Die nachgiebige Variante. Sie dient hier vor allem dem Vergleich: Wie viel
 * mehr bleibt uebrig, wenn man die Schwelle von „alle" auf „zwei Drittel"
 * senkt — und ist das Mehr belastbar oder nur bequem?
 */
final readonly class Mehrheit implements Verdichter
{
    public function __construct(private float $schwelle = 0.5)
    {
        if ($schwelle <= 0.0 || $schwelle > 1.0) {
            throw new \InvalidArgumentException(
                sprintf('Schwelle muss zwischen 0 (exklusiv) und 1 liegen, %.2f gegeben.', $schwelle),
            );
        }
    }

    public function verdichte(array $durchgaenge): array
    {
        if ($durchgaenge === []) {
            return [];
        }

        $zaehler = [];
        foreach ($durchgaenge as $d) {
            foreach ($d->aussagen as $a) {
                $zaehler[$a] = ($zaehler[$a] ?? 0) + 1;
            }
        }

        $noetig = $this->schwelle * count($durchgaenge);

        return array_keys(array_filter(
            $zaehler,
            static fn (int $n): bool => (float) $n >= $noetig,
        ));
    }
}
