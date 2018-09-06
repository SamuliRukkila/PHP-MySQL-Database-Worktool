<?php

/* This file contains four ready-made variables which can be used to give content
to empty databases. It is automatically included within the databases.php - class. */

$basketball =
    "CREATE TABLE Pelipaikka (
    pelipaikka INT NOT NULL AUTO_INCREMENT,
    nimi VARCHAR(20) NOT NULL,
    PRIMARY KEY (pelipaikka)
    ) ENGINE=INNODB;
    CREATE TABLE Pelaaja (
    pelaaja INT NOT NULL AUTO_INCREMENT,
    etunimi VARCHAR(20) NOT NULL,
    sukunimi VARCHAR(30) NOT NULL,
    syntymaaika DATE,
    numero INT,
    pituus INT,
    paino INT,
    pelipaikka INT NOT NULL,
    PRIMARY KEY (pelaaja),
    CONSTRAINT pelaaja_pelipaikka_fk FOREIGN KEY (pelipaikka) REFERENCES Pelipaikka(pelipaikka)
       ON UPDATE CASCADE ON DELETE RESTRICT
    ) ENGINE=INNODB;
    CREATE TABLE Sponsori (
    sponsori INT NOT NULL AUTO_INCREMENT,
    nimi VARCHAR(30),
    PRIMARY KEY (sponsori)
    ) ENGINE=INNODB;
    CREATE TABLE PelaajaSponsori (
    pelaajasponsori INT AUTO_INCREMENT NOT NULL,
    pelaaja INT NOT NULL,
    sponsori INT NOT NULL,
    summa DECIMAL(10,2),
    tila CHAR(1),
    PRIMARY KEY (pelaajasponsori),
    CONSTRAINT pelaajasponsori_pelaaja_fk FOREIGN KEY (pelaaja) REFERENCES Pelaaja(pelaaja)
       ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT pelaajasponsori_sponsori_fk FOREIGN KEY (sponsori) REFERENCES Sponsori(sponsori)
       ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=INNODB;
    INSERT INTO Pelipaikka(pelipaikka, nimi) VALUES
    (1, 'Point Guard'),
    (2, 'Shoot Guard'),
    (3, 'Small Forward'),
    (4, 'Power Forward'),
    (5, 'Center');
    INSERT INTO Pelaaja(pelaaja, etunimi, sukunimi, syntymaaika, numero, pituus, paino, pelipaikka) VALUES
    (1, 'Armas', 'Ahtonen', '1984-04-11', 1, 180, 76, 1),
    (2, 'Bob', 'Bobrikov', '1970-09-18', 10, 210, 113, 3),
    (3, 'Heikki', 'Huhtanen', '1987-01-26', 3, 188, 90, 1),
    (4, 'Iiro', 'Itäluoma', '1968-02-26', NULL, 166, 70, 5),
    (5, 'Ville', 'Virtanen', '1990-02-13', NULL, NULL, NULL, 2),
    (6, 'Ville', 'Virtanen', '1977-09-10', 99, 205, 100, 5),
    (7, 'Vilho', 'Viinikainen', '1982-04-04', NULL, NULL, NULL, 5);
    INSERT INTO Sponsori(sponsori, nimi) VALUES
    (1, 'Einon Makkara oy'),
    (2, 'Annelin hoivapalvelut ky'),
    (3, 'Keski-Suomen mattokude ay'),
    (4, 'Tmi DJ Jouni-Kullervo');
    INSERT INTO PelaajaSponsori(pelaajasponsori, pelaaja, sponsori, summa, tila) VALUES
    (1, 4, 2, 1000, 'A'),
    (2, 5, 1, 2000, 'A'),
    (3, 2, 2, 3000, 'A'),
    (4, 2, 2, 2500, 'V'),
    (5, 7, 4, 100, 'V');";

$chainstore =
    "CREATE TABLE Toimipiste (
        toimipiste INT AUTO_INCREMENT,
        myymalanro CHAR(6) NOT NULL UNIQUE,
        nimi  VARCHAR(50) NOT NULL,
        osoite VARCHAR(30) NOT NULL,
        postinro CHAR(5) NOT NULL,
        postitp VARCHAR(20) NOT NULL,
        PRIMARY KEY (toimipiste)
    ) ENGINE=INNODB;
    CREATE TABLE Tuoteryhma (
        tuoteryhma  INT AUTO_INCREMENT,
        nimi        VARCHAR(50) NOT NULL,
        PRIMARY KEY (tuoteryhma)
    ) ENGINE=INNODB;
    CREATE TABLE Tuote (
        tuote VARCHAR(20),
        nimi  VARCHAR(50) NOT NULL,
        hinta DECIMAL(10,2) NOT NULL,
        tuoteryhma INT NOT NULL,
        PRIMARY KEY (tuote),
        CONSTRAINT fk_tuote_tuoteryhma
          FOREIGN KEY (tuoteryhma) REFERENCES Tuoteryhma(tuoteryhma)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
    ) ENGINE=INNODB;
    CREATE TABLE Henkilosto (
        henkilosto      INT AUTO_INCREMENT,
        sotu            CHAR(11) NOT NULL UNIQUE,
        etunimi         VARCHAR(50) NOT NULL,
        sukunimi        VARCHAR(50) NOT NULL,
        osoite          VARCHAR(30) NOT NULL,
        postinro        CHAR(5) NOT NULL,
        postitp         VARCHAR(20) NOT NULL,
        toimipiste      INT NOT NULL,
        PRIMARY KEY (henkilosto),
        CONSTRAINT fk_henkilosto_toimipiste
          FOREIGN KEY (toimipiste) REFERENCES Toimipiste(toimipiste)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
    ) ENGINE=INNODB;
    INSERT INTO Toimipiste (myymalanro, nimi, osoite, postinro, postitp)
    	VALUES  ('313011', 'Maken kauppa', 'Kauppakatu 23', '40100', 'Jyväskylä'),
    			    ('313022', 'Ostosretiisi', 'Ostoskatu 50', '40640', 'Jyväskylä'),
    			    ('313033', 'Marketti', 'Myyntikuja 6', '40200', 'Jyväskylä');
    INSERT INTO Tuoteryhma (nimi)
      VALUES  ('Maitotuotteet'),
              ('Alkoholijuomat'),
              ('Kahvit'),
              ('Leivät'),
              ('Pakasteet'),
              ('Hygienia'),
              ('Herkut'),
              ('Lehdet');
    INSERT INTO Tuote (tuote, nimi, hinta, tuoteryhma)
      VALUES  ('6415712506032', 'Kotimaista Rasvaton Maito 1l', 0.69, 1),
              ('6415715517401', 'Kotimaista Kevyt Maito 1l', 0.79, 1),
              ('6415709325219', 'Herkku Rahka 200g', 0.87, 1),
              ('6415732154215', 'Laktoositon Ruokakerma 2dl', 0.52, 1),
              ('6415754885231', 'Kuohukerma 2dl', 0.99, 1),
              ('6415700932212', 'Arla Fetakuutiot 150g', 2.59, 1),
              ('6413665421311', 'Hartwall Cool Grape Long Drink 5,5% 0,5l', 3.70, 2),
              ('6413632132334', 'Olvi Sandels 4,7% 0,33l', 1.23, 2),
              ('6413671551769', 'Olvi III-olut 4,5% 0,33l', 0.89, 2),
              ('6413612124558', 'Sinebrychoff Crowmoor Dry Apple 4,7% 0,5l', 3.49, 2),
              ('6413632149877', 'Olvi A. Le Coq Alkoholiton 0,0% 0,33l', 0.79, 2);
    INSERT INTO Henkilosto (sotu, etunimi, sukunimi, osoite, postinro, postitp, toimipiste)
      VALUES  ('201095-103F', 'Matias', 'Metsäjätti', 'Uusitie 40', '42240', 'Tikkakoski', 1),
              ('121200-590F', 'Jenni', 'Jakku', 'Porraskuja 13', '41000', 'Valkola', 1),
              ('300187-204W', 'Risto', 'Rillitin', 'Uusjoentie 4', '43510', 'Kanavuori', 1),
              ('010160-978Y', 'Teppo', 'Tärpätti', 'Torikatu 40A 50', '40740', 'Tikkala', 1),
              ('280199-972S', 'Piitu', 'Pinakka', 'Lehtokuja 3', '40470', 'Jyväskylä', 2),
              ('280199-976W', 'Talevi', 'Talvi', 'Turmiontie 1', '40185', 'Pieksämäki', 2);";


$steam =
    "CREATE TABLE Kayttaja (
    kayttaja			INT NOT NULL,
    tunnus            	VARCHAR(33) NOT NULL,
    salasana            VARCHAR(33) NOT NULL,
    nimi            	VARCHAR(33) NOT NULL,
    sposti            	VARCHAR(33) NOT NULL,
    maa    				VARCHAR(33),
    liittymispvm	    DATE,
    PRIMARY KEY (kayttaja)
  ) ENGINE=INNODB;
    CREATE TABLE Pelistudio (
    pelistudio 			INT NOT NULL,
    nimi				VARCHAR(33) NOT NULL,
    perustamisvuosi		INT NOT NULL,
    PRIMARY KEY (pelistudio)
  ) ENGINE=INNODB;
    CREATE TABLE Genre (
    genre             	INT NOT NULL,
    nimi              	VARCHAR(33) NOT NULL,
    PRIMARY KEY (genre)
    ) ENGINE=INNODB;
    CREATE TABLE Peli (
    peli             	INT NOT NULL,
    nimi              	VARCHAR(33) NOT NULL,
    julkaisuvuosi		INT,
    hinta	            DECIMAL(5,2),
    pelistudio			INT NOT NULL,
    PRIMARY KEY (peli),
    FOREIGN KEY	(pelistudio) REFERENCES Pelistudio(pelistudio)
    ) ENGINE=INNODB;
    INSERT INTO Kayttaja(kayttaja, tunnus, salasana, nimi, sposti, maa, liittymispvm)
    VALUES (1, 'keijokeijo', 'salasana1', 'Keke', 'k.keinonen@osoite.com', NULL, '2008-04-05'),
    (2, 'ainoaitola', 'salasana2', 'Ainooo', 'a.aitola@osoite.com', 'Suomi', '2010-12-20'),
    (3, 'erkintunnari', 'salasana3', 'Ege', 'e.erkkinen@osoite.com', 'Suomi', '2015-08-17'),
    (4, 'outoneno', 'salasana4', 'Outi', 'o.outonen@osoite.com', NULL, '2012-02-16'),
    (5, 'jeanclaude2','motdetpasse5', 'Jean', 'j.claude@adresse.com', 'Ranska', '2014-01-15'),
    (6, 'heidih', 'passwort6', 'Heidi:)', 'h.heinzel@adresse.com', 'Saksa', '2016-03-3'),
    (7, 'rumiko27', 'pasuwaado7', 'Rumiko', 'r.takahashi@atena@com', 'Japani', '2017-09-12'),
    (8, 'uunou', 'salasana8', 'Uuno', 'u.ujonen@osoite.com', 'Suomi', '2018-04-08');
    INSERT INTO Pelistudio(pelistudio, nimi, perustamisvuosi)
    VALUES (1, 'CD PROJEKT RED', 1994),
    (2, 'Bioware', 1995),
    (3, 'Firaxis Games', 1996),
    (4, 'Valve', 1996),
    (5, 'Trendy Entertainment', 2009),
    (6, 'Subset Games', 2010),
    (7, 'New Indie', 2017);
    INSERT INTO Genre(genre, nimi)
    VALUES (1, 'rpg'),
    (2, 'action'),
    (3, 'turn-based'),
    (4, 'strategy'),
    (5, 'puzzle'),
    (6, 'first-person'),
    (7, 'FPS'),
    (8, 'tower defense'),
    (9, 'co-op'),
    (10, 'rogue-like'),
    (11, 'indie'),
    (12, 'open world');
    INSERT INTO Peli(peli, nimi, julkaisuvuosi, hinta, pelistudio)
    VALUES (1, 'Mass Effect', 2008, 9.99, 2),
    (2, 'Dragon Age Origins', 2009, 19.99, 2),
    (3, 'Witcher 3', 2015, 29.99, 1),
    (4, 'Sid Meiers Civilization V', 2010, 29.99, 3),
    (5, 'XCOM 2', 2016, 49.99, 3),
    (6, 'Portal', 2007, 9.99, 4),
    (7, 'Half-Life', 1998, 9.99, 4),
    (8, 'Team Fortress 2', 2007, 0.00, 4),
    (9, 'Dungeon Defenders', 2011, 11.99, 5),
    (10, 'Dungeon Defenders II', 2017, 0.00, 5),
    (11, 'FTL: Faster Than Light', 2012, 9.99, 6),
    (12, 'Into The Breach', 2018, 14.99, 6),
    (13, 'Battle Monsters Beta', NULL, NULL, 7);";

$library =
  "CREATE TABLE Asiakas (
  asiakas             CHAR(11) NOT NULL,
  etunimi             VARCHAR(20) NOT NULL,
  sukunimi            VARCHAR(40) NOT NULL,
  lahiosoite          VARCHAR(50) NOT NULL,
  postinumero         INT NOT NULL,
  postitoimipaikka    VARCHAR(20) NOT NULL,
  sakot_yhteensa      DECIMAL(10,2) DEFAULT 0,
  PRIMARY KEY (asiakas)
  ) ENGINE=INNODB;
  CREATE TABLE Puhelin (
  puhelin             INT AUTO_INCREMENT NOT NULL,
  puhnro              VARCHAR(20) NOT NULL,
  asiakas             CHAR(11) NOT NULL,
  PRIMARY KEY (puhelin),
  CONSTRAINT puhelin_asiakas_fk
     FOREIGN KEY (asiakas) REFERENCES Asiakas(asiakas)
        ON UPDATE CASCADE
        ON DELETE CASCADE
  ) ENGINE=INNODB;
  CREATE TABLE Nimeke (
  nimeke              VARCHAR(20) NOT NULL,
  nimi                VARCHAR(50) NOT NULL,
  etunimi             VARCHAR(20),
  sukunimi            VARCHAR(50),
  kustantaja          VARCHAR(50),
  luokitus            VARCHAR(10),
  PRIMARY KEY (nimeke)
  ) ENGINE=INNODB;
  CREATE TABLE Nide (
  nide                INT AUTO_INCREMENT NOT NULL,
  korjauspaiva        DATE,
  sijainti            VARCHAR(10),
  nimeke              VARCHAR(20) NOT NULL,
  PRIMARY KEY (nide),
  CONSTRAINT nide_nimeke_fk
     FOREIGN KEY (nimeke) REFERENCES Nimeke(nimeke)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
  ) ENGINE=INNODB;
  CREATE TABLE Lainaus (
  lainaus             INT AUTO_INCREMENT NOT NULL,
  lainauspaiva        DATE NOT NULL,
  erapaiva            DATE NOT NULL,
  asiakas             CHAR(11) NOT NULL,
  PRIMARY KEY (lainaus),
  CONSTRAINT lainaus_asiakas_fk
     FOREIGN KEY (asiakas) REFERENCES Asiakas(asiakas)
        ON UPDATE CASCADE
        ON DELETE CASCADE
  ) ENGINE=INNODB;
  CREATE TABLE LainausNide (
  lainaus             INT NOT NULL,
  nide                INT NOT NULL,
  PRIMARY KEY (lainaus, nide),
  CONSTRAINT lainausnide_lainaus_fk
     FOREIGN KEY (lainaus) REFERENCES Lainaus(lainaus)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
  CONSTRAINT lainausnide_nide_fk
     FOREIGN KEY (nide) REFERENCES Nide(nide)
        ON UPDATE CASCADE
        ON DELETE CASCADE
  ) ENGINE=INNODB;
  INSERT INTO Asiakas(asiakas, etunimi, sukunimi, lahiosoite, postinumero, postitoimipaikka) VALUES
  ('010180-111A', 'Pekka', 'Pekkanen', 'Kotikatu 1', 40100, 'Jyväskylä'),
  ('311285-999Z', 'Anneli', 'Auvinen', 'Koulukatu 2', 40100, 'Jyväskylä'),
  ('111070-222B', 'Mauno', 'Manninen', 'Kauppakatu 1 A 1', 40100, 'Jyväskylä'),
  ('150575-333C', 'Riitta', 'Ruuskanen-Rantanen', 'Kirkkotie 5', 40500, 'Jyväskylä'),
  ('060695-444D', 'Keijo', 'Karhunen', 'Keskustie 1', 40950, 'Muurame'),
  ('070790-555E', 'Sinikka', 'Sinkkonen', 'Kotikatu 1', 40100, 'Jyväskylä');

  INSERT INTO Puhelin(puhnro, asiakas) VALUES
  ('040-1111222', '010180-111A'),
  ('050-1111222', '010180-111A'),
  ('041-6667777', '150575-333C'),
  ('040-9998888', '070790-555E'),
  ('050-5656567', '311285-999Z'),
  ('040-7777777', '111070-222B'),
  ('041-8888888', '111070-222B');

  INSERT INTO Nimeke(nimeke, nimi, etunimi, sukunimi, kustantaja, luokitus) VALUES
  ('1111-2222-333', 'SQL-Opas', 'Ari', 'Hovi', 'Nörttikustannus oy', '11.1'),
  ('2222-3333-444', 'Esirippu', 'Agatha', 'Christie', 'Kustannusosakeyhtiö Jännärit', '84.2'),
  ('3333-4444-555', 'Kuolema Niilillä', 'Agatha', 'Christie', 'Kustannusosakeyhtiö Jännärit', '84.2'),
  ('4444-5555-666', 'Python-ohjelmointi', 'Pertti', 'Python', 'Nörttikustannus oy', '11.1'),
  ('5555-6666-777', 'Suomen linnut', 'Lauri', 'Laihonen', 'Oy Luontokirjat Ab', '55.5'),
  ('6666-7777-888', 'TV:n historia', 'Tellervo', 'Tarvainen', 'Omakustanne', '66.6');

  INSERT INTO Nide(nide, korjauspaiva, sijainti, nimeke) VALUES
  (1, NULL, 'Hyllyssä', '4444-5555-666'),
  (2, NULL, 'Hyllyssä', '4444-5555-666'),
  (1000, '2010-12-12', 'Hyllyssä', '6666-7777-888'),
  (1001, NULL, 'Lainassa', '5555-6666-777'),
  (112345, '2010-05-04', 'Lainassa', '1111-2222-333'),
  (112346, '2010-05-04', 'Hyllyssä', '1111-2222-333'),
  (199999, NULL, 'Hyllyssä', '2222-3333-444'),
  (200000, NULL, 'Hyllyssä', '3333-4444-555'),
  (200001, NULL, 'Lainassa', '3333-4444-555'),
  (200002, NULL, 'Hyllyssä', '3333-4444-555'),
  (200003, NULL, 'Hyllyssä', '3333-4444-555'),
  (200004, NULL, 'Hyllyssä', '3333-4444-555'),
  (200005, NULL, 'Hyllyssä', '3333-4444-555');

  INSERT INTO Lainaus(lainaus, lainauspaiva, erapaiva, asiakas) VALUES
  (1, '2011-01-12', '2011-02-12', '010180-111A'),
  (2, '2011-01-12', '2011-02-12', '311285-999Z'),
  (3, '2011-02-01', '2011-03-01', '111070-222B'),
  (4, '2011-03-01', '2011-04-01', '010180-111A');

  INSERT INTO LainausNide(lainaus, nide) VALUES
  (1, 112345),
  (1, 1000),
  (1, 200005),
  (1, 1001),
  (2, 199999),
  (3, 112345),
  (3, 200001),
  (4, 199999);"

;
?>
