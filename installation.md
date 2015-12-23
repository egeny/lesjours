# Installation

## Serveur web

* Télécharger le fichier `.workflow`
* Double click dessus et choisir « Installer »
* Maintenant, en faisant un click droit sur un dossier on peut utiliser l'option « Serveur web ». Un navigateur s'ouvrira à la bonne adresse.

## GitHub

* Télécharger [GitHub Desktop](https://central.github.com/mac/latest)
* Lancer l'application et la configurer
* Ajouter le repository : `git@github.com:egeny/lesjours.git`

## Installer le compilateur

* Ouvrir un terminal
* Installer Homebrew : `ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"`
* Installer node : `brew install node`
* Installer gulp : `npm install -g gulp-cli`

## Télécharger les dépendences

* Double-clicker sur le fichier `scripts/install.command`

## Lancer la compilation

* Double-clicker sur le fichier `scripts/build.command`
* Utiliser plutôt le fichier `scripts/watch.command` pour lancer automatiquement la compilation dès qu'une modification est détectée sur un fichier
* Profits