-- ----------------------------------------------------------
-- Script MYSQL pour mcd 
-- ----------------------------------------------------------


-- ----------------------------
-- Table: type_prise
-- ----------------------------
CREATE TABLE type_prise (
  type_prise VARCHAR(10) NOT NULL,
  CONSTRAINT type_prise_PK PRIMARY KEY (type_prise)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: type_paiement
-- ----------------------------
CREATE TABLE type_paiement (
  type_paiement VARCHAR(50) NOT NULL,
  CONSTRAINT type_paiement_PK PRIMARY KEY (type_paiement)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: departement
-- ----------------------------
CREATE TABLE departement (
  code_dep VARCHAR(10) NOT NULL,
  nom_departement VARCHAR(50) NOT NULL,
  CONSTRAINT departement_PK PRIMARY KEY (code_dep)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: condition_acces
-- ----------------------------
CREATE TABLE condition_acces (
  condition_acces VARCHAR(20) NOT NULL,
  CONSTRAINT condition_acces_PK PRIMARY KEY (condition_acces)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: implantation
-- ----------------------------
CREATE TABLE implantation (
  implantation_station VARCHAR(50) NOT NULL,
  CONSTRAINT implantation_PK PRIMARY KEY (implantation_station)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: enseigne
-- ----------------------------
CREATE TABLE enseigne (
  nom_enseigne VARCHAR(255) NOT NULL,
  CONSTRAINT enseigne_PK PRIMARY KEY (nom_enseigne)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: horaires
-- ----------------------------
CREATE TABLE horaires (
  horaires VARCHAR(255) NOT NULL,
  CONSTRAINT horaires_PK PRIMARY KEY (horaires)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: acteur
-- ----------------------------
CREATE TABLE acteur (
  id_acteur INT NOT NULL AUTO_INCREMENT,
  nom VARCHAR(50),
  siren VARCHAR(50),
  contact VARCHAR(255),
  telephone VARCHAR(50),
  role VARCHAR(20) NOT NULL,
  CONSTRAINT acteur_PK PRIMARY KEY (id_acteur)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: commune
-- ----------------------------
CREATE TABLE commune (
  code_insee_commune VARCHAR(10) NOT NULL,
  nom_commune VARCHAR(50) NOT NULL,
  code_postal VARCHAR(10) NOT NULL,
  code_dep VARCHAR(10),
  CONSTRAINT commune_PK PRIMARY KEY (code_insee_commune),
  CONSTRAINT commune_code_dep_FK FOREIGN KEY (code_dep) REFERENCES departement (code_dep)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: station
-- ----------------------------
CREATE TABLE station (
  id_station_itinerance VARCHAR(50) NOT NULL,
  id_station_local VARCHAR(50),
  nom_station VARCHAR(255) NOT NULL,
  adresse_station VARCHAR(255) NOT NULL,
  nbre_pdc INT NOT NULL,
  date_mise_en_service DATE,
  implantation_station VARCHAR(50),
  code_insee_commune VARCHAR(10),
  id_acteur INT,
  id_acteur_operateur INT,
  horaires VARCHAR(255),
  nom_enseigne VARCHAR(255),
  CONSTRAINT station_PK PRIMARY KEY (id_station_itinerance),
  CONSTRAINT station_implantation_station_FK FOREIGN KEY (implantation_station) REFERENCES implantation (implantation_station),
  CONSTRAINT station_code_insee_commune_FK FOREIGN KEY (code_insee_commune) REFERENCES commune (code_insee_commune),
  CONSTRAINT station_id_acteur_FK FOREIGN KEY (id_acteur) REFERENCES acteur (id_acteur),
  CONSTRAINT station_id_acteur_operateur_FK FOREIGN KEY (id_acteur_operateur) REFERENCES acteur (id_acteur),
  CONSTRAINT station_horaires_FK FOREIGN KEY (horaires) REFERENCES horaires (horaires),
  CONSTRAINT station_nom_enseigne_FK FOREIGN KEY (nom_enseigne) REFERENCES enseigne (nom_enseigne)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: point_de_recharge
-- ----------------------------
CREATE TABLE point_de_recharge (
  id INT NOT NULL,
  puissance_nominale DECIMAL(10,2) NOT NULL,
  cable_t2_attache TINYINT(1),
  gratuit TINYINT(1),
  tarification VARCHAR(255),
  consolidated_longitude FLOAT NOT NULL,
  consolidated_latitude FLOAT NOT NULL,
  id_station_itinerance VARCHAR(50),
  condition_acces VARCHAR(20),
  CONSTRAINT point_de_recharge_PK PRIMARY KEY (id),
  CONSTRAINT point_de_recharge_id_station_itinerance_FK FOREIGN KEY (id_station_itinerance) REFERENCES station (id_station_itinerance),
  CONSTRAINT point_de_recharge_condition_acces_FK FOREIGN KEY (condition_acces) REFERENCES condition_acces (condition_acces)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: point_recharge_paiement
-- ----------------------------
CREATE TABLE point_recharge_paiement (
  type_paiement VARCHAR(50) NOT NULL,
  id INT NOT NULL,
  CONSTRAINT point_recharge_paiement_PK PRIMARY KEY (type_paiement, id),
  CONSTRAINT point_recharge_paiement_type_paiement_FK FOREIGN KEY (type_paiement) REFERENCES type_paiement (type_paiement),
  CONSTRAINT point_recharge_paiement_id_FK FOREIGN KEY (id) REFERENCES point_de_recharge (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: point_recharge_prise
-- ----------------------------
CREATE TABLE point_recharge_prise (
  id INT NOT NULL,
  type_prise VARCHAR(10) NOT NULL,
  CONSTRAINT point_recharge_prise_PK PRIMARY KEY (id, type_prise),
  CONSTRAINT point_recharge_prise_id_FK FOREIGN KEY (id) REFERENCES point_de_recharge (id),
  CONSTRAINT point_recharge_prise_type_prise_FK FOREIGN KEY (type_prise) REFERENCES type_prise (type_prise)
)ENGINE=InnoDB;