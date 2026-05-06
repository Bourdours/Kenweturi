# Kenweturi

Application web développée avec **CodeIgniter 4**.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Extensions PHP : `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`

## Installation

```bash
git clone https://github.com/Bourdours/Kenweturi.git
cd Kenweturi
composer install
```

## Configuration

Copie le fichier d'exemple et remplis tes valeurs :

```bash
cp .env.example .env
```

Ouvre `.env` et configure :

- **App** : `app.baseURL`
- **Base de données** : host, nom, identifiants
- **Mailer** : identifiants SMTP (voir section ci-dessous)
- **Pusher** : clés de l'app (voir section ci-dessous)

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

### Pusher

Utilisé pour les événements en temps réel (WebSockets).

Fichier d'exemple : `app/Libraries/PusherExample.php`

Crée une app sur [dashboard.pusher.com](https://dashboard.pusher.com), puis récupère les clés dans **App Settings > App Keys** et configure `.env` :

```
pusher.appKey    = xxxxxxxxxxxxxxxxxxxxxxxx
pusher.appSecret = xxxxxxxxxxxxxxxxxxxxxxxx
pusher.appId     = 000000
pusher.cluster   = eu
```

### Tailwind CSS

Utilisé pour le style des vues via des classes utilitaires.

Fichier d'exemple : `app/Libraries/TailwindExample.php`

Aucune variable `.env` requise. Le CSS généré est servi depuis `public/css/tailwind.css`.

Commandes disponibles :

```bash
npm install        # installe les dépendances npm (première fois)
npm run dev        # rebuild automatique pendant le développement
npm run build      # build minifié pour la production
```

Inclure le CSS dans chaque vue :

```html
<link rel="stylesheet" href="/css/tailwind.css">
```

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
