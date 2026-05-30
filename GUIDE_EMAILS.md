# 📧 Guide Configuration Emails Professionnels — ATLÉX-SPORT
**Domaine :** atlexsport.com  
**Hébergeur :** Hostinger

---

## Boîtes email à créer

| Adresse | Usage | Destinataire |
|---------|-------|--------------|
| `contact@atlexsport.com` | Contact public du site, formulaire de contact | Ulrich (SG) |
| `admin@atlexsport.com` | Connexion au panneau admin du site | Ulrich (SG) |
| `ulrich@atlexsport.com` | Email personnel du SG | Ulrich |
| `noreply@atlexsport.com` | Envois automatiques (rappels Hostinger, confirmations) | Alias (pas de boîte réelle nécessaire) |

---

## ÉTAPE 1 — Créer les boîtes mail dans Hostinger

1. Hostinger → **Hosting → Manage → Emails → Email Accounts**
2. Cliquer **Create email account**
3. Créer dans l'ordre :

### contact@atlexsport.com
- Nom d'affichage : `ATLÉX-SPORT`
- Mot de passe : (fort, à noter)
- Quota : 1 Go minimum

### ulrich@atlexsport.com
- Nom d'affichage : `Ulrich — ATLÉX-SPORT`
- Mot de passe : (fort, à noter)
- Quota : 1 Go minimum

### noreply@atlexsport.com
- Nom d'affichage : `ATLÉX-SPORT (Ne pas répondre)`
- Peut être un alias pointant vers `contact@atlexsport.com`

---

## ÉTAPE 2 — Configurer le client mail (Outlook / Gmail / Thunderbird)

### Paramètres IMAP (réception)
```
Serveur : imap.hostinger.com
Port    : 993
Sécurité: SSL/TLS
```

### Paramètres SMTP (envoi)
```
Serveur : smtp.hostinger.com
Port    : 465
Sécurité: SSL/TLS
```

---

## ÉTAPE 3 — Ajouter Gmail comme client (optionnel)

Pour lire les emails @atlexsport.com directement dans Gmail :

1. Gmail → **Paramètres → Comptes et importation**
2. **Consulter d'autres comptes de messagerie** → Ajouter un compte
3. Saisir `ulrich@atlexsport.com`
4. Choisir **POP3** avec les paramètres Hostinger
5. Cocher **"Conserver la copie sur le serveur"**

Pour envoyer depuis Gmail en tant que `ulrich@atlexsport.com` :
1. **Envoyer un message en tant que** → Ajouter une adresse
2. SMTP : `smtp.hostinger.com` · Port : `465` · SSL

---

## ÉTAPE 4 — Mettre à jour le .env du site

S'assurer que ces lignes sont dans le `.env` :

```env
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USER=contact@atlexsport.com
MAIL_PASS=MOT_DE_PASSE_DE_LA_BOITE_CONTACT
MAIL_ENCRYPTION=ssl
MAIL_FROM_NAME="ATLÉX-SPORT"

ADMIN_EMAIL=ulrich@atlexsport.com
```

---

## ÉTAPE 5 — Tester l'envoi

Depuis le panel admin → **Hébergement** → tester la connexion Hostinger.
Le premier rappel par email sera envoyé à `ulrich@atlexsport.com`.

Alternativement, depuis le formulaire de contact public :
1. Aller sur `https://atlexsport.com/contact`
2. Remplir et envoyer un message test
3. Vérifier la réception sur `contact@atlexsport.com`

---

## Configuration SPF/DKIM (anti-spam)

Hostinger configure automatiquement DKIM pour les emails envoyés via leur SMTP.  
Vérifier dans Hostinger → **Emails → Email Security** que DKIM est activé.

Enregistrement SPF à avoir dans le DNS (normalement ajouté automatiquement) :
```
TXT @ v=spf1 include:_spf.hostinger.com ~all
```

---

## 🗂️ Récapitulatif des accès

| Service | URL | Identifiant |
|---------|-----|-------------|
| Panel Hostinger | hostinger.com | email du compte Hostinger |
| Webmail | webmail.atlexsport.com | contact@atlexsport.com |
| Admin site | atlexsport.com/admin | admin@atlexsport.com |
| API Hostinger | api.hostinger.com | Token API (dans .env) |
