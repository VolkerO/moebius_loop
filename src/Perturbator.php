<?php

declare(strict_types=1);

namespace Moebius;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Verschiebt die Parameter eines Sets zufaellig innerhalb ihrer Bandbreite.
 *
 * Der Zufall ist bewusst gesetzt statt beliebig: Ein Lauf, der sich nicht
 * wiederholen laesst, ist als Nachweis wertlos. Ueber den Seed ist jede
 * Perturbationsfolge exakt reproduzierbar.
 */
final class Perturbator
{
    private readonly Randomizer $zufall;

    public function __construct(private readonly int $seed)
    {
        $this->zufall = new Randomizer(new Mt19937($seed));
    }

    public function seed(): int
    {
        return $this->seed;
    }

    /**
     * Erzeugt eine verschobene Fassung des Sets.
     *
     * Parameter mit Bandbreite 0.0 bleiben unveraendert — so lassen sich
     * einzelne Groessen bewusst festhalten, waehrend andere variieren.
     */
    public function verschiebe(ParameterSet $set): ParameterSet
    {
        $verschoben = [];

        foreach ($set->alle() as $p) {
            $verschoben[] = $p->bandbreite === 0.0
                ? $p
                : new Parameter($p->name, $this->zufall->getFloat($p->untergrenze(), $p->obergrenze()), $p->bandbreite);
        }

        return new ParameterSet(...$verschoben);
    }
}
