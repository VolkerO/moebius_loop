<?php

declare(strict_types=1);

namespace Moebius\Aggregation;

use Moebius\Durchgang;

/**
 * Behaelt nur, was in jedem einzelnen Durchgang vorkam — die Schnittmenge.
 *
 * Das ist die strengste Auslegung und der eigentliche Punkt des Verfahrens:
 * Was jede Verschiebung der Parameter uebersteht, haengt nicht an der
 * Parametrierung. Was verschwindet, sobald man an den Stellschrauben dreht,
 * war ein Artefakt der Einstellung — nicht der Sache.
 *
 * Preis dieser Strenge: Sie ist unbarmherzig. Ein einziger Ausreisserlauf
 * kann eine richtige Aussage kassieren. Wer das nicht will, nimmt Mehrheit.
 */
final readonly class Ueberlebende implements Verdichter
{
    public function verdichte(array $durchgaenge): array
    {
        if ($durchgaenge === []) {
            return [];
        }

        $schnitt = $durchgaenge[0]->aussagen;

        foreach (array_slice($durchgaenge, 1) as $d) {
            $schnitt = array_values(array_intersect($schnitt, $d->aussagen));

            if ($schnitt === []) {
                break;
            }
        }

        return $schnitt;
    }
}
