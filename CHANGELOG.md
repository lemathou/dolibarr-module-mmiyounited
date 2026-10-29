# Changelog — MMIYounited

Toutes les évolutions notables, avec dates (ISO).

## [1.1.3] — 2026-10-28

### Modifié
- Migration Dolibarr 20.0 (RM3565).
- Documentation : README complet (fonctionnalités, prérequis, configuration) et CHANGELOG daté (RM3569).

## Historique daté

- 2026-10-28 — Merge branch '3580-security' into '20.0'
- 2026-10-28 — RM3580 mmiyounited : webhook sans confiance au payload, journal hors racine web
- 2026-10-28 — Merge branch '3579-fix-mmiyounited' into '20.0'
- 2026-10-28 — RM3579 mmiyounited : libelle Area (Younited Pay)
- 2026-10-28 — Merge branch '3579-home-mmiyounited' into '20.0'
- 2026-10-28 — RM3579 mmiyounited : page d accueil + entree de menu
- 2026-10-28 — Merge branch '3581-tests-mmiyounited' into '20.0'
- 2026-10-28 — RM3581 mmiyounited : extraction de maturity_list en classe pure + tests unitaires
- 2026-10-28 — Merge branch '3567-cleanup-mmiyounited' into '20.0'
- 2026-10-28 — RM3567 mmiyounited : echappement SQL et verification des retours (webhook)
- 2026-10-28 — RM3567 mmiyounited : retrait du debug actif et commente, exit(-1) -> exit(1)
- 2026-10-28 — Merge 19.0 into 20.0
- 2026-09-25 — Merge branch '3307-le-mode-de-paiement-younited-pay-est-maintenant-av' into '19.0'
- 2026-09-25 — RM3307 — Retrait du placement JS après PayPal (ordre relatif PayPal non requis)
- 2026-09-25 — RM3307 — Younited Pay après CB et PayPal sur la page publique de paiement
- 2026-02-24 — Fix apiVersion to 2025-01-01
- 2025-11-24 — Fix amount to 2 decimals
- 2025-11-24 — Fix reste amount
- 2025-11-24 — Add option for free (for customer) maturity list (paid by shop)
- 2025-09-30 — Fix last day propal
- 2025-07-01 — Remove redundans translations Settings and Abaout
- 2025-07-01 — Logo and Name
- 2025-06-17 — Fix nologin request
- 2025-05-22 — Add specific user for validation, minimum amount, check customer data, etc.
- 2025-05-20 — Fix : remove debug return url
