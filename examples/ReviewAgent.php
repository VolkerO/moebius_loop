<?php

declare(strict_types=1);

namespace Moebius\Beispiel;

use Moebius\Agent;
use Moebius\ParameterSet;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Nachgebildeter Review-Agent — bewusst ohne Modellanbindung.
 *
 * Er meldet Befunde, deren Konfidenz ueber einer Schwelle liegt, und erfindet
 * zusaetzlich Fehlalarme, deren Zahl mit dem Parameter „streuung" waechst. Damit
 * bildet er zwei Eigenschaften echter Agenten nach, auf die es hier ankommt:
 *
 *   1. Was gemeldet wird, haengt an der Einstellung, nicht nur an der Sache.
 *   2. Fehlalarme sind nicht zufaellig ueber alle Laeufe verteilt, sondern
 *      folgen der Parametrierung.
 *
 * Er ist bei gegebener Parametrierung deterministisch. Das ist wichtig: Sonst
 * mischte sich der Zufall des Agenten unter den der Perturbation, und der
 * gemessene Effekt liesse sich keiner Ursache mehr zuordnen.
 */
final readonly class ReviewAgent implements Agent
{
    /** @var array<string, float> Befund => Konfidenz */
    private const array BEFUNDE = [
        'SQL-Injection in ReportRepository::findByFilter()' => 0.97,
        'Fehlender Null-Check in InvoiceService::total()'   => 0.93,
        'N+1-Query in OrderController::list()'             => 0.88,
        'Ungenutzter Import in MailerFactory'              => 0.71,
        'Methode CustomerImport::run() zu lang (180 Zeilen)' => 0.64,
        'Magic Number 86400 in SessionGuard'               => 0.52,
        'Variablenname $tmp2 in PriceCalculator'           => 0.41,
    ];

    private const array FEHLALARME = [
        'Moegliche Race Condition in CacheWarmer',
        'Unsichere Deserialisierung in ConfigLoader',
        'Fehlende Transaktionsklammer in BatchJob',
        'Toter Code in LegacyAdapter',
    ];

    public function laufe(ParameterSet $parameter): array
    {
        $schwelle = $parameter->wert('schwelle');
        $streuung = $parameter->wert('streuung');

        $gemeldet = [];
        foreach (self::BEFUNDE as $befund => $konfidenz) {
            if ($konfidenz >= $schwelle) {
                $gemeldet[] = $befund;
            }
        }

        // Der Seed haengt allein an der Parametrierung: gleiche Einstellung,
        // gleiche Fehlalarme.
        $wuerfel = new Randomizer(new Mt19937((int) round($streuung * 1_000_000)));
        $anzahl = (int) floor($streuung * count(self::FEHLALARME));

        for ($i = 0; $i < $anzahl; ++$i) {
            $gemeldet[] = self::FEHLALARME[$wuerfel->getInt(0, count(self::FEHLALARME) - 1)];
        }

        return array_values(array_unique($gemeldet));
    }
}
