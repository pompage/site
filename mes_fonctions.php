<?php if (!defined("_ECRIRE_INC_VERSION")) return;
/*
* filtre pour les brèves de "traduit ailleurs"
* -- on ne veut pas de retour à la ligne
* -- pendant que j'y suis je nettoie tout sauf em, strong, a
* -- de toute fa...
* */

function pompage_strip_tags($str) {
  $str = strip_tags($str, '<a><em><strong>');
  return $str;
}

/*
* test d'iso propre
* */
function date_iso_pompage($date_heure) {
  list($annee, $mois, $jour) = recup_date($date_heure);
  list($heures, $minutes, $secondes) = recup_heure($date_heure);
  $time = mktime($heures, $minutes, $secondes, $mois, $jour, $annee);
  return gmdate("Y-m-d\TH:i:s\+02:00", $time);
}


/* extra fields restrictions
***********************************************************
include_spip('inc/cextras_autoriser');
 restrict an array of extra fields to sector 1 (traduction)
restreindre_extras('article', array('autorisation',
                                    'beta',
                                    'titre_orig',
                                    'date_orig',
                                    'url_orig'), 1, 'secteur');
*/ 


// Fabien‑20110709 : modification pour pompage
// Echapper les <code>...</ code>
// http://doc.spip.org/@traiter_echap_code_dist
function traiter_echap_code($regs) {
  list(,,$att,$corps) = $regs;
  $echap = htmlspecialchars($corps); // il ne faut pas passer dans entites_html, ne pas transformer les &#xxx; du code !

  // ne pas mettre le <div...> s'il n'y a qu'une ligne
  if (is_int(strpos($echap,"\n"))) {
    // supprimer les sauts de ligne debut/fin
    // (mais pas les espaces => ascii art).
    $echap = preg_replace("/^[\n\r]+|[\n\r]+$/s", "", $echap);
    $echap = "<pre><code$att>".$echap."</code></pre>";
  } else {
    $echap = "<code$att>".$echap."</code>";
  }

  $echap = str_replace("\t", "  ", $echap);
  return $echap;
}

