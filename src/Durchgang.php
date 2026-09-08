<?php

declare(strict_types=1);

namespace Moebius;

/**
 * Ergebnis eines einzelnen Laufs: die verwendete Parametrierung und was dabei herauskam.
 */
final readonly class Durchgang
{
    /** @var list<string> */
    public array $aussagen;

    /**
     * @param list<string> $aussagen
     */
    public function __construct(
        public int $nummer,
        public ParameterSet $parameter,
        array $aussagen,
        public bool $istReferenz = false,
    ) {
        $this->aussagen = array_values(array_unique($aussagen));
    }
}
