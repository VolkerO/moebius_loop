<?php

declare(strict_types=1);

namespace Moebius\Aggregation;

use Moebius\Durchgang;

/**
 * Regel, nach der aus auseinanderlaufenden Durchgaengen ein Ergebnis wird.
 *
 * Das ist die Stelle, an der sich entscheidet, ob Perturbation etwas nuetzt.
 * Ohne Verdichtungsregel erzeugt man nur Streuung; mit ihr wird die Streuung
 * zum Filter.
 */
interface Verdichter
{
    /**
     * @param list<Durchgang> $durchgaenge
     *
     * @return list<string>
     */
    public function verdichte(array $durchgaenge): array;
}
