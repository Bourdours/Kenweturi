# Kenweturi

Application de covoiturage web développée avec **CodeIgniter 4**, destinée aux étudiants et formateurs.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js / npm
- Extensions PHP : `intl`, `mbstring`, `json`, `mysqlnd`, `curl`

## Installation

```bash
git clone https://github.com/Bourdours/Kenweturi.git
cd Kenweturi
composer install
npm install
```

## Configuration

Copie le fichier d'exemple et remplis tes valeurs :

```bash
cp .env.example .env
```

Ouvre `.env` et configure :

- **App** : `app.baseURL`
- **Base de données** : host, nom, identifiants
- **Encryption** : `encryption.key` — génère une clé avec `php -r "echo bin2hex(random_bytes(32));"`
- **Mailer** : identifiants SMTP (voir section ci-dessous)
- **Site** : email de contact et URL publique

## Dépendances installées

### PHPMailer

Utilisé pour l'envoi d'emails via SMTP.

Fichier d'exemple : `app/Libraries/MailerExample.php`

Configure les variables dans `.env` :

```
mailer.host     = smtp.ton-fournisseur.com
mailer.port     = 587
mailer.username = ton-email@example.com
mailer.password = ton-mot-de-passe-smtp
mailer.from     = noreply@example.com
mailer.fromName = Kenweturi
```

> Pour Gmail, génère un [mot de passe d'application](https://support.google.com/mail/answer/185833) plutôt que d'utiliser ton mot de passe principal.

### Site

Variables publiques affichées dans les pages légales :

```
site.contactEmail = contact@example.com
site.siteUrl      = kenweturi.fr
```

### Tailwind CSS

Utilisé pour le style des vues via des classes utilitaires.

Fichier d'exemple : `app/Libraries/TailwindExample.php`

Aucune variable `.env` requise. Le CSS généré est servi depuis `public/css/tailwind.css`.

Commandes disponibles :

```bash
npm run dev        # rebuild automatique pendant le développement
npm run build      # build minifié pour la production
```

Inclure le CSS dans chaque vue :

```html
<link rel="stylesheet" href="/css/tailwind.css">
```

### Leaflet

Utilisé pour l'affichage des cartes interactives (trajets, aperçus, réservations).

Chargé via CDN, aucune installation requise :

```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

Les tuiles cartographiques proviennent de l'IGN Géoportail (`data.geopf.fr`), sans clé API, avec les labels en français.

## Base de données

Lance les migrations pour créer les tables :

```bash
php spark migrate
```

## Lancer le projet

```bash
php spark serve
```

L'application est accessible sur `http://localhost:8080`.
