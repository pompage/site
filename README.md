#Pompage

Gestion du code source du site [Pompage](htp://pompage.net/).

[Faire une suggestion ou signaler un problème](https://github.com/pompage/site/issues)

____

##TODO :

**Mettre en place le code des squelettes SPIP actuels sur github** 
(le répertoire /squelettes de l'install de spip)
Pour celà, il faut que la personne qui a les codes admin sur le site :

- Synchronise un SPIP sur sa machine avec le SPIP de nursit : http://www.nursit.com/Le-plugin-migration-pour-SPIP ou alors récupère le zip du site existant : http://www.nursit.com/Installer-le-zip-du-site-SPIP

- Envoie le contenu de `/squelettes` sur github.

**Se constituer un environnement de dev chez soi** 
Très facile, avec les deux méthodes d'export vues plus haut. La méthode "récupération du zip" est impressionante de simplicité !
Remplacez le contenu du dossier `/squelettes` de votre copie locale par le contenu du repostory github de pompage.

**Faire une "preprod" sur nursit pour tester en conditions réelles** (peut attendre)
Grace au plugin "migration", voir plus haut.

##Comment travailler :
**Installer chez soi une copie locale de pompage.** 
2 méthodes : [synchroniser une instance de SPIP existante](http://www.nursit.com/Le-plugin-migration-pour-SPIP) ou [Récupérer un zip prêt à fonctionner](http://www.nursit.com/Installer-le-zip-du-site-SPIP)

Ensuite, il vous faudra remplacer le contenu du répertoire `/squelettes` de votre installation par un clone de ce repository Git. Pour le fonctionnement de `git`, voir la documentation en ligne. Ce lien peut être un bon début : http://www.tuteurs.ens.fr/logiciels/git/

**Tester, développer**
Git : http://www.tuteurs.ens.fr/logiciels/git/

SPIP : http://www.spip.net/@?lang=fr et http://www.spip.net/fr_rubrique91.html

**Tester en preprod, mettre en prod**
Une fois les évolutions faites, testées et commitées sur Github, on pourra les mettre sur une "preprod", clone exact du site (à faire sur nursit). A ce moment là toute l'équipe pourra tester et vérifier.

Une fois ces ultimes vérifications faites, on envoie en "prod", sur le site officiel.

Pour tout cela, utiliser le plugin migration (voir plus haut) ou la méthode webDAV : http://www.nursit.com/Activer-Webdav-sur-votre-site-SPIP-chez-Nursit

____

[Faire une suggestion ou signaler un problème](https://github.com/pompage/site/issues)
