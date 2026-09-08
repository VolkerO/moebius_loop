<?php

declare(strict_types=1);

namespace Moebius;

/**
 * Ein Agent, der unter einer gegebenen Parametrierung eine Menge von Aussagen liefert.
 *
 * „Aussage" ist bewusst offen gehalten: ein Befund aus einem Code-Review, eine
 * Empfehlung, ein extrahierter Fakt. Entscheidend ist nur, dass sie sich als
 * Zeichenkette vergleichen laesst — darauf setzt die Verdichtung auf.
 *
 * Die Implementierung kann ein LLM-Aufruf sein, eine Heuristik oder ein
 * deterministisches Modell. Der Loop interessiert sich nicht dafuer.
 */
interface Agent
{
    /**
     * @return list<string> Aussagen dieses Durchgangs. Reihenfolge ist unerheblich,
     *                      Duplikate werden vom Loop zusammengefasst.
     */
    public function laufe(ParameterSet $parameter): array;
}
