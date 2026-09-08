<?php

declare(strict_types=1);

namespace Moebius;

/**
 * Ein einzelner Stellparameter mit Basiswert und zulaessiger Bandbreite.
 *
 * Die Bandbreite ist relativ angegeben: 0.2 bedeutet, dass der Wert um bis zu
 * 20 Prozent nach oben oder unten verschoben werden darf. Damit bleibt die
 * Perturbation an die Groessenordnung des Parameters gekoppelt, statt eine
 * absolute Schrittweite ueber alle Parameter zu stuelpen.
 */
final readonly class Parameter
{
    public function __construct(
        public string $name,
        public float $basis,
        public float $bandbreite,
    ) {
        if ($name === '') {
            throw new \InvalidArgumentException('Parametername darf nicht leer sein.');
        }

        if ($bandbreite < 0.0 || $bandbreite > 1.0) {
            throw new \InvalidArgumentException(
                sprintf('Bandbreite von "%s" muss zwischen 0.0 und 1.0 liegen, %.2f gegeben.', $name, $bandbreite),
            );
        }
    }

    /** Kleinster Wert, den dieser Parameter annehmen kann. */
    public function untergrenze(): float
    {
        return $this->basis - abs($this->basis) * $this->bandbreite;
    }

    /** Groesster Wert, den dieser Parameter annehmen kann. */
    public function obergrenze(): float
    {
        return $this->basis + abs($this->basis) * $this->bandbreite;
    }
}
