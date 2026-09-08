<?php

declare(strict_types=1);

namespace Moebius\Tests\Aggregation;

use Moebius\Aggregation\Mehrheit;
use Moebius\Durchgang;
use Moebius\Parameter;
use Moebius\ParameterSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MehrheitTest extends TestCase
{
    /** @param list<string> $aussagen */
    private function durchgang(int $nummer, array $aussagen): Durchgang
    {
        return new Durchgang($nummer, new ParameterSet(new Parameter('x', 1.0, 0.1)), $aussagen);
    }

    #[Test]
    public function haelt_die_schwelle_ein(): void
    {
        $durchgaenge = [
            $this->durchgang(0, ['a', 'b']),
            $this->durchgang(1, ['a', 'b']),
            $this->durchgang(2, ['a']),
            $this->durchgang(3, ['a']),
        ];

        // 'a' in 4/4, 'b' in 2/4 — bei Schwelle 0.66 faellt 'b' heraus.
        self::assertSame(['a'], (new Mehrheit(0.66))->verdichte($durchgaenge));
        self::assertSame(['a', 'b'], (new Mehrheit(0.5))->verdichte($durchgaenge));
    }

    #[Test]
    public function schwelle_ausserhalb_des_bereichs_wird_abgewiesen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Mehrheit(1.5);
    }
}
