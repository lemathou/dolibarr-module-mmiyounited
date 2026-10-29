# MMIYounited — Paiement Younited Pay : création de prêt/paiement, offres, redirection, webhook et suivi des statuts.

> Module Dolibarr **MMIYounited** (version 1.1.3), branche `20.0`.

## Fonctionnalités

### Fonctionnalités principales

- Création de prêt/paiement (`request.php`) et offres (`offers.php`) via l'API Younited (sandbox/production).
- Webhook de mise à jour (`webhook.php`) : statut re-interrogé auprès de l'API, journal hors racine web (RM3580).
- Retours client (`return.php`, `accepted.php`, `cancelled.php`) et suivi des maturités (`mmi_younited_maturity`).
- Configuration API prod/sandbox (URL, identifiants, mot de passe webhook, shop code, merchant ref), mode de paiement, compte bancaire, notifications et maturités.

### Écrans et menus

- **Accueil** — `/custom/mmiyounited/mmiyounitedindex.php` (menu mmiyounited, droit `1`)

### Pages

- `accepted.php`
- `admin/about.php`
- `admin/setup.php`
- `cancelled.php`
- `mmiyounitedindex.php`
- `offers.php`
- `request.php`
- `return.php`
- `webhook.php`

### Configuration

| Constante | Type | Rôle |
|---|---|---|
| `MMI_YOUNITED_ENABLE` | yesno | Activer le module de paiement |
| `MMI_YOUNITED_API_MODE_SANDBOX` | yesno | Mode Sandbox (test) |
| `MMI_YOUNITED_DEBUG` | yesno | Mode Debug (développeur) |
| `MMI_YOUNITED_BANQ` | separator | Paramètres bancaires |
| `MMI_YOUNITED_PAYMENT_MODE` | int | Mode de paiement |
| `MMI_YOUNITED_ACCOUNT_ID` | int | Compte |
| `MMI_YOUNITED_USER_ID` | int | Utilisateur validateur (admin auto) |
| `MMI_YOUNITED_AMOUNT_MIN` | int | Montant minimum accepté |
| `MMI_YOUNITED_NOTIFICATION` | separator | Notifications par Email |
| `MMI_YOUNITED_NOTIFICATION_EMAIL_FROM` | string | Adresse email d'expéditeur |
| `MMI_YOUNITED_NOTIFICATION_EMAIL_TO` | string | Adresse email de destination |
| `MMI_YOUNITED_API_PRODUCTION` | separator | Environnement Production |
| `MMI_YOUNITED_API_PRODUCTION_TOKEN_URL` | string | URL de récupération de token de production |
| `MMI_YOUNITED_API_PRODUCTION_URL` | string | URL de production |
| `MMI_YOUNITED_API_PRODUCTION_USERNAME` | string | Nom d'utilisateur production |
| `MMI_YOUNITED_API_PRODUCTION_PASSWORD` | securekey | Mot de passe production |
| `MMI_YOUNITED_API_PRODUCTION_WEBHOOK_PASSWORD` | securekey |  |
| `MMI_YOUNITED_API_PRODUCTION_SHOPCODE` | string |  |
| `MMI_YOUNITED_API_PRODUCTION_MERCHANT_REF` | string |  |
| `MMI_YOUNITED_API_PRODUCTION_TOKEN` | securekey | Token de production (utile ?) |
| `MMI_YOUNITED_API_SANDBOX` | separator | Environnement Sandbox |
| `MMI_YOUNITED_API_SANDBOX_TOKEN_URL` | string | URL de récupération de token de sandbox |
| `MMI_YOUNITED_API_SANDBOX_URL` | string | URL sandbox |
| `MMI_YOUNITED_API_SANDBOX_USERNAME` | string | Nom d'utilisateur sandbox |
| `MMI_YOUNITED_API_SANDBOX_PASSWORD` | securekey | Mot de passe sandbox |
| `MMI_YOUNITED_API_SANDBOX_WEBHOOK_PASSWORD` | securekey |  |
| `MMI_YOUNITED_API_SANDBOX_SHOPCODE` | string |  |
| `MMI_YOUNITED_API_SANDBOX_MERCHANT_REF` | string |  |
| `MMI_YOUNITED_API_SANDBOX_TOKEN` | securekey | Token sandbox (utile ?) |
| `MMI_YOUNITED_MATURITIES` | separator |  |
| `MMI_YOUNITED_MATURITY_LIST` | string |  |
| `MMI_YOUNITED_MATURITY_FREE_ENABLED` | yesno |  |
| `MMI_YOUNITED_MATURITY_FREE_LIST` | string |  |
| `MMI_YOUNITED_MATURITY_FREE_AMOUNT_MIN` | int |  |
| `MMI_YOUNITED_MATURITY_FREE_COEFF_MIN` | number |  |

### Hooks

- Contextes : `invoicecard`, `ordercard`, `propalcard`, `newpayment`
- Callbacks : `doCheckStatus`, `addOnlinePaymentMeans`, `doaddButton`, `doValidatePayment`, `doPayment`

### Classes et composants

- `class/actions_mmiyounited.class.php`
- `class/mmi_younited_maturity.class.php`
- `class/mmi_younited_pay.class.php`
- `class/younitedpay.class.php`
- `class/younitedpay_webhooklog.class.php`
- `lib/mmiyounited.lib.php`

## Prérequis

- Dolibarr >= 11.0 (branche `20.0` : Dolibarr 20.0).
- Modules MMI : `mmicommon`, `mmipayments`. **`mmicommon` doit être activé en premier.**

## Installation

1. Copier le module dans `htdocs/custom/mmiyounited` :

   ```sh
   cd htdocs/custom
   git clone <dépôt> mmiyounited
   ```

2. Dans Dolibarr : **Configuration → Modules**, activer d'abord `mmicommon`, puis ce module.
3. Vérifier les permissions dans la fiche des utilisateurs concernés.

## Licence

GPLv3 ou ultérieure — voir `COPYING`. Documentation sous GFDL.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) (évolutions datées).
