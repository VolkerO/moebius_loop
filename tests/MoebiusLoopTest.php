<?php

declare(strict_types=1);

namespace Moebius\Tests;

use Moebius\Agent;
use Moebius\Aggregation\Ueberlebende;
use Moebius\MoebiusLoop;
use Moebius\Parameter;
use Moebius\ParameterSet;
use Moebius\Perturbator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoebiusLoopTest extends TestCase
{
    private function basis(): ParameterSet
    {
        return new ParameterSet(new Parameter('schwelle', 0.5, 0.4));
    }

    /** Meldet 'stabil' immer, 'wackelig' nur unterhalb der Basisschwelle. */
    private function agent(): Agent
    {
        return new class() implements Agent {
            public function laufe(ParameterSet $parameter): array
            {
                return $parameter->wert('schwelle') < 0.5
                    ? ['stabil', 'wackelig']
                    : ['stabil'];
            }
        };
    }

    #[Test]
    public function der_referenzlauf_kommt_dazu(): void
    {
        $bericht = (new MoebiusLoop(new Perturbator(1), new Ueberlebende()))
            ->laufe($this->agent(), $this->basis(), 5);

        self::assertCount(6, $bericht->durchgaenge, 'Referenz plus fünf Varianten.');

        $referenz = $bericht->referenz();
        self::assertNotNull($referenz);
        self::assertSame(0, $referenz->nummer);
        self::assertSame(0.5, $referenz->parameter->wert('schwelle'), 'Der Referenzlauf nutzt die Basiswerte.');
    }

    #[Test]
    public function nur_das_stabile_ueberlebt(): void
    {
        $bericht = (new MoebiusLoop(new Perturbator(20260908), new Ueberlebende()))
            ->laufe($this->agent(), $this->basis(), 12);

        self::assertSame(['stabil'], $bericht->ergebnis);
        self::assertContains('wackelig', $bericht->vereinigung());
    }

    #[Test]
    public function der_bericht_haelt_den_seed_fest(): void
    {
        $bericht = (new MoebiusLoop(new Perturbator(4242), new Ueberlebende()))
            ->laufe($this->agent(), $this->basis(), 2);

        self::assertSame(4242, $bericht->seed);
    }

    #[Test]
    public function ohne_variante_kein_lauf(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new MoebiusLoop(new Perturbator(1), new Ueberlebende()))
            ->laufe($this->agent(), $this->basis(), 0);
    }
}
