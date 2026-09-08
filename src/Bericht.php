<?php

declare(strict_types=1);

namespace Moebius;

/**
 * Ergebnis eines vollstaendigen Laufs samt der Kennzahlen, die ihn beurteilbar machen.
 *
 * Die wichtigste Zahl steht in artefakte(): was der ungestoerte Lauf behauptet
 * haette und die Perturbation kassiert hat. Genau das ist der Ertrag des
 * Verfahrens — alles andere ist Beiwerk.
 */
final readonly class Bericht
{
    /**
     * @param list<Durchgang> $durchgaenge
     * @param list<string>    $ergebnis
     */
    public function __construct(
        public array $durchgaenge,
        public array $ergebnis,
        public int $seed,
    ) {
    }

    public function referenz(): ?Durchgang
    {
        foreach ($this->durchgaenge as $d) {
            if ($d->istReferenz) {
                return $d;
            }
        }

        return null;
    }

    /** @return list<string> Jede Aussage, die irgendein Durchgang hervorgebracht hat. */
    public function vereinigung(): array
    {
        $alle = [];
        foreach ($this->durchgaenge as $d) {
            foreach ($d->aussagen as $a) {
                $alle[$a] = true;
            }
        }

        return array_keys($alle);
    }

    /**
     * Anteil der Aussagen, die die Verdichtung ueberstanden haben.
     *
     * Niedrige Werte sind kein Makel des Verfahrens, sondern sein Befund: Die
     * Ergebnisse haengen stark an der Parametrierung.
     */
    public function stabilitaet(): float
    {
        $gesamt = count($this->vereinigung());

        return $gesamt === 0 ? 0.0 : count($this->ergebnis) / $gesamt;
    }

    /**
     * Was der Referenzlauf geliefert haette, das die Verdichtung verworfen hat.
     *
     * @return list<string>
     */
    public function artefakte(): array
    {
        $referenz = $this->referenz();

        return $referenz === null ? [] : array_values(array_diff($referenz->aussagen, $this->ergebnis));
    }

    /**
     * Wie oft jede Aussage aufgetreten ist, absteigend sortiert.
     *
     * @return array<string, int>
     */
    public function haeufigkeit(): array
    {
        $zaehler = [];
        foreach ($this->durchgaenge as $d) {
            foreach ($d->aussagen as $a) {
                $zaehler[$a] = ($zaehler[$a] ?? 0) + 1;
            }
        }

        arsort($zaehler);

        return $zaehler;
    }
}
