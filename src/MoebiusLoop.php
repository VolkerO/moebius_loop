<?php

declare(strict_types=1);

namespace Moebius;

use Moebius\Aggregation\Verdichter;

/**
 * Legt eine Perturbationsschicht ueber einen Agenten.
 *
 * Ablauf: ein Referenzlauf mit den Basiswerten, danach n Laeufe mit zufaellig
 * innerhalb der Bandbreiten verschobenen Parametern. Der Verdichter entscheidet,
 * was davon uebrig bleibt.
 *
 * Der Referenzlauf ist nicht Zierde: Ohne ihn laesst sich nicht sagen, was das
 * Verfahren gegenueber einem gewoehnlichen Durchlauf ueberhaupt leistet.
 */
final readonly class MoebiusLoop
{
    public function __construct(
        private Perturbator $perturbator,
        private Verdichter $verdichter,
    ) {
    }

    public function laufe(Agent $agent, ParameterSet $basis, int $varianten): Bericht
    {
        if ($varianten < 1) {
            throw new \InvalidArgumentException(
                sprintf('Es braucht mindestens eine Variante, %d gefordert.', $varianten),
            );
        }

        $durchgaenge = [new Durchgang(0, $basis, $agent->laufe($basis), istReferenz: true)];

        for ($i = 1; $i <= $varianten; ++$i) {
            $verschoben = $this->perturbator->verschiebe($basis);
            $durchgaenge[] = new Durchgang($i, $verschoben, $agent->laufe($verschoben));
        }

        return new Bericht(
            $durchgaenge,
            $this->verdichter->verdichte($durchgaenge),
            $this->perturbator->seed(),
        );
    }
}
