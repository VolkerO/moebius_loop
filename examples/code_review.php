<?php

declare(strict_types=1);

use Moebius\Aggregation\Mehrheit;
use Moebius\Aggregation\Ueberlebende;
use Moebius\MoebiusLoop;
use Moebius\Parameter;
use Moebius\ParameterSet;
use Moebius\Perturbator;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/ReviewAgent.php';

$agent = new Moebius\Beispiel\ReviewAgent();

// Basis: Meldeschwelle 0.60, Streuung 0.30 — beide duerfen um 25 Prozent wandern.
$basis = new ParameterSet(
    new Parameter('schwelle', 0.60, 0.25),
    new Parameter('streuung', 0.30, 0.25),
);

$seed = 20260908;
$varianten = 8;

echo "MÖBIUS-LOOP — Beispiel: Code-Review-Agent\n";
echo str_repeat('=', 72), "\n";
printf("Seed %d · %d Varianten plus Referenzlauf\n\n", $seed, $varianten);

$streng = (new MoebiusLoop(new Perturbator($seed), new Ueberlebende()))
    ->laufe($agent, $basis, $varianten);

echo "Durchgänge im Einzelnen\n";
echo str_repeat('-', 72), "\n";
foreach ($streng->durchgaenge as $d) {
    printf(
        "%-12s schwelle=%.3f streuung=%.3f → %d Befunde\n",
        $d->istReferenz ? '  Referenz' : sprintf('  Variante %d', $d->nummer),
        $d->parameter->wert('schwelle'),
        $d->parameter->wert('streuung'),
        count($d->aussagen),
    );
}

$referenz = $streng->referenz();
assert($referenz !== null);

echo "\nErgebnis der strengen Verdichtung (nur was jede Variante überlebt)\n";
echo str_repeat('-', 72), "\n";
foreach ($streng->ergebnis as $befund) {
    echo "  ✓ ", $befund, "\n";
}

echo "\nVom Referenzlauf behauptet, von der Perturbation kassiert\n";
echo str_repeat('-', 72), "\n";
$artefakte = $streng->artefakte();
if ($artefakte === []) {
    echo "  (keine — der Referenzlauf war robust)\n";
}
foreach ($artefakte as $befund) {
    printf("  ✗ %-56s (in %d von %d Läufen)\n", $befund, $streng->haeufigkeit()[$befund], count($streng->durchgaenge));
}

$nachgiebig = (new MoebiusLoop(new Perturbator($seed), new Mehrheit(0.66)))
    ->laufe($agent, $basis, $varianten);

echo "\nKennzahlen\n";
echo str_repeat('-', 72), "\n";
printf("  Referenzlauf allein            %2d Befunde\n", count($referenz->aussagen));
printf("  Vereinigung aller Läufe        %2d Befunde\n", count($streng->vereinigung()));
printf("  Streng (Schnittmenge)          %2d Befunde · Stabilität %.0f %%\n",
    count($streng->ergebnis), $streng->stabilitaet() * 100);
printf("  Nachgiebig (2/3-Mehrheit)      %2d Befunde · Stabilität %.0f %%\n",
    count($nachgiebig->ergebnis), $nachgiebig->stabilitaet() * 100);
printf("  Artefakte im Referenzlauf      %2d von %d (%.0f %%)\n",
    count($artefakte), count($referenz->aussagen),
    count($referenz->aussagen) === 0 ? 0.0 : count($artefakte) / count($referenz->aussagen) * 100);
echo "\n";
