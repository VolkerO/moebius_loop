<?php

declare(strict_types=1);

namespace Moebius\Tests\Aggregation;

use Moebius\Aggregation\Ueberlebende;
use Moebius\Durchgang;
use Moebius\Parameter;
use Moebius\ParameterSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UeberlebendeTest extends TestCase
{
    /** @param list<string> $aussagen */
    private function durchgang(int $nummer, array $aussagen): Durchgang
    {
        return new Durchgang($nummer, new ParameterSet(new Parameter('x', 1.0, 0.1)), $aussagen);
    }

    #[Test]
    public function behaelt_nur_was_in_jedem_durchgang_vorkommt(): void
    {
        $ergebnis = (new Ueberlebende())->verdichte([
            $this->durchgang(0, ['a', 'b', 'c']),
            $this->durchgang(1, ['b', 'c', 'd']),
            $this->durchgang(2, ['c', 'b', 'e']),
        ]);

        sort($ergebnis);
        self::assertSame(['b', 'c'], $ergebnis);
    }

    #[Test]
    public function ein_einziger_ausreisser_kassiert_die_aussage(): void
    {
        // Der bewusst in Kauf genommene Preis der Strenge.
        $ergebnis = (new Ueberlebende())->verdichte([
            $this->durchgang(0, ['a']),
            $this->durchgang(1, ['a']),
            $this->durchgang(2, []),
        ]);

        self::assertSame([], $ergebnis);
    }

    #[Test]
    public function ohne_durchgaenge_kein_ergebnis(): void
    {
        self::assertSame([], (new Ueberlebende())->verdichte([]));
    }
}
