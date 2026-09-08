<?php

declare(strict_types=1);

namespace Moebius;

/**
 * Unveraenderliche Sammlung von Parametern.
 *
 * Ein Set beschreibt die Konfiguration eines einzelnen Durchgangs. Der
 * Perturbator erzeugt daraus neue Sets; das urspruengliche bleibt unangetastet,
 * damit der Referenzlauf jederzeit reproduzierbar bleibt.
 */
final readonly class ParameterSet
{
    /** @var array<string, Parameter> */
    private array $parameter;

    public function __construct(Parameter ...$parameter)
    {
        $indiziert = [];
        foreach ($parameter as $p) {
            if (isset($indiziert[$p->name])) {
                throw new \InvalidArgumentException(sprintf('Parameter "%s" ist doppelt vergeben.', $p->name));
            }
            $indiziert[$p->name] = $p;
        }

        $this->parameter = $indiziert;
    }

    public function wert(string $name): float
    {
        return ($this->parameter[$name] ?? throw new \OutOfBoundsException(
            sprintf('Unbekannter Parameter "%s".', $name),
        ))->basis;
    }

    public function hat(string $name): bool
    {
        return isset($this->parameter[$name]);
    }

    /** @return array<string, Parameter> */
    public function alle(): array
    {
        return $this->parameter;
    }

    /** @return array<string, float> Momentaufnahme der Werte, etwa fuer Protokolle. */
    public function werte(): array
    {
        return array_map(static fn (Parameter $p): float => $p->basis, $this->parameter);
    }
}
