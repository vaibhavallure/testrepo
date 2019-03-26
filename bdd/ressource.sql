--
-- ALTER TABLE pour la donnée offexc => astdesc
--
ALTER TABLE `emailing`.`messagedata` CHANGE COLUMN `offexc` `astdesc` VARCHAR(45) NULL DEFAULT NULL ;

--
-- UPDATE pour les données articles
--
UPDATE `emailing`.`traduction` SET `type`='article2_astart' WHERE `type`='article2_offexc';
UPDATE `emailing`.`traduction` SET `type`='article1_astart' WHERE `type`='article1_offexc';
UPDATE `emailing`.`traduction` SET `type`='article3_astart' WHERE `type`='article3_offexc';
UPDATE `emailing`.`traduction` SET `type`='article4_astart' WHERE `type`='article4_offexc';


--
-- Structure de la table `ressource`
--

CREATE TABLE `emailing`.`ressource` (
  `id` int(10) NOT NULL,
  `name` varchar(762) DEFAULT NULL,
  `store` varchar(762) DEFAULT NULL,
  `value` blob,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour la table `ressource`
--
ALTER TABLE `emailing`.`ressource`
  ADD PRIMARY KEY (`id`);

 --
-- AUTO_INCREMENT pour la table `ressource`
--
ALTER TABLE `emailing`.`ressource`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;



--
-- données initiales de la table `ressource`
--
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','F','<strong>Frais de port offerts</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','B','<strong>Frais de port offerts</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','L','<strong>Frais de port offerts</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','D','<strong>Lieferung gratis</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','O','<strong>Lieferung gratis</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','SA','<strong>Lieferung gratis</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','SF','<strong>Frais de port offerts</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','G','<strong>Free delivery</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','I','<strong>Free delivery</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','P','<strong>Transporte grátis</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','E','<strong>Entrega gratuita</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','Y','<strong>Spedizione gratuita</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','H','<strong>Free delivery</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','SG','<strong>Free delivery</strong>','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo','U','','2019-03-12 00:00:00',NULL);

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','F','jusqu\'au 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','B','jusqu\'au 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','L','jusqu\'au 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','D','','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','O','','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','SA','Gültig bis 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','SF','jusqu\'au 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','G','until the 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','I','until the 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','P','até 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','E','','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','Y','','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','H','','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','SG','until the 31/08/2019','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_ssphrase','U','','2019-03-12 00:00:00',NULL);

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','F','*Hors vins primeurs - Livraison en une seule fois à une seule adresse en France Métropolitaine - Valable jusqu\'au {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','B','*Hors vins primeurs - Livraison en une seule fois à une seule adresse en Belgique - Valable jusqu\'au {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','L','*Hors vins primeurs - Livraison en une seule fois à une seule adresse au Luxembourg - Valable jusqu\'au {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','D','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Deutschland.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','O','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Österreich.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','SA','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in der Schweiz - Gültig bis {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','SF','*Hors vins primeurs - Livraison en une seule fois à une seule adresse en Suisse - Valable jusqu\'au {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','G','*Excluding en primeur wines. Free delivery on one order to one address – valid until the {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','I','*Excluding en primeur wines. Free delivery on one order to one address – valid until the {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','Y','*Non riguarda i vini primeur. Per un\'unica consegna ad un solo indirizzo.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','E','*No incluye los vinos en primeur. Para una sola expedición a una única dirección.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','P','*Fora dos vinhos primeurs – Entrega numa morada em Portugal Continental - Até {$datefdpo}','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','H','*Excluding en primeur wines. Free delivery on one order to one address','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','SG','*Excluding en primeur wines. Free delivery on one order to one address','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bdf_fdpo_detail','U','','2019-03-12 00:00:00',NULL);

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','F','<strong>Livraison garantie avant Noël pour les commandes reçues avant le 12 décembre 2018</strong>','2018-11-01 00:00:00','2018-12-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','B','<strong>Livraison garantie avant Noël pour les commandes reçues avant le 6 décembre 2018</strong>','2018-11-01 00:00:00','2018-12-06 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','L','<strong>Livraison garantie avant Noël pour les commandes reçues avant le 6 décembre 2018</strong>','2018-11-01 00:00:00','2018-12-06 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SF','<strong>Livraison garantie avant Noël pour les commandes reçues avant le 3 décembre 2018</strong>','2018-11-01 00:00:00','2018-12-03 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','D','<strong>Bestellungen, die uns bis zum 4. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>','2018-11-04 00:00:00','2018-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','O','<strong>Bestellungen, die uns bis zum 4. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>','2018-11-04 00:00:00','2018-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SA','<strong>Bestellungen, die uns bis zum 3. Dezember 2018 vorliegen, können bis Weihnachten angeliefert werden.</strong>','2018-11-03 00:00:00','2018-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','G','<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>','2018-11-01 00:00:00','2018-12-09 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','I','<strong>Delivery before Christmas guaranteed for orders received before the 2nd of December.</strong>','2018-11-01 00:00:00','2018-12-02 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','H','<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>','2018-11-01 00:00:00','2018-11-09 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SG','<strong>Delivery before Christmas guaranteed for orders received before the 9th of December.</strong>','2018-11-01 00:00:00','2018-11-09 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','Y','<strong>Per gli ordini effettuati prima del 6 dicembre, Millésima garantisce la consegna entro Natale.</strong>','2018-11-01 00:00:00','2018-12-06 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','E','<strong>Se entregarán antes de Navidad los pedidos recibidos antes del 6 de diciembre de 2018</strong>','2018-11-01 00:00:00','2018-12-06 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','P','<strong>As encomendas recebidas antes de 9 de Dezembro de 2018 serão entregues antes do Natal.</strong>','2018-11-01 00:00:00','2018-12-09 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','U','','2018-11-01 00:00:00','2018-12-01 00:00:00');




insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','H','<strong>Order before the 3rd of March for a delivery from the 14th of March onwards.</strong>','2019-02-28 00:00:00','2019-03-03 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','H','<strong>Order before the 31st of March for a delivery from the 10th of April onwards.</strong>','2019-03-03 00:00:00','2019-03-31 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','H','<strong>Order before the 14th of April for a delivery from the 24th of April onwards.</strong>','2019-03-31 00:00:00','2019-04-14 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SG','<strong>Order before the 3rd of March for a delivery from the 14th of March onwards.</strong>','2019-02-28 00:00:00','2019-03-03 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SG','<strong>Order before the 31st of March for a delivery from the 10th of April onwards.</strong>','2019-03-12 00:00:00','2019-03-31 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SG','<strong>Order before the 14th of April for a delivery from the 24th of April onwards.</strong>','2019-03-31 00:00:00','2019-04-14 00:00:00');

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','H','<strong>In order to guarantee the integrity of our wines, the next shipment to Hong Kong will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>','2017-07-01 00:00:00','2017-08-23 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_detail','SG','<strong>In order to guarantee the integrity of our wines, the next shipment to Singapore will take place at the end of August. Please make sure to order before the 23rd of August at the latest for delivery before the 15th of September in time for the Moon Festival.</strong>','2017-07-01 00:00:00','2017-08-23 00:00:00');

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','F','Livraison avant Noël : derniers jours *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','B','Livraison avant Noël : derniers jours *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','L','Livraison avant Noël : derniers jours *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','SF','Livraison avant Noël : derniers jours *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','D','Eine Lieferung bis Weihnachten – Nur noch wenige Tage *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','O','Eine Lieferung bis Weihnachten – Nur noch wenige Tage *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','SA','Eine Lieferung bis Weihnachten – Nur noch wenige Tage *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','G','Delivery before Christmas: very last days *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','I','Delivery before Christmas: very last days *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','H','Delivery before Christmas: very last days *','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_title','SG','Delivery before Christmas: very last days *','2015-11-01 00:00:00','2015-12-01 00:00:00');

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','F','* Livraison garantie avant Noël pour les commandes reçues avant le <strong>7 décembre 2015</strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','B','* Livraison garantie avant Noël pour les commandes reçues avant le <strong>7 décembre 2015</strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','L','* Livraison garantie avant Noël pour les commandes reçues avant le <strong>7 décembre 2015</strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','SF','* Livraison garantie avant Noël pour les commandes reçues avant le <strong>7 décembre 2015</strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','D','* Bestellungen, die uns bis zum <strong>8. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','O','* Bestellungen, die uns bis zum <strong>8. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','SA','* Bestellungen, die uns bis zum <strong>7. Dezember 2015</strong> vorliegen, können bis Weihnachten angeliefert werden.','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','G','* Delivery guaranteed before Christmas for all orders placed before <strong>December the 7<sup>th</sup></strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','I','* Delivery guaranteed before Christmas for all orders placed before <strong>December the 7<sup>th</sup></strong>','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','Y','Solo gli ordini ricevuti prima del 4 dicembre 2015 saranno assicurati della consegna entro Natale.','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','E','Se entregarán antes de Navidad los pedidos recibidos antes del 4 de diciembre de 2015','2015-11-01 00:00:00','2015-12-01 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('bd_header_asterisque','P','* As encomendas recebidas antes de <strong>7 de Dezembro de 2015</strong> serão entregues antes do Natal.','2015-11-01 00:00:00','2015-12-01 00:00:00');


insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Offre valable une fois jusqu\'au {$datevalide} inclus sur l\'ensemble du site (hors promotions et vins primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Hors promotion en cours et hors vins en primeurs. Valable jusqu\'au {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Chaque oeuvre dispose de son propre certificat d\'authenticité.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Offre valable une seule fois par client à partir de 400€ de vins livrables (hors vins primeurs), dans la limite des stocks disponibles','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Hors vins primeurs - Valable jusqu\'au 31/08/2018','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Quantités extrêmement limitées','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Offre valable une fois jusqu\'au {$datevalide} inclus sur l\'ensemble du site (hors promotions et vins primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Hors promotion en cours et hors vins en primeurs. Valable jusqu\'au {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Chaque oeuvre dispose de son propre certificat d\'authenticité.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Offre valable une seule fois par client à partir de 400€ de vins livrables (hors vins primeurs), dans la limite des stocks disponibles','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Hors vins primeurs - Valable jusqu\'au 31/08/2018','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Quantités extrêmement limitées','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Offre valable une fois jusqu\'au {$datevalide} inclus sur l\'ensemble du site (hors promotions et vins primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Hors promotion en cours et hors vins en primeurs. Valable jusqu\'au {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Chaque oeuvre dispose de son propre certificat d\'authenticité.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Offre valable une seule fois par client à partir de 400€ de vins livrables (hors vins primeurs), dans la limite des stocks disponibles','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Hors vins primeurs - Valable jusqu\'au 31/08/2018','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Quantités extrêmement limitées','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Offre valable dans la limite des stocks disponibles, hors promotions en cours et hors primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Offre valable une fois jusqu\'au {$datevalide} inclus sur l\'ensemble du site (hors promotions et vins primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Hors promotion en cours et hors vins en primeurs. Valable jusqu\'au {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Chaque oeuvre dispose de son propre certificat d\'authenticité.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Offre valable une seule fois par client à partir de CHF 450 de vins livrables (hors vins primeurs), dans la limite des stocks disponibles','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Hors vins primeurs - Valable jusqu\'au 31/08/2018','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Uniquement valide sur les caisses panachées, hors vins primeurs et hors promotion.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Hors seconds vins déjà en promotion, primeurs 2016 et 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Quantités très limitées et sous réserve des stocks disponibles. Certains vins sont stockés dans le chais de la propriété. La livraison se fera sous 45 jours dans nos chais + délai de livraison selon le pays.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Quantités extrêmement limitées','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gültig, solange der Vorrat reicht, nicht kumulierbar mit anderen Aktionen, Subskriptionen ausgeschlossen.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gütlig einmalig bis zum {$datevalide} auf unsere vollständige Internetseite, ausgenommen sind bereits rabattierte Produkte und Primeursubskriptionen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*ausgeschlossen sind Subskriptionsweine und bereits reduzierte Weine. Dieses Angebot ist bis zum {$datevalide} Gültig.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Jedes Werk hat ein Echtheitszertifikat.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gültig für einmalige Anwendung ab 400€ lieferbarem Wein (außer Subskriptionsweine) - solange der Vorrat reicht','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gültig für Bestellungen ab 300 €, nur lieferbare Weine, Subskriptionen ausgeschlossen und nur für die 50 ersten Bestellungen','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gültig für Mischkisten, ausgenommen Primeurweine und bereits bestehende Sonderangebote!','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*ausgenommen sind bereits reduzierte Weine, Subskriptionen 2016 und 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Sehr begrenzte Mengen und lieferbar solange der Vorrat reicht. Einige Weine lagern direkt im Weingut. Die Lieferung in unsere Weinkeller erfolgt innerhalb von 45 Tagen, dazu kommt die übliche Lieferzeit zu Ihnen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Mengen extrem limitiert','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gültig, solange der Vorrat reicht, nicht kumulierbar mit anderen Aktionen, Subskriptionen ausgeschlossen.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gütlig einmalig bis zum {$datevalide} auf unsere vollständige Internetseite, ausgenommen sind bereits rabattierte Produkte und Primeursubskriptionen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*ausgeschlossen sind Subskriptionsweine und bereits reduzierte Weine. Dieses Angebot ist bis zum {$datevalide} Gültig.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Jedes Werk hat ein Echtheitszertifikat.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gültig für einmalige Anwendung ab 400€ lieferbarem Wein (außer Subskriptionsweine) - solange der Vorrat reicht','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gültig für Bestellungen ab 300 €, nur lieferbare Weine, Subskriptionen ausgeschlossen und nur für die 50 ersten Bestellungen','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gültig für Mischkisten, ausgenommen Primeurweine und bereits bestehende Sonderangebote!','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*ausgenommen sind bereits reduzierte Weine, Subskriptionen 2016 und 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Sehr begrenzte Mengen und lieferbar solange der Vorrat reicht. Einige Weine lagern direkt im Weingut. Die Lieferung in unsere Weinkeller erfolgt innerhalb von 45 Tagen, dazu kommt die übliche Lieferzeit zu Ihnen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Mengen extrem limitiert','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Angebot gültig, solange der Vorrat reicht, nicht kumulierbar mit anderen Aktionen, Subskriptionen ausgeschlossen.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Angebot gütlig einmalig bis zum {$datevalide} auf unsere vollständige Internetseite, ausgenommen sind bereits rabattierte Produkte und Primeursubskriptionen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*ausgeschlossen sind Subskriptionsweine und bereits reduzierte Weine. Dieses Angebot ist bis zum {$datevalide} Gültig.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Jedes Werk hat ein Echtheitszertifikat.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Angebot gültig für einmalige Anwendung ab CHF 450 lieferbarem Wein (ausser Subskriptionsweine) - solange der Vorrat reicht','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Primeurweine ausgeschlossen - Gültig bis zum 31/08/2018','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Angebot gültig für Mischkisten, ausgenommen Primeurweine und bereits bestehende Sonderangebote!','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*ausgenommen sind bereits reduzierte Weine, Subskriptionen 2016 und 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Sehr begrenzte Mengen und lieferbar solange der Vorrat reicht. Einige Weine lagern direkt im Weingut. Die Lieferung in unsere Weinkeller erfolgt innerhalb von 45 Tagen, dazu kommt die übliche Lieferzeit zu Ihnen.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Mengen extrem limitiert','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Offer valid on one purchase placed on or before {$datevalide}. Does not include en primeur or previously discounted wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Each limited edition case comes with a certificate of authenticity.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Offer valid once per customer on an order of £ 280.00 or more (not including en primeur wines), according to availability','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Excluding en primeur wines. Valid through 31/08/18','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Valid only on our mixed \"my own tasting cases.\" Not combinable with other promotional offers and exclusive of en primeur wines','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','* Excluding previously discounted wines, 2016 and 2017 en primeur wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Extremely limited quantities available)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Each limited edition case comes with a certificate of authenticity.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Offer valid once per customer on an order of € 400.00 or more (not including en primeur wines), according to availability','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Excluding en primeur wines. Valid through 31/08/18','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Valid only on our mixed \"my own tasting cases.\" Not combinable with other promotional offers and exclusive of en primeur wines','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','* Excluding previously discounted wines, 2016 and 2017 en primeur wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Extremely limited quantities available','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Each limited edition case comes with a certificate of authenticity.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Minimum order of HKD 4,000.00. Not cumulative with other promotional codes. Not valid on en primeur or previously discounted wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Offer valid once per customer on an order of HK$ 3000.00 or more (not including en primeur wines), according to availability','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Offer valid on an order of HKD 2800.00 or more (not including en primeur or previously discounted wines. Not combinable with other promotional codes).','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Valid only on our mixed \"my own tasting cases.\" Not combinable with other promotional offers and exclusive of en primeur wines','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','* Excluding previously discounted wines, 2016 and 2017 en primeur wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Extremely limited quantities available','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Valid while stocks remain. Not combinable with other promotional codes. Excluding en primeur and previously discounted wines.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Offer valid on one purchase placed on or before {$datevalide}. Does not include en primeur or previously discounted wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Does not include previously discounted and en primeur wines. Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Each limited edition case comes with a certificate of authenticity.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Minimum order of SGD 650.00. Not cumulative with other promotional codes. Not valid on en primeur or previously discounted wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Offer valid once per customer on an order of SGD 600.00 or more (not including en primeur wines), according to availability','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Offer valid on an order of SGD 500.00 or more (not including en primeur or previously discounted wines. Not combinable with other promotional codes).','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Valid only on our mixed \"my own tasting cases.\" Not combinable with other promotional offers and exclusive of en primeur wines','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','* Excluding previously discounted wines, 2016 and 2017 en primeur wines.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Very limited quantities, orders subject to availability. Certain vintages are still at the producing estate, and therefore subject to an additional delivery delay. Delivery estimated in 45 days, plus the average delivery delay depending on the country.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Extremely limited quantities available','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta a disponibilità limitata, escluse le promozioni in corso e i vini in primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta valida una sola volta e fino al {$datevalide} incluso su tutto il sito (salvo vini già in promozione e vini primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Codice non valido per i vini già in promozione e per i vini primeurs. Offerta valida fino al {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Ogni opera ha il suo proprio certificato di autenticità.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta valida a partire da 300€ di spesa, esclusi vini Primeurs, nel limite degli stock disponibili.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta valida a partire da 300 € di vini consegnabili (promozioni e vini in primeurs esclusi)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta valida solo sulle casse miste, non cumilabile con altre promozioni ed escllusi i vini in primeurs.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Non riguarda i secondi vin già in offerta, i primeurs 2016 e 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*In quantità molto limitata e secondo disponibilità. Alcuni di questi vini si trovano nelle cantine della proprietà. La consegna sarà effettuta in 45 giorni a partire dalla nostra cantina + il tempo necessario per ogni Paese.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*In quantità estremamente limitata.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Oferta válida en el límite de las existencias disponibles, no se refiere a los vinos en oferta especial o en primeurs.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Oferta válida una sóla vez hasta el {$datevalide} en todo el sito (no se refiere a los vinos en oferta especial o en primeurs).','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*No se refiere a las ofertas especiales o los vinos en primeurs. Oferta válida hasta el {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Cada obra tiene su propio certificado de autenticidad.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Oferta válida una sola vez por cliente a partir de 300 € de vinos listos para la entrega (no se refiere a los vinos en primeurs), en el límite de las existencias disponibles.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Oferta válida a partir de 300€ de vinos disponibles para la entrega (no se refiere a los vinos en primeurs o en oferta especial).','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Sólo válido en las cajas mixtas, no se refiere a los vinos en primeurs.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*No se refiere a vinos en oferta especial, vinos en primeurs 2016 y 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Cantidades muy limitadas y dependiendo de las existencias disponibles. Ciertos vinos están almacenados en la bodega del château. La entrega en nuestra bodega se efectuará en un plazo de 45 días y tendremos que añadir el plazo de entrega según la dirección de entrega.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Cantidad muy limitada.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*Oferta válida limitada aos stocks existentes, e não aplicável aos vinhos em promoção ou em Primeur.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','* Oferta válida até {$datevalide}, uma vez em todo o site (excluindo promoções e vinhos jovens)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*Promoção não cumulativa com qualquer outra promoção em vigor e fora dos vinhos em primeurs. Oferta válida até {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*Cada obra possui o seu próprio certificado de autenticidade.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*Oferta válida para encomenda a partir de 400 euros de vinhos disponíveis (com exclusão dos vinhos em primeurs), de acordo com o stock disponível.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','* Oferta válida a partir de 300 € de vinhos disponíveis (excluindo vinhos em promoção e vinhos em primeurs)','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*applicàvel unicamente para as caixas personalizadas e fora dos primeurs e dos vinhos já em promoção.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*fora dos vinhos já em promoção e dos primeurs 2016 e 2017.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','* Quantidades muito limitadas e sob reserva de disponibilidade. Alguns vinhos são armazenados na adega do Château Yquem, a entrega será prolongada de 45 dias, à confirmar.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','* Quantidades extremamente limitadas','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid on select items.','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*This offer is valid on select items. Please also note that we currently advise against shipping wine in some areas of the country due to freezing temperatures. Exposure to below freezing temperatures could harm the wine during transit. We are happy to hold your order until temperature becomes moderate, but please be aware this may not be until the spring.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid on select items. Millesima will hold wines until weather permitted if needed.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid from 06/30/2018 to 07/02/2018, 11:59 pm. Excluding items already on sale and futures wines. Offer valid once per user only. Not valid on delivery of wooden cases. Millesima will hold wines until weather permitted, if needed.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid until Monday, November 27th, 2017,11:59 pm EST. Offer valid on select items only.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid on all items excluding futures and on sale items','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid on select items only. Because of extreme tempertures during the summer, Millesima may advise to hold shipment until the fall.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Deliveries in the US are scheduled in Spring 2018.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*$100 discount for a minimum purchase of $700 or more. Excluding futures and on sale items. Not combinable with other promo codes. Offer valid from March 30th to April 2nd midnight. Offer valid once per customer.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Offer valid on one purchase <strong>totaling $500 or more before tax and/or shipping</strong> placed <strong>between July 8th and July 12th, 2017</strong>. Does not include futures or on sale items. Not valid on previous orders.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*To ensure delivery of in-stock items in time for the holidays, orders must be placed no later than Sunday December 18th. For an estimated arrival date to your shipping address, please call us at 212-639-9463.','2019-03-12 00:00:00','2019-03-23 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','*Terms and Conditions for Promotion WELCOMEMAY:<br />The free shipping offer is valid on your orders over $100 or more before taxes, per shipping address. Valid on orders placed this weekend from 04/28/2018 to 04/30/2018. Redeem the offer with promotion code WELCOMEMAY at checkout. Excludes Futures and delivery of original wooden cases. Offer expires at 11:59 p.m. ET, 04/30/18. Offer is not valid on previously purchases. Millesima will hold wines until weather permitted if needed.','2019-03-23 00:00:00',NULL);

insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*Hors vins primeurs - En une seule fois à une seule adresse en Belgique. Valable jusqu’au {$datevalide} inclus.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*Hors vins primeurs - En une seule fois à une seule adresse en Belgique. Valable jusqu’au {$datevalide} inclus.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*Hors vins primeurs - En une seule fois à une seule adresse au Luxembourg. Valable jusqu’au {$datevalide} inclus.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*Hors vins primeurs - En une seule fois à une seule adresse en Suisse. Valable jusqu’au {$datevalide} inclus.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Deutschland.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in Österreich.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Primeurweine ausgeschlossen - Lieferung an eine einzige Adresse in der Schweiz. Gültig bis {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Excluding en primeur wines. Free delivery on one order to one address - Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Excluding en primeur wines. Free delivery on one order to one address - Valid till {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Excluding en primeur wines. Free delivery on one order to one address','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Excluding en primeur wines. Free delivery on one order to one address','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Non riguarda i vini primeur. Per un\'unica consegna ad un solo indirizzo.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*No incluye los vinos en primeur. Para una sola expedición a una única dirección.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','*Fora dos vinhos primeurs, entrega numa morada em Portugal Continental. Válida até {$datevalide}.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','','2019-03-12 00:00:00','2019-03-12 00:00:00');


insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','F','*valable jusqu’au {$datevalide} inclus','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','B','*valable jusqu’au {$datevalide} inclus','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','L','*valable jusqu’au {$datevalide} inclus','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SF','*valable jusqu’au {$datevalide} inclus','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','D','*Angebot gültig für eine Auswahl an Produkten mit limitiertem Lagerbestand von 23.01.16 bis 25.01.16','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','O','*Angebot gültig für eine Auswahl an Produkten mit limitiertem Lagerbestand von 23.01.16 bis 25.01.16','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SA','*Angebot gültig für eine Auswahl an Produkten mit limitiertem Lagerbestand von 23.01.16 bis 25.01.16','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','G','*Special offer valid on a selection of product in limited availability from the 23.01.2016 to the 25.01.2016 included.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','I','*Special offer valid on a selection of product in limited availability from the 23.01.2016 to the 25.01.2016 included.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','H','*Special offer valid on a selection of product in limited availability from the 23.01.2016 to the 25.01.2016 included.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','SG','*Special offer valid on a selection of product in limited availability from the 23.01.2016 to the 25.01.2016 included.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','Y','*Offerta valida per una selezione di vini in quantità limitata dal 23/01/2016 fino al 25/01/2016','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','E','*Oferta válida en una selección de vinos en cantidad limitada desde el 23/01/2016 hasta el 25/01/16','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','P','* Promoção válida sobre uma selecção de produtos, em quantidade limitada do 23/01/16 ao 25/01/16 incluído.','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_description','U','','2019-03-12 00:00:00','2019-03-12 00:00:00');





insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','F','*Offre valable dans la limite des stocks disponibles','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','B','*Offre valable dans la limite des stocks disponibles','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','L','*Offre valable dans la limite des stocks disponibles','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','SF','*Offre valable dans la limite des stocks disponibles','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','D','*Angebot gültig, solange der Vorrat reicht','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','O','*Angebot gültig, solange der Vorrat reicht','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','SA','*Angebot gültig, solange der Vorrat reicht','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','G','*According to availability','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','I','*According to availability','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','H','*According to availability','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','SG','*According to availability','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','Y','*Offerta valida nel limite degli stock disponibili','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','E','*Ofertas válidas en el límite de las existencias disponibles','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','P','*Oferta valida de acordo com o estoque disponível','2019-03-12 00:00:00',NULL);
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_articles','U','*Offer valid on select items only','2019-03-22 00:00:00','2019-03-29 00:00:00');


insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_article1','F','*Offre valable dans la limite des stocks disponibles jusqu\'au {$datevalide}','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_article1','B','*Offre valable dans la limite des stocks disponibles jusqu\'au {$datevalide}','2019-03-12 00:00:00','2019-03-12 00:00:00');
insert into `emailing`.`ressource` (`name`, `store`, `value`, `start_date`, `end_date`) values('ast_article3','P','*Oferta valida de acordo com o estoque disponível até {$datevalide}','2019-03-12 00:00:00','2019-03-12 00:00:00');


























