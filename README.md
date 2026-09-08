# Möbius-Loop

Eine Perturbationsschicht über einem Agent-Loop: Sie verschiebt die Parameter jedes
Durchgangs zufällig innerhalb fester Bandbreiten und behält am Ende nur das, was jede
Verschiebung überstanden hat.

**Prototyp mit lauffähigem Beispiel.** Kein Produktivcode, keine Modellanbindung.

## Das Problem

Ein Agent-Loop läuft unter immer gleichen Bedingungen. Dieselbe Temperatur, dieselben
Schwellenwerte, dieselben Gewichte. Die Realität, auf die das Ergebnis später trifft, ist
nicht so stabil.

Das führt zu einem Effekt, den man leicht übersieht: Ein Teil dessen, was der Agent
liefert, hängt gar nicht an der Sache, sondern an der Einstellung. Dreht man an einer
Stellschraube, verschwindet der Befund — er war ein Artefakt der Parametrierung. Nur sieht
man das nicht, solange man immer dieselbe Einstellung benutzt.

## Die Idee

Nicht einmal laufen lassen, sondern mehrfach — jedes Mal mit leicht verschobenen
Parametern. Und dann nur behalten, was in **jedem** Durchgang vorkam.

```
Referenzlauf   (Basiswerte)          ─┐
Variante 1     (Parameter verschoben) │
Variante 2     (Parameter verschoben) ├──► Schnittmenge ──► Ergebnis
…                                     │
Variante n     (Parameter verschoben) ─┘
```

Was nach der Drehung noch gilt, gilt wirklich.

## Was der Prototyp zeigt

`examples/code_review.php` legt den Loop über einen nachgebildeten Review-Agenten. Der
meldet Befunde, deren Konfidenz über einer Schwelle liegt, und produziert mit steigender
Streuung zusätzlich Fehlalarme — zwei Eigenschaften, die echte Agenten teilen.

Zwei Parameter dürfen um 25 Prozent wandern, acht Varianten plus Referenzlauf:

```
Ergebnis der strengen Verdichtung (nur was jede Variante überlebt)
  ✓ SQL-Injection in ReportRepository::findByFilter()
  ✓ Fehlender Null-Check in InvoiceService::total()
  ✓ N+1-Query in OrderController::list()

Vom Referenzlauf behauptet, von der Perturbation kassiert
  ✗ Ungenutzter Import in MailerFactory                    (in 8 von 9 Läufen)
  ✗ Methode CustomerImport::run() zu lang (180 Zeilen)     (in 6 von 9 Läufen)
  ✗ Mögliche Race Condition in CacheWarmer                 (in 3 von 9 Läufen)

Kennzahlen
  Referenzlauf allein             6 Befunde
  Streng (Schnittmenge)           3 Befunde · Stabilität 33 %
  Nachgiebig (2/3-Mehrheit)       5 Befunde · Stabilität 56 %
  Artefakte im Referenzlauf       3 von 6 (50 %)
```

Die Hälfte dessen, was ein einzelner Durchlauf behauptet hätte, hält der Parametervariation
nicht stand. Übrig bleiben genau die drei Befunde mit der höchsten Konfidenz.

Der zweite Block ist der interessantere: „Ungenutzter Import" kam in acht von neun Läufen
vor und fällt trotzdem heraus. Das ist der Preis der strengen Regel, und er ist beabsichtigt
— aber man sollte ihn kennen. Deshalb steht die nachgiebige Variante zum Vergleich daneben.

## Das Prinzip ist nicht neu — das ist die gute Nachricht

Kontrollierte Perturbation zur Robustheitsprüfung ist etabliert:

| Verfahren | Feld | Kern |
|---|---|---|
| **Domain Randomization** | Robotik / RL | Physikparameter über breite Verteilungen randomisieren, damit die Policy invariant wird |
| **Property-Based Testing** | Softwaretest | Zufällige Eingaben prüfen Invarianten (QuickCheck, Hypothesis) |
| **Metamorphic Testing** | Softwaretest | Eingabevariation nach bekannten Relationen, für Systeme ohne Testorakel |
| **Chaos Engineering** | Betrieb | Kontrollierte Störungen im laufenden System |
| **Sensitivitätsanalyse** | Simulation | Parameterstreuung, Messung der Ergebnisstreuung |

Für LLM-Agenten wird die Frage derzeit aktiv bearbeitet, unter anderem in **StressWeb**
(kontrollierte Perturbation der Web-Umgebung) und **AgentNoiseBench** (Robustheit
werkzeugnutzender Agenten unter Tool-Noise).

## Was hier anders ist

Diese Arbeiten nutzen Perturbation zur **Evaluation** — Robustheit messen — oder zum
**Training**. Hier läuft sie **zur Laufzeit im produktiven Loop**, mit dem Ziel, das
Arbeitsergebnis selbst zu filtern. Nicht „wie robust ist mein Agent?", sondern „welcher
Teil dieser konkreten Antwort ist belastbar?".

Das ist die Anwendungsrichtung, um die es geht.

## Die offene Frage

Perturbation allein erzeugt nur Streuung. Erst die Verdichtungsregel macht daraus einen
Filter — und welche Regel richtig ist, ist nicht entschieden. Der Prototyp stellt zwei
zur Wahl:

- **`Ueberlebende`** — die Schnittmenge. Streng, unbarmherzig, ein einziger Ausreißerlauf
  kassiert eine richtige Aussage.
- **`Mehrheit`** — Schwellenwert über die Läufe. Nachgiebiger, aber die Schwelle ist
  gesetzt und nicht hergeleitet.

Ob das auf echte Agent-Loops überträgt, ist mit einem simulierten Agenten nicht zu klären.
Dafür bräuchte es Läufe gegen ein Modell und eine Aufgabe mit überprüfbarer Wahrheit.

## Zur Metapher

Das Möbiusband hat eine **feste** halbe Drehung: nach einem Umlauf ist man auf der
Gegenseite, nach zwei wieder am Start. Hier ist die Verschiebung zufällig, diese
Determiniertheit fehlt also. Die Metapher trägt für „derselbe Weg, andere Seite" — nicht
für den Zufallsanteil. Der Name ist ein Bild, kein Anspruch.

## Aufbau

```
src/
  Parameter.php              Basiswert und relative Bandbreite
  ParameterSet.php           unveränderliche Sammlung
  Perturbator.php            verschiebt innerhalb der Bandbreite, seedbar
  Agent.php                  Schnittstelle: ParameterSet → Aussagen
  MoebiusLoop.php            Referenzlauf plus n Varianten
  Durchgang.php              ein Lauf mit seiner Parametrierung
  Bericht.php                Ergebnis und Kennzahlen
  Aggregation/
    Verdichter.php           Schnittstelle
    Ueberlebende.php         Schnittmenge über alle Läufe
    Mehrheit.php             Schwellenwert
examples/
  ReviewAgent.php            nachgebildeter Agent, ohne Modellanbindung
  code_review.php            ausführbares Beispiel
```

Der Zufall ist **gesetzt, nicht beliebig**: Über den Seed ist jede Perturbationsfolge exakt
reproduzierbar. Ein Lauf, der sich nicht wiederholen lässt, taugt nicht als Nachweis — der
erste Test im Verzeichnis prüft genau das.

## Ausführen

```bash
composer install
php examples/code_review.php

vendor/bin/phpunit                      # 18 Tests
vendor/bin/phpstan analyse              # Level 9 + strict-rules
```

Voraussetzung: PHP 8.4.

## Stand

Prototyp. Der Agent ist nachgebildet, es gibt keine Modellanbindung und keine Messung
gegen eine überprüfbare Wahrheit. Was hier steht, zeigt den Mechanismus und die
Kennzahlen — nicht, dass das Verfahren in der Praxis trägt.

Nächster Schritt wäre ein Lauf gegen ein echtes Modell auf einer Aufgabe mit bekannter
Lösung, um zu messen, ob die Schnittmenge tatsächlich die richtigen Antworten behält und
nicht bloß die wenigen.

---

MIT · Volker Orgeldinger · orgeldinger.volker@gmail.com
