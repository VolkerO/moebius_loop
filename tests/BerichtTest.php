<?php

declare(strict_types=1);

namespace Moebius\Tests;

use Moebius\Bericht;
use Moebius\Durchgang;
use Moebius\Parameter;
use Moebius\ParameterSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BerichtTest extends TestCase
{
    /** @param list<string> $aussagen */
    private function durchgang(int $nummer, array $aussagen, bool $referenz = false): Durchgang
    {
        return new Durchgang($nummer, new ParameterSet(new Parameter('x', 1.0, 0.1)), $aussagen, $referenz);
    }

    #[Test]
    public function artefakte_sind_was_der_referenzlauf_zu_viel_behauptet(): void
    {
        // Das ist die Kennzahl, um die es dem Verfahren geht.
        $bericht = new Bericht(
            [
                $this->durchgang(0, ['echt', 'artefakt'], referenz: true),
                $this->durchgang(1, ['echt']),
            ],
            ['echt'],
            1,
        );

        self::assertSame(['artefakt'], $bericht->artefakte());
    }

    #[Test]
    public function stabilitaet_misst_den_anteil_der_ueberlebenden(): void
    {
        $bericht = new Bericht(
            [
                $this->durchgang(0, ['a', 'b', 'c', 'd'], referenz: true),
                $this->durchgang(1, ['a']),
            ],
            ['a'],
            1,
        );

        self::assertSame(0.25, $bericht->stabilitaet());
    }

    #[Test]
    public function ohne_aussagen_ist_die_stabilitaet_null_statt_division_durch_null(): void
    {
        self::assertSame(0.0, (new Bericht([$this->durchgang(0, [])], [], 1))->stabilitaet());
    }

    #[Test]
    public function haeufigkeit_zaehlt_ueber_alle_durchgaenge(): void
    {
        $bericht = new Bericht(
            [
                $this->durchgang(0, ['a', 'b'], referenz: true),
                $this->durchgang(1, ['a']),
                $this->durchgang(2, ['a']),
            ],
            ['a'],
            1,
        );

        self::assertSame(['a' => 3, 'b' => 1], $bericht->haeufigkeit());
    }
}
