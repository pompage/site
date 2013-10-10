<?php if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Filtre pour les brèves de "traduit ailleurs"
 * On ne veut pas de retour à la ligne.
 * Pendant que j'y suis je nettoie tout sauf em, strong, a
 * @param  string $str Texte à filtrer
 * @return string      Texte filtré
 */
function pompage_strip_tags($str) {
  $str = strip_tags($str, '<a><em><strong>');
  return $str;
}

/**
 * Fabien‑20110709 : modification pour pompage
 * Echapper les <code>...</ code>
 * http://doc.spip.org/@traiter_echap_code_dist
 */
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
