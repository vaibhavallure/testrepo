<?php
/**
 * Fichier appelé par le Pick pour lancer la mise à jour de la table des produits 'baseok'
 * @todo faire quelque chose de moins tordu
 */
//exec("docker exec  millesima-emailing-mysql /bin/sh -c 'mysql -uroot -pmillesima emailing < importbaseok.sql'");
try {
    exec("mysql -h millesima-emailing-mysql -uroot -pmillesima emailing < importbaseok.sql");
    echo 'success';
} catch (exception $e) {
    echo 'something went wrong : '.$e;
}
