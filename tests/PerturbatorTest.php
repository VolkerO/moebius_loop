<?php

declare(strict_types=1);

namespace Moebius\Tests;

use Moebius\Parameter;
use Moebius\ParameterSet;
use Moebius\Perturbator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PerturbatorTest extends TestCase
{
    private function basis(): ParameterSet
    {
        return new ParameterSet(
            new Parameter('schwelle', 0.60, 0.25),
            new Parameter('gewicht', 10.0, 0.10),
        );
    }

    #[Test]
    public function gleicher_seed_erzeugt_identische_folge(): void
    {
        // Die wichtigste Eigenschaft des Verfahrens: Ein Lauf, der sich nicht
        // wiederholen laesst, taugt nicht als Nachweis.
        $a = new Perturbator(4711);
        $b = new Perturbator(4711);

        for ($i = 0; $i < 5; ++$i) {
            self::assertSame(
                $a->verschiebe($this->basis())->werte(),
                $b->verschiebe($this->basis())->werte(),
                sprintf('Durchgang %d weicht ab.', $i),
            );
        }
    }

    #[Test]
    public function verschiedene_seeds_erzeugen_verschiedene_folgen(): void
    {
        self::assertNotSame(
            (new Perturbator(1))->verschiebe($this->basis())->werte(),
            (new Perturbator(2))->verschiebe($this->basis())->werte(),
        );
    }

    #[Test]
    public function werte_bleiben_in_der_bandbreite(): void
    {
        $perturbator = new Perturbator(99);
        $basis = $this->basis();

        for ($i = 0; $i < 200; ++$i) {
            $verschoben = $perturbator->verschiebe($basis);

            foreach ($basis->alle() as $name => $original) {
                $wert = $verschoben->wert($name);
                self::assertGreaterThanOrEqual($original->untergrenze(), $wert);
                self::assertLessThanOrEqual($original->obergrenze(), $wert);
            }
        }
    }

    #[Test]
    public function bandbreite_null_haelt_den_parameter_fest(): void
    {
        $fix = new ParameterSet(new Parameter('konstante', 42.0, 0.0));
        $verschoben = (new Perturbator(7))->verschiebe($fix);

        self::assertSame(42.0, $verschoben->wert('konstante'));
    }

    #[Test]
    public function die_basis_bleibt_unangetastet(): void
    {
        $basis = $this->basis();
        (new Perturbator(3))->verschiebe($basis);

        self::assertSame(0.60, $basis->wert('schwelle'));
    }
}
