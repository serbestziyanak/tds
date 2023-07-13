<?php
ob_start();
 if ( session_status() == PHP_SESSION_NONE ) {
	session_start();
}
include 'veritabani.php';
/*
	Session tanımlaması yapıyoruz. Çünkü henüz giriş yapmadığı zaman session tanmsız oluyor. 
	Biz de tanımlayıp default değerini false yapıyoruz. 
*/

$_SESSION[ 'giris' ] = array_key_exists( 'giris', $_SESSION ) ? $_SESSION[ 'giris' ] : false;

class Fonksiyonlar {
	private $vt;

	const SQL_rol = <<< SQL
SELECT
	 r.id
	,r.adi
	,GROUP_CONCAT( gr.gorulecek_rol_id SEPARATOR ',' ) AS gorulecek_rol_id
	,r.varsayilan
FROM
	tb_roller AS r
LEFT JOIN
	tb_gorulecek_roller AS gr ON r.id = gr.rol_id
GROUP BY
	r.id
ORDER BY
	r.id DESC
SQL;

	const SQL_super = <<< SQL
SELECT
	super
FROM
	tb_sistem_kullanici
WHERE
	id = ?
SQL;

	const SQL_yetki = <<< SQL
SELECT
	 m.id AS menuID
	,m.adi AS sinif_adi
	,m.modul
	,ku.email AS kullanici_adi
	,ku.adi
	,ku.soyadi
	,ku.super
	,ku.rol_id
	,ku.resim
	,r.adi AS rol_adi
	,( SELECT GROUP_CONCAT( birim_id SEPARATOR ',' )	FROM tb_sistem_kullanici_yetkili_birimler WHERE kullanici_id = ku.id )	AS yetki_birim_id
	,( SELECT GROUP_CONCAT( adi SEPARATOR ',' )			FROM tb_yetki_islem_turleri WHERE id = ANY( SELECT yetki_islem_turu_id FROM tb_yetki where kullanici_id = ku.id  AND modul_id = m.id ) )	AS yetki_islemler
	,( SELECT GROUP_CONCAT( adi SEPARATOR ',' )			FROM tb_yetki_islem_turleri WHERE id = ANY( SELECT islem_turu_id FROM tb_rol_yetkiler WHERE rol_id = ku.rol_id AND modul_id = m.id ) )		AS rol_islemler
FROM
	tb_modul AS m
LEFT JOIN
	tb_sistem_kullanici AS ku ON ku.id = ?
JOIN
	tb_roller AS r ON ku.rol_id = r.id
SQL;

/* Yetki Modülü içinde kullanılan sube ve firmalar için yetki kontrolü.*/
	const SQL_yetkili_subeler_yetki_modulu = <<<SQL
SELECT
	*
FROM
	tb_subeler
WHERE
	id = ANY( SELECT sube_id FROM tb_rol_yetkili_subeler WHERE rol_id = ? )
SQL;

	const SQL_yetkili_firmalar_yetki_modulu = <<<SQL
SELECT
	*
FROM
	tb_firmalar
WHERE
	id = ANY( SELECT firma_id FROM tb_rol_yetkili_firmalar WHERE rol_id = ? )
SQL;
/**/

/* Normal Modüller içinde kullanılan sube ve firmalar için yetki kontrolü.*/
	const SQL_yetkili_subeler = <<<SQL
SELECT
	*
FROM
	tb_subeler
WHERE
	id = ANY( SELECT sube_id FROM tb_rol_yetkili_subeler WHERE rol_id = ? ) or ?
SQL;

	const SQL_yetkili_firmalar = <<<SQL
SELECT
	*
FROM
	tb_firmalar
WHERE
	id = ANY( SELECT firma_id FROM tb_rol_yetkili_firmalar WHERE rol_id = ? ) or ?
SQL;
/* */

	const SQL_firmalar = <<<SQL
SELECT
	*
FROM
	tb_firmalar
SQL;


	const SQL_module_atanan_yetki_islem_turleri = <<<SQL
SELECT
	yetki_islem_id AS id
FROM
	tb_modul_yetki_islemler
WHERE
	modul_id = ?
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
	const SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
     baslangic_saat
    ,bitis_saat
    ,maas_kesintisi
	,adi AS islemTipi
FROM
	tb_giris_cikis
LEFT JOIN tb_giris_cikis_tipi ON tb_giris_cikis_tipi.id =  tb_giris_cikis.islem_tipi
LEFT JOIN tb_giris_cikis_tipleri ON tb_giris_cikis_tipleri.id =  tb_giris_cikis_tipi.tip_id
WHERE
	baslangic_saat  IS NOT NULL AND 
	personel_id 	= ? AND 
	tarih 			=? AND 
	aktif 			= 1
ORDER BY baslangic_saat ASC 
SQL;

 	const SQL_giris_cikis_saat = <<< SQL
SELECT 
	t1.*
from
	tb_tarifeler AS t1
LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id

WHERE 
	t1.baslangic_tarih <= ? AND 
	t1.bitis_tarih >= ? AND
	mt.gunler LIKE ? AND 
	t1.grup_id LIKE ? AND
	t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1
SQL;

/*Kapatilmış Olan tarifeyi Getirme*/
const SQL_kapatilmis_tarife_getir = <<< SQL
SELECT 
	*
from
	tb_kapatilan_tarifeler 
WHERE 
	id = ?
LIMIT 1
SQL;

//TARİFEYE AİT SAAT LİSTESİ
	const SQL_tarife_saati = <<< SQL
SELECT 
	*
from
	tb_tarife_saati 
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//TARİFEYE AİT SAAT LİSTESİ
	const SQL_mola_saati = <<< SQL
SELECT 
	*
from
	tb_molalar
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//KAPATILAN TARİFEYE AİT SAAT LİSTESİ
const SQL_kapatilan_tarife_saati = <<< SQL
SELECT 
	*
from
	tb_kapatilan_tarife_saati 
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//KAPATILAN TARİFEYE AİT SAAT LİSTESİ
	const SQL_kapatilan_mola_saati = <<< SQL
SELECT 
	*
from
	tb_kapatilan_molalar
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;


//Giriş Çıkış id sine göre listeleme 
	const SQL_puantaj_oku = <<< SQL
SELECT
	*
FROM
	tb_puantaj
WHERE
	personel_id 	= ? AND
	tarih 	   		= ?

SQL;

/*Puantaj Güncelleme İşlemi*/
	const SQL_puantaj_guncelle = <<< SQL
UPDATE tb_puantaj
SET 
	personel_id			= ?,
	tarih				= ?,
	izin				= ?,
	calisma				= ?,
	ucretli_izin		= ?,
	ucretsiz_izin		= ?,
	toplam_kesinti		= ?,
	tatil				= ?,
	maasa_etki_edilsin	= ?,
	yarim_gun_tatil		= ?
WHERE
	id 					= ?  
SQL;

/*tb_puantaj tablosuna veri ekleme*/
	const SQL_puantaj_kaydet = <<< SQL
INSERT INTO
	tb_puantaj
SET
	personel_id			= ?,
	tarih				= ?,
	izin				= ?,
	calisma				= ?,
	ucretli_izin		= ?,
	ucretsiz_izin		= ?,
	toplam_kesinti		= ?,
	tatil				= ?,
	maasa_etki_edilsin	= ?,
	yarim_gun_tatil		= ?
SQL;

/*İşlem Yapılan donemin kapatılıp kapatılmadığını kntrol etme*/
	const SQL_donem_kontrol = <<< SQL
SELECT 
	*
FROM 
	tb_donem
WHERE 
	firma_id 	= ? AND 
	yil 		= ? AND
	ay 		= ? AND 
	aktif 	= 1 
SQL;

/*Personelin Kapatilmış olan tarihe göre tarifesini öğrenme*/
const SQL_tarife_ogren = <<< SQL
SELECT 
	id,
	tarife
FROM 
	tb_giris_cikis
WHERE
	personel_id = ? AND
	tarih 		= ? AND
	aktif 		= 1 
SQL;

/*Personelin Kapatilmış olan tarihe göre tarifesini öğrenme*/
const SQL_eksik_gun_say = <<< SQL
SELECT 
	COUNT(toplam_kesinti) AS toplam
FROM 
	tb_puantaj
WHERE
	personel_id 	 = ? AND
	tarih 			>= ? AND
	tarih 			< ? AND
	toplam_kesinti 	>= 450;
SQL;

/*Personelin Kapatilmış olan tarihe göre tarifesini öğrenme*/
const SQL_genel_ayarlar = <<< SQL
SELECT 
	aylik_calisma_saati,
	haftalik_calisma_saati,
	giris_cikis_denetimi_grubu,
	pazar_kesinti_sayisi,
	puantaj_hesaplama_grubu,
	beyaz_yakali_personel,
	giris_cikis_liste_goster,
	giris_cikis_tutanak_kaydet,
	tutanak_olustur,
	normal_carpan_id,
	tatil_mesai_carpan_id,
	gunluk_calisma_suresi,
	yarim_gun_tatil_suresi
FROM 
	tb_genel_ayarlar
WHERE
	firma_id 	 = ?
SQL;

/*Personelin Kapatilmış olan tarihe göre tarifesini öğrenme*/
const SQL_donem_ayarlar = <<< SQL
SELECT 
	aylik_calisma_saati,
	haftalik_calisma_saati,
	giris_cikis_denetimi_grubu,
	pazar_kesinti_sayisi,
	puantaj_hesaplama_grubu,
	beyaz_yakali_personel,
	giris_cikis_liste_goster,
	giris_cikis_tutanak_kaydet,
	tutanak_olustur,
	normal_carpan_id,
	tatil_mesai_carpan_id,
	gunluk_calisma_suresi,
	yarim_gun_tatil_suresi
FROM 
	tb_donem
WHERE
	firma_id 	= ? AND 
	yil 		= ? AND
	ay 			= ? AND 
	aktif 		= 1
SQL;

/*Geç Gelenler Listesi*/
const SQL_gecgelenler = <<< SQL
SELECT 
	gc.personel_id AS id,
	gc.baslangic_saat,
	(
		SELECT CONCAT(p.adi," ",p.soyadi) FROM tb_personel AS p WHERE p.id = ilk_giris.personel_id
	) AS adsoyad
FROM 
	tb_giris_cikis AS gc
LEFT JOIN (
            SELECT
           	 	personel_id, MIN(baslangic_saat) AS ilk_giris_saat, tarih
            FROM 
            	tb_giris_cikis
            WHERE 
                tarih = ? AND
                aktif = 1
						GROUP BY personel_id
            ORDER BY id DESC
        ) AS ilk_giris ON gc.personel_id = ilk_giris.personel_id
WHERE 
	ilk_giris_saat > (
            SELECT
            (
                SELECT 
                	ADDTIME( baslangic, CONCAT("00:",gec_gelme_tolerans,":00")) AS baslangic 
                FROM 
                	tb_tarife_saati AS ts 
                WHERE 
                    tarife_id = t1.id AND 
                    ts.carpan = 1  
                ORDER BY ts.id ASC 
            ) AS baslangic
            from
            	tb_tarifeler AS t1
            LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id
            LEFT JOIN tb_gruplar AS g ON t1.grup_id LIKE CONCAT('%,', g.id, ',%')
            WHERE 
                t1.baslangic_tarih <= ilk_giris.tarih AND 
                t1.bitis_tarih >= ilk_giris.tarih AND
                mt.gunler LIKE ? AND 
                t1.aktif = 1
            ORDER BY t1.id DESC
            LIMIT 1
        ) AND
	gc.tarih =ilk_giris.tarih AND
	gc.aktif = 1
SQL;

/*Geç Gelenler Listesi*/
const SQL_gecgelenler_listesi = <<< SQL
SELECT 
	gc.personel_id AS id,
	gc.baslangic_saat,
	(
		SELECT CONCAT(p.adi," ",p.soyadi) FROM tb_personel AS p WHERE p.id = ilk_giris.personel_id
	) AS adsoyad
FROM 
	tb_giris_cikis AS gc
LEFT JOIN (
            SELECT
           	 	personel_id, MIN(baslangic_saat) AS ilk_giris_saat, tarih
            FROM 
            	tb_giris_cikis
            WHERE 
                tarih = ? AND
                aktif = 1
						GROUP BY personel_id
            ORDER BY id DESC
        ) AS ilk_giris ON gc.personel_id = ilk_giris.personel_id
WHERE 
	ilk_giris_saat > (
            SELECT
            (
                SELECT 
                	ADDTIME( baslangic, CONCAT("00:",gec_gelme_tolerans,":00")) AS baslangic 
                FROM 
                	tb_tarife_saati AS ts 
                WHERE 
                    tarife_id = t1.id AND 
                    ts.carpan = 1  
                ORDER BY ts.id ASC 
            ) AS baslangic
            from
            	tb_tarifeler AS t1
            LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id
            LEFT JOIN tb_gruplar AS g ON t1.grup_id LIKE CONCAT('%,', g.id, ',%')
            WHERE 
                t1.baslangic_tarih <= ilk_giris.tarih AND 
                t1.bitis_tarih >= ilk_giris.tarih AND
                mt.gunler LIKE ? AND 
                t1.aktif = 1
            ORDER BY t1.id DESC
            LIMIT 1
        ) AND
	gc.personel_id NOT IN(
		SELECT personel_id FROM tb_tutanak WHERE tb_tutanak.tarih = ? AND tip = "gecgelme"
	) AND
	gc.tarih =ilk_giris.tarih AND
	gc.personel_id IN( SELECT p.id FROM tb_personel AS p LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id WHERE p.grup_id != ga.beyaz_yakali_personel ) AND
	gc.aktif = 1
SQL;

/*Erken Çıkanlar Listesi*/
const SQL_erkenCikanlar = <<< SQL
SELECT 
	gc.personel_id AS id,
	gc.bitis_saat,
	(
		SELECT CONCAT(p.adi," ",p.soyadi) FROM tb_personel AS p WHERE p.id = son_cikis.personel_id
	) AS adsoyad
FROM 
	tb_giris_cikis AS gc
LEFT JOIN (
            SELECT
           	 	personel_id, MAX(bitis_saat) AS son_bitis_saat, tarih
            FROM 
            	tb_giris_cikis
            WHERE 
				bitis_saat IS NOT NULL AND
                tarih 		= ? AND
                aktif 		= 1
    		GROUP BY personel_id
            ORDER BY id DESC
        ) AS son_cikis ON gc.personel_id = son_cikis.personel_id
WHERE 
	son_bitis_saat < (
		SELECT
			(
				SELECT 
					ADDTIME( bitis, CONCAT("-00:",erken_cikma_tolerans,":00")) AS baslangic 
				FROM 
					tb_tarife_saati AS ts 
				WHERE 
					tarife_id = t1.id AND 
					ts.carpan = 1  
				ORDER BY ts.id ASC 
			) AS baslangic
			from
				tb_tarifeler AS t1
			LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id
			LEFT JOIN tb_gruplar AS g ON t1.grup_id LIKE CONCAT('%,', g.id, ',%')
			WHERE 
				t1.baslangic_tarih <= son_cikis.tarih AND 
				t1.bitis_tarih >= son_cikis.tarih AND
				mt.gunler LIKE ? AND 
				t1.aktif = 1
			ORDER BY t1.id DESC
			LIMIT 1
	) AND
	gc.tarih = son_cikis.tarih AND
	gc.aktif = 1
LIMIT 1
SQL;

/*Erken Çıkanlar Listesi*/
const SQL_erkenCikanlar_listesi = <<< SQL
SELECT 
	gc.personel_id AS id,
	gc.bitis_saat,
	(
		SELECT CONCAT(p.adi," ",p.soyadi) FROM tb_personel AS p WHERE p.id = son_cikis.personel_id
	) AS adsoyad
FROM 
	tb_giris_cikis AS gc
LEFT JOIN (
            SELECT
           	 	personel_id, MAX(bitis_saat) AS son_bitis_saat, tarih
            FROM 
            	tb_giris_cikis
            WHERE 
				bitis_saat IS NOT NULL AND
                tarih 		= ? AND
                aktif 		= 1
    		GROUP BY personel_id
            ORDER BY id DESC
        ) AS son_cikis ON gc.personel_id = son_cikis.personel_id
WHERE 
	son_bitis_saat < (
		SELECT
			(
				SELECT 
					ADDTIME( bitis, CONCAT("-00:",erken_cikma_tolerans,":00")) AS baslangic 
				FROM 
					tb_tarife_saati AS ts 
				WHERE 
					tarife_id = t1.id AND 
					ts.carpan = 1  
				ORDER BY ts.id ASC 
			) AS baslangic
			from
				tb_tarifeler AS t1
			LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id
			LEFT JOIN tb_gruplar AS g ON t1.grup_id LIKE CONCAT('%,', g.id, ',%')
			WHERE 
				t1.baslangic_tarih <= son_cikis.tarih AND 
				t1.bitis_tarih >= son_cikis.tarih AND
				mt.gunler LIKE ? AND 
				t1.aktif = 1
			ORDER BY t1.id DESC
			LIMIT 1
	) AND
	gc.personel_id NOT IN(
		SELECT personel_id FROM tb_tutanak WHERE tb_tutanak.tarih = ? AND tip = "erkencikma"
	) AND
	gc.tarih = son_cikis.tarih AND
	gc.personel_id IN( SELECT p.id FROM tb_personel AS p LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id WHERE p.grup_id != ga.beyaz_yakali_personel ) AND
	gc.aktif = 1
LIMIT 1
SQL;


/*Gelmeyenler*/
const SQL_gelmeyenler = <<< SQL
SELECT 
	p.id,
	CONCAT(p.adi," ",p.soyadi) AS adsoyad
FROM 
	tb_personel AS p
LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id
WHERE 
	p.id NOT IN (
		SELECT gc.personel_id
		FROM tb_giris_cikis AS gc
		WHERE 
			gc.baslangic_saat IS NOT NULL AND
			gc.tarih 		= ? AND
			aktif 			= 1
	)AND 
	p.grup_id != ga.beyaz_yakali_personel AND
	p.aktif = 1 AND
	p.grup_id IN (
					SELECT 
						TRIM(BOTH ',' FROM REPLACE(REGEXP_REPLACE(giris_cikis_denetimi_grubu, ',[[:space:]]+', ','), ',,', ',')) AS denetim_gruplari
					FROM tb_genel_ayarlar AS ga WHERE ga.firma_id = p.firma_id
				 )

SQL;

/*Gelmeyenler*/
const SQL_gelmeyenler_listesi = <<< SQL
SELECT 
	p.id,
	CONCAT(p.adi," ",p.soyadi) AS adsoyad
FROM 
	tb_personel AS p
LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id
WHERE 
	p.id NOT IN (
		SELECT gc.personel_id
		FROM tb_giris_cikis AS gc
		WHERE 
			gc.baslangic_saat IS NOT NULL AND
			gc.tarih 		= ? AND
			aktif 			= 1
	)AND
	p.id NOT IN(
		SELECT 
			personel_id
		FROM tb_tutanak 
		WHERE 
			tb_tutanak.tarih 	= ? AND 
			tip 				= ? 
	) AND
	p.grup_id != ga.beyaz_yakali_personel AND
	p.aktif = 1 AND
	p.grup_id IN (
					SELECT 
						TRIM(BOTH ',' FROM REPLACE(REGEXP_REPLACE(giris_cikis_denetimi_grubu, ',[[:space:]]+', ','), ',,', ',')) AS denetim_gruplari
					FROM tb_genel_ayarlar AS ga WHERE ga.firma_id = p.firma_id
				)

SQL;


/*Gelenler*/
const SQL_gelenler = <<< SQL
SELECT 
	p.id,
	CONCAT(p.adi," ",p.soyadi) AS adsoyad
FROM 
	tb_personel AS p
LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id
WHERE 
	p.id IN (
		SELECT gc.personel_id
		FROM tb_giris_cikis AS gc
		WHERE 
			gc.baslangic_saat IS NOT NULL AND
			gc.tarih 		= ? AND
			gc.islem_tipi 	= 0 AND 
			aktif 			= 1
		GROUP BY personel_id
		ORDER BY gc.id DESC
	)AND 
	p.grup_id != ga.beyaz_yakali_personel AND
	p.aktif = 1 AND
	p.grup_id IN (
					SELECT 
						TRIM(BOTH ',' FROM REPLACE(REGEXP_REPLACE(giris_cikis_denetimi_grubu, ',[[:space:]]+', ','), ',,', ',')) AS denetim_gruplari
					FROM tb_genel_ayarlar AS ga WHERE ga.firma_id = p.firma_id
				)
SQL;

/*Gelenler*/
const SQL_mesaiCikmayan = <<< SQL
SELECT 
	p.id,
	CONCAT(p.adi," ",p.soyadi) AS adsoyad
FROM 
	tb_personel AS p
LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id
WHERE 
	p.id IN (
		SELECT
			gc.personel_id
		FROM 
			tb_giris_cikis AS gc
		WHERE 
			gc.bitis_saat IS NULL AND
			gc.tarih 		= ? AND
			gc.islem_tipi 	= 0 AND
			gc.aktif 		= 1
		GROUP BY personel_id
		ORDER BY gc.id DESC
	)AND 
	p.grup_id != ga.beyaz_yakali_personel AND
	p.aktif = 1 AND
	p.grup_id IN (
					SELECT 
						TRIM(BOTH ',' FROM REPLACE(REGEXP_REPLACE(giris_cikis_denetimi_grubu, ',[[:space:]]+', ','), ',,', ',')) AS denetim_gruplari
					FROM tb_genel_ayarlar AS ga WHERE ga.firma_id = p.firma_id
				)
SQL;

/**/
const SQL_izinliPersonel = <<< SQL
SELECT
	p.id,
	CONCAT(p.adi," ",p.soyadi) AS adsoyad
FROM 
	tb_personel AS p
INNER JOIN tb_giris_cikis AS gc ON p.id = gc.personel_id
WHERE 
	gc.tarih 	= ? 
GROUP BY p.id
HAVING COUNT(*) = 1 AND 
MAX(gc.islem_tipi) > 0
SQL;


//Ay içierisinde Giriş Veya İşten Çıkan PErsonel Listesi
const SQL_ise_giris = <<< SQL
SELECT 
	id,
	CONCAT(adi," ",soyadi) AS adsoyad
FROM 
	tb_personel
WHERE 
	DATE_FORMAT(ise_giris_tarihi,'%Y-%m') = ? 
SQL;

const SQL_is_cikis = <<< SQL
SELECT 
	id,
	CONCAT(adi," ",soyadi)  AS adsoyad
FROM 
	tb_personel
WHERE 
	DATE_FORMAT(isten_cikis_tarihi,'%Y-%m') 	= ? 
SQL;

const SQL_beyaz_yakali = <<< SQL
SELECT
    p.id,
	CONCAT(adi," ",soyadi) AS adsoyad
FROM
    tb_personel AS p
LEFT JOIN tb_genel_ayarlar AS ga ON ga.firma_id = p.firma_id
WHERE
    p.firma_id  = ? AND 
    p.grup_id   = ga.beyaz_yakali_personel AND
    p.aktif     = 1 
SQL;

const SQL_kategoriGetir = <<< SQL
WITH RECURSIVE kategori_ustleri AS (
	SELECT id, adi, kategori AS kategori, CAST(id AS CHAR) AS tam_kategori_id, adi AS tam_kategori_adi
	FROM tb_firma_dosya_turleri
	WHERE id = ?
	UNION ALL
	SELECT t.id, t.adi, t.kategori, CONCAT(k.tam_kategori_id, '-', t.id), CONCAT(k.tam_kategori_adi, ' > ', t.adi) AS tam_kategori_adi
	FROM tb_firma_dosya_turleri t
	JOIN kategori_ustleri k ON t.id = k.kategori
	)
	SELECT tam_kategori_id, tam_kategori_adi FROM kategori_ustleri ORDER BY tam_kategori_id DESC LIMIT 1;
SQL;

const SQL_suresi_dolan_kategoriler = <<< SQL
SELECT 
	*
FROM tb_firma_dosya_turleri 
WHERE 
	firma_id 	= ? AND 
	tarih 		<= DATE_ADD(CURDATE(), INTERVAL 10 DAY) AND
	tarih 		!= "0000-00-00"
	
SQL;

const SQL_suresi_dolan_dosyalar = <<< SQL
SELECT 
	d.id,
	d.dosya_turu_id AS kategori,
	d.evrakTarihi AS tarih,
	(
		SELECT
			adi
		FROM tb_firma_dosya_turleri 
		WHERE 
			id = d.dosya_turu_id
	) AS adi
FROM tb_firma_dosyalari as d
LEFT JOIN tb_firma_dosya_turleri AS dt ON dt.id = d.dosya_turu_id
WHERE 
	dt.firma_id 		= ? AND 
	d.evrakTarihi 		<= DATE_ADD(CURDATE(), INTERVAL 10 DAY) AND
	d.evrakTarihi 		!= "0000-00-00"
SQL;

const SQL_kontrol = <<< SQL
SELECT
	 k.*
	,CASE k.super WHEN 1 THEN "Süper" ELSE r.adi END AS rol_adi
FROM
	tb_sistem_kullanici AS k
JOIN
	tb_roller AS r ON k.rol_id = r.id
WHERE
	k.id = ?
LIMIT 1
SQL;

	/* Kurucu metod  */
	public function __construct() {
		$this->vt = new VeriTabani();
	}
	/*Belirli bir gün için mesaiden erken ayrılan persone listesi*/
	public function erkenCikanlar( $tarih, $gun){
		return $this->vt->select( self::SQL_erkenCikanlar, array( $tarih, $gun ) )[2];
	}
	
	public function erkenCikanlarListesi( $tarih, $gun){
		return $this->vt->select( self::SQL_erkenCikanlar_listesi, array( $tarih, $gun,$tarih ) )[2];
	}
	/*Belirli bir gün için Mesaiye geç gelmiş personel listesi */
	public function gecGelenler( $tarih, $gun){
		return $this->vt->select( self::SQL_gecgelenler, array( $tarih, $gun ) )[2];
	}

	public function gecGelenlerListesi( $tarih, $gun){
		return $this->vt->select( self::SQL_gecgelenler_listesi, array( $tarih, $gun,$tarih) )[2];
	}
	/*Belirli bir gün için gelmeyenler listesi izinli olan personel listelenmemektedir*/
	public function gelmeyenler( $tarih ){
		return $this->vt->select( self::SQL_gelmeyenler, array( $tarih ) )[2];
	}
	
	public function gelmeyenlerListesi( $tarih ){
		return $this->vt->select( self::SQL_gelmeyenler_listesi, array( $tarih, $tarih,"gunluk" ) )[2];
	}
	/*Belirli gün içinde gelen personel listesi*/
	public function gelenler( $tarih ){
		return $this->vt->select( self::SQL_gelenler, array( $tarih ) )[2];
	}
	/*Belirli gün için mesai çıkışı yapmayan personel lisetsi*/
	public function mesaiCikmayan( $tarih ){
		return $this->vt->select( self::SQL_mesaiCikmayan, array( $tarih ) )[2];
	}
	/*Belirli bir gün için izinli olan personel lisetesi*/
	public function izinliPersonel( $tarih ){
		return $this->vt->select( self::SQL_izinliPersonel, array( $tarih ) )[2];
	}
	/*Ay içinde Giriş Yapmış Personel Listesi*/
	public function iseGiris( $tarih ){
		return $this->vt->select( self::SQL_ise_giris, array( $tarih ) )[2];
	}
	/*Ay içinde Çıkış yapan personel listesi*/
	public function istenCikis( $tarih ){
		return $this->vt->select( self::SQL_is_cikis, array( $tarih ) )[2];
	}
	
	public function beyazYakali(  ){
		return $this->vt->select( self::SQL_beyaz_yakali, array( $_SESSION[ "firma_id" ] ) )[2];
	}
	
	/*Firma Dosyaları içinde seçilen bir kategorinin ust kategori idlerinin arasına > bırakarak listeler*/
	public function kategoriHiyerarsiAdi( $id ){
		$hiyerarsi 	= $this->vt->select( self::SQL_kategoriGetir, array( $id ) )[2][0]["tam_kategori_adi"];
		$bol 		= explode(">",$hiyerarsi);
		$bol 		= array_reverse( $bol );

		return implode(' <i class="fas fa-arrow-right"></i> ', $bol );
	}
	
	/*Firma Dosyaları içinde seçilen bir kategorinin ust kategori idlerinin arasına - bırakarak listeler*/
	public function kategoriHiyerarsiId( $id ){
		$hiyerarsi 	= $this->vt->select( self::SQL_kategoriGetir, array( $id ) )[2][0]["tam_kategori_id"];

		$hiyerarsi 	= str_replace( "$id-", "", $hiyerarsi);

		return $hiyerarsi;
	}

	public function suresiDolmusKategori(){
		return $this->vt->select( self::SQL_suresi_dolan_kategoriler, array( $_SESSION[ "firma_id" ] ) )[2];
	}
	
	public function suresiDolmusDosya(){
		return $this->vt->select( self::SQL_suresi_dolan_dosyalar, array( $_SESSION[ "firma_id" ] ) )[2];
	}



	/* Kullanıcı süper değilse rolünün görebileceği roller dizisi döner. */
	public function rolVer() {
		$sonuc				= $this->vt->select( self::SQL_rol );
		$kayitlar			= array();
		$gorulecekRoller	= array();
		
		/* hata varsa boş dizi dön */
		if( $sonuc[ 0 ] ) return array();
		$sonuc = $sonuc[ 2 ];

		for( $i = 0; $i < count( $sonuc ); $i++ )
			if( $sonuc[ $i ][ 'id' ] == $_SESSION[ 'rol_id' ] ) {
				$gorulecekRoller = array_map( 'intval', explode( ',', $sonuc[ $i ][ 'gorulecek_rol_id' ] ) );
			}

		for( $i = 0; $i < count( $sonuc ); $i++ ) {
			if( !in_array( $sonuc[ $i ][ 'id' ], $gorulecekRoller ) && !$this->superKullanici() ) continue;

			$kayit[ 'id' ]					= $sonuc[ $i ][ 'id' ] * 1;
			$kayit[ 'adi' ]					= $sonuc[ $i ][ 'adi' ];
			$kayit[ 'gorulecek_rol_id' ]	= $sonuc[ $i ][ 'gorulecek_rol_id' ] ? array_map( 'intval', explode( ',', $sonuc[ $i ][ 'gorulecek_rol_id' ] ) ) : array();
			$kayit[ 'varsayilan' ]			= $sonuc[ $i ][ 'varsayilan' ] * 1;
			$kayitlar[]						= $kayit;
		}
		return $kayitlar;
	}

	/*
	*
	*	GÜVENLİK FONKSİYONLARI
	*
	*/

	/* Süper kullanıcı olup olmadığını kontrol ediyor. */
	public function superKullanici( $kul_id = 0 ) {
		if( !$kul_id ) $kul_id = $_SESSION[ 'kullanici_id' ];
		$sonuc = $this->vt->selectSingle( self::SQL_super, array( $kul_id ) );
		return  $sonuc[ 2 ][ 'super' ] * 1;
	}

	/*
	*	Kullanıcının sahip olduğu roldeki "tb_rol_yetkiler" ve "tb_yetki" tablosundaki yetkileri birleştiriliyor.
	*	Kümelerdeki birleşim kuralı geçerlidir. Yani aynı eleman tekrarlanmaz. Örneğin hem "tb_rol_yetkiler" tablosunda hem de "tb_yetki" tablosunda "ekle" işlemi olursa birtane "ekle" işlemi alınıyor.
	*	Eğer yetkiIslem = A ve rolIslem B adında bir kümeyi ifade ederse, rolVeModulYetkileriBirlestir = ( A / B )U( B ) olur.
	*/
	public function rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDeger = true ) {
		$yeniDizi	= array();
		/* $yetkiIslem'de olup $rolIslem'de olmayan yetki işlemlerini $rolIslem'e ekle */
		$dizi		= count( $yetkiIslem ) ? array_merge( array_diff( $yetkiIslem, $rolIslem ), $rolIslem ) : $rolIslem;
		/** Eğer isteniyorsa Dizinin anahtar ve değerleri aynı olsun. */
		for( $i = 0; $i < count( $dizi ); $i++ ) $yeniDizi[ $dizi[ $i ] ] = $dizi[ $i ];
		/* Eğer dizinin anahtar ve değrlerinin aynı olması İSTENMİYORSA rolVeModulYetkileriBirlestir fonksiyonu  $anahtarDeger = false parametresi ile çağırılmalıdır. */
		return $anahtarDeger ? $yeniDizi : $dizi;
	}

	/*
	*	"kullanici_id" si bilinen bir kullanıcının her bir modüle ait tüm yetkilerini dizi şeklinde verir. 
	*
	*	Sisteme yeni bir modul eklendiğinde bu modul için rol yetkileri de, yani "tb_rol_yetkiler" tablosuna eklenmelidir. 
	*	Tüm rollerin tüm moduller için yetkileri "tb_rol_yetkiler" tablosunda bulunmak zorundadır. Bütün rollerin varsayılan yetkileri listele'dir.
	*	Ayrıca sistemde kayıtlı bir varsayılan rol olmak zorundadır. Çünkü eklenecek yeni kullanıcıların varsayılan olarak bir rolun atanması lazım. 
	*	Yani sistemde rolu olmayan bir kullanıcı olamaz.
	*/

	public function tumYetkileriVer( $id ) {
		if( !$id ) return array();
		$sonuc				= $this->vt->select( self::SQL_yetki,  array( $id ) );
		$yetkiler			= array();
		$kullaniciAdi 		= '';
		$ad 				= '';
		$soyad 				= '';
		$rolAdi 			= '';
		$super				= 0;
		$rol_id				= 0;
		$yetkiliBirimler	= array();
		
		foreach( $sonuc[ 2 ] AS $satir ) {
			$yetkiIslem		= $satir[ "yetki_islemler" ];
			$rolIslem		= $satir[ "rol_islemler" ];

			/* "tb_yetki" tablosunda kullanıcının herhangi bir modül için sahip olduğu yetki işlemleri ( ekle,sil,guncelle...) */
			$yetkiIslem		= $yetkiIslem ? explode( ",", $yetkiIslem ) : array();

			/* "tb_rol_yetkiler" tablosunda bir kullanıcının rolünün sahip olduğu yetki işlemleri ( ekle,sil,guncelle...) */
			$rolIslem		= $rolIslem ? explode( ",", $rolIslem ) : array();

			$kullaniciAdi		= $satir[ "kullanici_adi"	];
			$ad 				= $satir[ "adi" 			];
			$soyad 				= $satir[ "soyadi"			];
			$rol_id 			= $satir[ "rol_id"			];
			$rolAdi 			= $satir[ "rol_adi"			];
			$super 				= $satir[ "super"			] * 1;
			$resim 				= $satir[ "resim"			];
			$yetkiliBirimler	= $satir[ 'yetki_birim_id' ];
			$yetkiliBirimler	= explode( ',', $yetkiliBirimler );
			$yetkiler[ $satir[ "modul" ] ] = $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem );
		}

		$hesapBilgileri = array(
			 'ad'				=> $ad
			,'soyad'			=> $soyad
			,'kullaniciAdi'		=> $kullaniciAdi
			,'rolAdi'			=> $rolAdi
			,'rol_id'			=> $rol_id * 1
			,'supermi'			=> $super
			,'giris'			=> $_SESSION[ 'giris' ] ? true : false
			,'yetkiliBirimler'	=> array_map( 'intval', $yetkiliBirimler )
		);

		$_SESSION[ 'ad_soyad' ]			= $ad . ' ' . $this->tumuBuyukHarf( $soyad );
		$_SESSION[ 'rol_adi' ]			= $super ? 'Süper' : $rolAdi;
		$_SESSION[ 'rol_id' ]			= $rol_id;
		$_SESSION[ 'kullanici_resim' ]	= $resim;
		$yetkiler[ 'hesapBilgileri' ]	= $hesapBilgileri;
		return $yetkiler;
	}
	
	/* İstenilen kullanıcının tüm modülleri varsa yetki işlemleri ile birlikte verir. */
	public function modulYetkileriVer( $id, $anahtarDegerAyni = false ) {
		if( !$id ) return array();
		$sonuc					= $this->vt->select( self::SQL_yetki,  array( $id ) );

		$modulDizisiIndexli		= array();
		$modulDizisiAnahtarli	= array();

		foreach( $sonuc[ 2 ] AS $satir ) {
			$yetkiIslem		= $satir[ "yetki_islemler" ];
			$rolIslem		= $satir[ "rol_islemler" ];

			/* "tb_yetki" tablosunda kullanıcının herhangi bir modül için sahip olduğu yetki işlemlerini verir ( ekle,sil,guncelle...) */
			$yetkiIslem		= $yetkiIslem ? explode( ",", $yetkiIslem ) : array();

			/* "tb_rol_yetkiler" tablosunda bir kullanıcının rolünün sahip olduğu yetki işlemlerini verir ( ekle,sil,guncelle...) */
			$rolIslem = $rolIslem ? explode( ",", $rolIslem ) : array();
			$modulDizisiIndexli[ $satir[ 'menuID' ] ]	= $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDegerAyni );
			$modulDizisiAnahtarli[ $satir[ "modul" ] ]	= $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDegerAyni );
		}
		return $anahtarDegerAyni ? $modulDizisiAnahtarli : $modulDizisiIndexli;
	}

	public function yetkiKontrol( $kullanici_id = 0, $modul = '', $islem = '' ) {
		/* id, modül veya sorgulanacak yetki işlemi boş gelirse veya giriş yapılmamışsa dön. */
		if( !$kullanici_id || !$modul || !$islem ) return 0;
		/* Eğer kullanıcı süper yetkili ise, yetki kontrolünü yapma ve dön. Bu durumda kullanıcı tam yetkili olur. */
		if( $this->superKullanici( $kullanici_id ) ) return 1;
		/** Sorgulanan kullanıcının tüm yetkileri al. */
		$yetkiler = $this->modulYetkileriVer( $kullanici_id, true );
		if( !count( $yetkiler ) ) return 0;
		/** istenilen modulde istenilen yetki varmı bak */
		//$modul = 'mdl_' . $modul;
		foreach( $yetkiler as $modulAnahtar => $islemler ) foreach( $islemler as $isl ) if( $modulAnahtar == $modul && $isl == $islem ) return 1;
		return 0;
	}
	
	/* Herhangi bir rol_id nin yetkili olduğu subelerı verir.*/
	public function superKontrolluRolYetkilisubeVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_subeler,  array( $rol_id, $_SESSION[ 'super' ] ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function superKontrolluRolYetkiliFirmaVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_firmalar,  array( $rol_id, $_SESSION[ 'super' ] ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}
	
	
//////////////////////
	/* Herhangi bir rol_id nin yetkili olduğu subelerı verir.*/
	public function yetkilisubeVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_subeler_yetki_modulu,  array( $rol_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}


	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function yetkiliFirmaVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_firmalar_yetki_modulu,  array( $rol_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function yetkiliIslemTurleriVer( $modul_id ) {
		$sonuclar = $this->vt->select( self::SQL_module_atanan_yetki_islem_turleri,  array( $modul_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] =  $sonuc[ 'id' ];
		return $dizi;
	}

	/*
	*	Firma'nın türüne göre firma listesini verir.
	*	@firma_turu : uretici_firma, tasiyici_firma, alici_firma, satici_firma, analiz_firmasi olabilir
	*	@sadece_idler : true, false olabilir.
	*/
	public function firmaVer( $firma_turu = 1, $sadece_idler = false ) {
		$SQL = self::SQL_firmalar;
		$SQL = $SQL . ' WHERE ' . $firma_turu . ' = ?';
		$sonuclar = $this->vt->select( $SQL,  array( 1 ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Cihaz algılama fonksiyonu */
	public function mobilCihaz() {
		$useragent = $_SERVER[ 'HTTP_USER_AGENT' ];
		if( preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4) ) )
			return true;
		return false;
	}
	
	/**
	* @$file_input_adi file input nesnesine verdiğiniz isim (name)
	* @dosya_adi upload edilecek olan dosya vermek istediğiniz isim
	*/
	public function resimYukle( $file_input_adi, $dosya_adi ) {
		if( isset( $_FILES[ $file_input_adi ] ) ) {
			$errors		= array();
			$file_name	= $_FILES[ $file_input_adi ]['name'];
			$file_size	= $_FILES[ $file_input_adi ]['size'];
			$file_tmp	= $_FILES[ $file_input_adi ]['tmp_name'];
			$file_type	= $_FILES[ $file_input_adi ]['type'];
			$file_ext	= strtolower( end( explode( '.', $_FILES[ $file_input_adi ][ 'name' ] ) ) );
			$expensions	= array( "jpeg", "jpg", "png", 'JPEG', 'JPG', 'PNG' );
			if( in_array( $file_ext, $expensions ) === false ) {
				$errors[] = "Lütfen sadece jpg veya png uzantılı fotoğtaf yükleyiniz. ";
			}
			if( $file_size > 2012345 ) {
				$errors[] = 'Dosya boyutu en fazla 2MB olabilir';
			}
			if( empty( $errors ) == true ) {
				if( move_uploaded_file( $file_tmp, "../../resimler/" . $dosya_adi . "." . $file_ext ) ) {
					return array( true, $dosya_adi . "." . $file_ext );
				} else {
					return array( false, false );
				}
			} else {
				return array( false, false );
			}
		} else {
			return array( false, false );
		}
	}

	/*
	*
	*	METİN FONKSİYONLARI
	*
	*/	

	/*( 1.234,67 ) şeklinde sayı formatı verir.*/
	public function sayiFormatiVer( $sayi ) {
		if( gettype( $sayi ) == 'double' )
			return number_format( $sayi, 2, ',', '.');
		else
			return number_format( $sayi, 0, ',', '.');
	}
	
	/* tüm harfleri _BÜYÜK_ harfe çevir */
	public function tumuBuyukHarf( $metin ) {
		$b = array( 'Ç', 'Ğ', 'İ', 'I', 'Ö', 'Ş', 'Ü' );
		$k = array( 'ç', 'ğ', 'i', 'ı', 'ö', 'ş', 'ü' );
		return mb_strtoupper( str_replace( $k, $b, $metin ), 'utf-8' );
	}
	
	/* tüm harfleri _küçük_ harfe çevir */
	public function tumuKucukHarf( $metin ) {
		$b = array( 'Ç', 'Ğ', 'İ', 'I', 'Ö', 'Ş', 'Ü' );
		$k = array( 'ç', 'ğ', 'i', 'ı', 'ö', 'ş', 'ü' );
		return mb_strtolower( str_replace( $b, $k, $metin ), 'utf-8' );
	}
	
	/* Her kelimenin ilk harfini büyük diğerlerini küçük yazar. */
	public function ilkHarfleriBuyut( $metin ) {
		if( !$metin ) return '';		
		$metin = str_replace( "I", "ı", $metin );
		$ilkHarf = mb_substr($metin, 0, 1, $encoding);
		if( $ilkHarf == "i" )
			$metin = str_replace( "i", "İ", $metin );
		if( $ilkHarf == "ı" )
			$metin = str_replace( "ı", "I", $metin );
		return mb_convert_case( $metin, MB_CASE_TITLE, "UTF-8" );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihi 22-01-2015 şeklinde saat olmadan verir */
	public function tarihFormatiDuzelt( $tarih ) {
		if( strlen( $tarih ) < 1 ) return '00-00-0000';
		$t 	= explode( ' ', $tarih );
		$t	= explode( '-', $t[ 0 ] );
		return $t[ 2 ] . '.' . $t[ 1 ] . '.' . $t[ 0 ];
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 00:00 şeklinde verir */
	public function saatFormatiDuzelt( $tarih ) {
		if( strlen( $tarih ) < 1 ) return '00:00';
		$t 	= explode( ' ', $tarih );
		$s	= explode( ':', $t[ 1 ] );
		return $s[ 0 ] . ':' . $s[ 1 ];
	}

	/* 1986-04-23 09:32:52 formatındaki tarihi 22-01-2015 şeklinde saat olmadan verir */
	public function tarihVer( $tarih ) {
		if( $tarih == "" or $tarih == null )
			return "";
		else
			return date('d.m.Y', strtotime( $tarih ) );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 09:32 şeklinde verir */
	public function saatDakikaVer( $tarih ) {
		return date('H:i', strtotime( $tarih ) );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 09:32:52 şeklinde verir */
	public function saatDakikaSaniyeVer( $tarih ) {
		return date('H:i:s', strtotime( $tarih ) );
	}

	/* 1986-04-23 23:32:52 formatındaki tarihi 23-04-1986 23:32:52 şeklinde verir */
	public function tarihSaatVer( $tarih ) {
		return date('d-m-Y H:i:s', strtotime( $tarih ) );
	}

	public function uzantiVer( $str ) {
		if( !strlen( $str ) ) return $str;
		$tersMetin		= strrev( $str );
		$dizi			= explode( '.', $tersMetin );
		return strrev( $dizi[ 0 ] );
	}
	/* Bir metindeki boşlukları '_' karakteri ile ve türkçe karakterleri de inglizce karakterleri ile değiştirir. */
	public function turkceKarakterSil( $s ) {
		$s	= str_replace( ' ', "_", $s );
		$tr	= array( 'ç', 'Ç', 'ı', 'İ', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü', 'ğ', 'Ğ' );
		$en	= array( 'c', 'C', 'i', 'I', 'o', 'O', 's', 'S', 'u', 'U', 'g', 'G' );
		return str_replace( $tr, $en, $s );
	}
	/* Rakam olarak verilne ayın adını ver*/
	public function ayAdiVer( $kacinci_ay, $ad_uzunlugu = 0 ) {
		$aylar = array(
			 1 =>	array( 'Ocak'		,'Oc.'		,"ocak")
			,2 =>	array( 'Şubat'		,'Şub.'		,"subat")
			,3 =>	array( 'Mart'		,'Mar.'		,"mart")
			,4 =>	array( 'Nisan'		,'Nis.'		,"nisan")
			,5 =>	array( 'Mayıs'		,'May.'		,"mayis")
			,6 =>	array( 'Haziran'	,'Haz.'		,"haziran")
			,7 =>	array( 'Temmuz'		,'Tem.'		,"temmuz")
			,8 =>	array( 'Ağustos'	,'Ağus.'	,"agustos")
			,9 =>	array( 'Eylül'		,'Eyl.'		,"eylul")
			,10 =>	array( 'Ekim'		,'Ek.'		,"ekim")
			,11 =>	array( 'Kasım'		,'Kas.'		,"kasim")
			,12 =>	array( 'Aralık'		,'Ara.'		,"aralik")
		);
		return $aylar[ $kacinci_ay ][ $ad_uzunlugu ];
	}

	//2022-04-30 formatındaki tarihe ait günü veriyor
	public function gunVer($gelenTarih,$locale='tr'){
	    $gelentarih=explode ("-",$gelenTarih);
	    //							     AY             Gün              YIL
	    $gun = date("l",mktime(0,0,0,$gelentarih[1],$gelentarih[2],$gelentarih[0])); 
	    if ($locale == 'tr') {
	        switch ($gun) {
	            case 'Monday':
	                    return 'Pazartesi';
	                break;

	            case 'Tuesday':
	                    return 'Salı';
	                break;

	            case 'Wednesday':
	                    return 'Çarşamba';
	                break;

	            case 'Thursday':
	                    return 'Perşembe';
	                break;

	            case 'Friday':
	                    return 'Cuma';
	                break;

	            case 'Saturday':
	                    return 'Cumartesi';
	                break;
	            
	            default:
	                    return 'Pazar';
	                break;
	        }
	    }else{
	        return $gun;
	    }
	}

	//Tek Haneli Sayi Veriyor
	public function ikiHaneliVer($sayi){
		return strlen($sayi) > 1 ? $sayi : '0'.$sayi;
	}

	//Giriş Çıkış tablosunda kayırlı olan sat ve güncellenen saatin karsılaştırmasını yapıyoruz Eger guncelenen bir saat varsa onu ele alacağız yoksa kaytıtlı saati alacagız 
	public function saatKarsilastir($kayitliSaat,$guncellenenSaat){
		if ($guncellenenSaat == "" or $guncellenenSaat == "00:00:00") {
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($kayitliSaat));
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($kayitliSaat));
			
		}else{
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($guncellenenSaat));
			$saat[] = $kayitliSaat == '' ? ' - ' : '<b class="text-danger">'.date("H:i",strtotime($kayitliSaat)).'</b>';
		}
		return $saat;
	}

	public function islemTipi($islemtipi,$personel_id,$tarih){
		$sonuc  = "";
		$baslik = "Tutanak Oluştur";
		if (!array_key_exists( "gelmedi", $islemtipi)) {
			if (count($islemtipi) == 0) {
				$baslik  = 	'Mesaide';
			}else if ( array_key_exists( "gecgelme", $islemtipi ) or array_key_exists( "erkencikma", $islemtipi ) ){
				$sonuc = array_key_exists( "gecgelme", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gecgelme&saat='.$islemtipi["gecgelme"].'" class="btn btn-outline-info btn-xs col-sm-12" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn" >Geç Gelme Yazdır </a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gecgelme&saat='.$islemtipi["gecgelme"].'" target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Geç Gelme Word İndir</a>' : '';

				//Personel erken çıkmış
				$sonuc .= array_key_exists( "erkencikma", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=erkencikma&saat='.$islemtipi["erkencikma"].'" class="btn btn-outline-primary btn-xs col-sm-12 mt-1" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn">Erken Çıkma Yazdır</a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=erkencikma&saat='.$islemtipi["erkencikma"].'"  target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Erken Çıkma Word İndir</a>' : '';
			}else{
				$sonuc = '<b class="text-center text-warning">'.implode( ", ", $islemtipi).'</b>';
			}
		}else{
			//Personel hiç giriş yapmamış ise 
			$sonuc =  array_key_exists( "gelmedi", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gunluk" class="btn btn-danger btn-xs col-sm-12" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn">Tutanak Tut</a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gunluk"  target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Word İndir</a>' : '<b class="text-center text-warning">'.implode( ", ", $islemtipi ).'</b>';
		}
		if ( $baslik == 'Mesaide' ){
			$sonuc  = '<b class="text-success">Mesaide</b>';
		}else{
			//dropdown menu oluşturma
			$sonuc = '
			<div class="btn-group">
	          <button modul="giriscikis" yetki_islem="tutanak_olustur" type="button" class="btn btn-xs btn-default" data-toggle="dropdown" aria-expanded="false">'.$baslik.'</button>
	          
	          </button>
	          <div class="dropdown-menu" role="menu" style="min-width:180px; padding:10px;">
	            '.$sonuc.'
	          </div>
	        </div>';
		}
			

		return $sonuc;
	}

	public function islem_tipi_isim( $islem ) {
		
		switch ($islem) {
			case 'gunluk':
				$sonuc = 'Gelmeme';
				break;

			case 'gecgelme':
				$sonuc = 'Geç Gelme';
				break;
			
			case 'erkencikma':
				$sonuc = 'Geç Gelme';
				break;
		}

		return $sonuc;
	}

	public function saatfarkiver( $baslangic, $bitis, $fonksiyon = "asagi" ) {
		// $baslangic = new DateTimeImmutable('12:12:10');
		// $bitis = new DateTimeImmutable('12:12:12');
		// $interval = $baslangic->diff($bitis);
		// echo intVal($interval->format('%R%s'));


		//baslangicSaati => o zamana kadar geçen saniyesini buluyoruz.
		$baslangicSaati = strtotime($baslangic);
		
		//bitisSaati => o zamana kadar geçen saniyesini buluyoruz.
		$bitisSaati = strtotime($bitis);
		
		//Aradaki saniye farkını bulduk.
		$fark = $bitisSaati - $baslangicSaati;

		$dakika = $fark / 60;
		return $dakika;
	
	}

	/* 1000,2546 sekindeki parayı 1,000.25 şeklinde vermektedir sayı virgülden sonra kaç basamak oluşturacağını belirler*/
	public function parabirimi($tutar,$sayi=2){
	    return number_format($tutar,$sayi,".","");
	}

	/* Belirli bir tarihin yılın kacıncı haftası olduğunu sorgular Tarih formatı tarih içindeki karekter " - (kısacizgi) " veya  " . (nokta) " olmalı */
	public function kacinciHafta( $tarih ){
	    $tarih = new DateTime( $tarih );
		$hafta = $tarih->format("W");
		return $hafta;
	}

	/*Puantaj Hesaplama İşlemleri tarih formatı => 2022-07, $sayi => gün 01, 05, 30, */
	public function puantajHesapla($personel_id,$tarih,$sayi, $grup_id,$genelCalismaSuresiToplami = array(),$tatil_mesaisi_carpan_id,$normal_carpan_id,$kapatilmis = 0 ){

		$KullanilanSaatler 				= array(); 	// Hangi tarilerin uygulanacağını kontrol ediyoruz
		$kullanilacakMolalar 			= array(); 	//tarifelerer ait molalar
		$saatSay 						= 0;
		$asilkullanilanMolalar			= array(); 	//Personelin Kullandığı molalar
		$calismasiGerekenToplamDakika 	= array(); 	//Calışması gereken toplam dakika
		$calisilanToplamDakika 			= array(); 	//Personelin çalıştığı toplam dakika
		$kullanilanToplamMola			= array(); 	//Asil Molaların Toplamı
		$kullanilmayanMolaToplami		= array(); 
		$islenenSaatler					= array(); 
		$izin[ "ucretli" ]				= 0; 
		$izin[ "ucretsiz" ]				= 0; 
		$kullanilmasiGerekenToplamMola	= 0; 
		$tatil_mesaisi	 				= 0; 		//Tatil Mesaisinin toplam kaç dakika olduğunu gönderilecek Genellikle Yarım Gün Çalışılacak Günler İçin Hesaplanacak
		$gecGelmeTolerans 				= 0;		//Personelin Toplam Geç Gelme Toleransı aktarılacak (Dakika Hesaplaması)
		$erkenCikmaTolerans				= 0;		//Personelin Toplam Erken Çıkma Tolaransı aktarılacak (Dakika Hesaplaması)
		
		/*Personele Ait Giriş Çıkış Saatleri*/
		$personel_giris_cikis_saatleri 			= $this->vt->select( self::SQL_belirli_tarihli_giris_cikis,array($personel_id,$tarih."-".$sayi))[2];
		$personel_giris_cikis_sayisi   			= count($personel_giris_cikis_saatleri);
		$rows = $personel_giris_cikis_sayisi 	== 0 ?  1 : $personel_giris_cikis_sayisi;

		/*Perosnel Giriş Yapmış ise tatilden Satılmayacak Ek mesai oalrak hesaplanacaktır. */
		if($personel_giris_cikis_sayisi > 0) {
			$tatil = 'hayir';
		}

		//Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
		$son_cikis_index 	= $personel_giris_cikis_sayisi - 1;

		$ilkGirisSaat 		= $this->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

		$SonCikisSaat 		= $this->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);

		/*Tairhin hangi güne denk oldugunu getirdik*/
		$gun = $this->gunVer($tarih."-".$sayi);
		if( $kapatilmis == 0){
			/*Belirtilen Tarihe uyan tarifeyi getirdik*/
			$giris_cikis_saat_getir = $this->vt->select( self::SQL_giris_cikis_saat, array( $tarih."-".$sayi, $tarih."-".$sayi, '%,'.$gun.',%', '%,'.$grup_id.',%' ) ) [ 2 ];
			
			/*tarifeye ait mesai saatleri */
			$saatler 			= $this->vt->select( self::SQL_tarife_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];
			
			/*tarifeye ait mola saatleri */
			$molalar 			= $this->vt->select( self::SQL_mola_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];	
		}else{
			
			/*Belirtilen Tarihe uyan tarifeyi getirdik*/
			$tarifeOgren = $this->vt->select( self::SQL_tarife_ogren, array( $personel_id, $tarih."-".$sayi ) ) [ 2 ][ 0 ];
			
			/*Belirtilen Tarihe uyan tarifeyi getirdik*/
			$giris_cikis_saat_getir = $this->vt->select( self::SQL_kapatilmis_tarife_getir, array( $tarifeOgren[ "tarife" ] ) ) [ 2 ];

			/*tarifeye ait mesai saatleri */
			$saatler 			= $this->vt->select( self::SQL_kapatilan_tarife_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];
			
			/*tarifeye ait mola saatleri */
			$molalar 			= $this->vt->select( self::SQL_kapatilan_mola_saati, array( $giris_cikis_saat_getir[ 0 ][ 'id' ] ) )[ 2 ];	
		}
		
		$mesai_baslangic 	= date("H:i",  strtotime( $saatler[ 0 ]["baslangic"] )  );
		$mesai_bitis 		= date("H:i",  strtotime( $saatler[ 0 ]["bitis"] ) );

		if( $saatler[ 0 ][ "carpan" ] == $normal_carpan_id ){
		
			//Geç Gelme Toleransını karsılaştıryoruz tolerabstan küçük veya eşşit işe farkı saate ekliyoruz
			$gecGelmeFark 		= $this->saatfarkiver( $mesai_baslangic, $ilkGirisSaat[ 0 ]);
			if( $gecGelmeFark 	>  0 AND $gecGelmeFark 	<=  $giris_cikis_saat_getir[ 0 ][ 'gec_gelme_tolerans' ] ){
				$gecGelmeTolerans += $gecGelmeFark;
			}
			//Erken Çıkma Toleransını karsılaştıryoruz tolerabstan küçük veya eşşit işe farkı saate ekliyoruz
			$erkenCikmaFark 	= $this->saatfarkiver( $SonCikisSaat[ 0 ], $mesai_bitis); 
			if( $erkenCikmaFark >  0 AND $erkenCikmaFark <= $giris_cikis_saat_getir[ 0 ][ 'erken_cikma_tolerans' ]){
				$erkenCikmaTolerans += $erkenCikmaFark;
			}
		}
		$toplamTolerans = $erkenCikmaTolerans + $gecGelmeTolerans;
		
		//Eger Tatil Olarak İsaretlenmisse Giriş Zorunluluğu bulunmayıp mesaiye gelmisse mesai yazdıracaktır.
		$tatil 				= $giris_cikis_saat_getir[ 0 ]["tatil"] 			 == 1  	?  'evet' : 'hayir';
		$maasa_etki_edilsin = $giris_cikis_saat_getir[ 0 ]["maasa_etki_edilsin"] == 1  	?  'evet' : 'hayir';
		$saySaat = 0;
		/*Personelin Hangi saat dilimler,nde maasın hesaplanacağını kontrol ediyoruz*/	
		foreach ( $saatler as $alan => $saat ) {
			if ( $SonCikisSaat[ 0 ] <= $saat[ "bitis" ] AND  $saat[ "baslangic" ] <= $SonCikisSaat[ 0 ]   ){
				$saySaat = $alan;
			}
		}

		/*Personelin HaNGİ saat dilimine kadar çalışmiş ise o zaman dilimlerini diziye aktarıyoruz*/
		while ($saatSay <= $saySaat ) {
			$KullanilanSaatler[] = $saatler[ $saatSay ];
			$saatSay++;
		}

		/*Personelin Çalıştığı saat dilimleri arasında kullandığı mola saatlerinizi alıyoruz*/
		foreach ( $molalar as $mola ) {
			foreach ( $KullanilanSaatler as $key => $saat ) {
				if ( $saat[ "baslangic" ] <= $mola[ "baslangic" ] AND $mola[ "bitis" ] <= $saat[ "bitis" ] ){
					$kullanilacakMolalar[ $saat[ "carpan" ] ][] = $mola;
				}
			}
		}
		/*Personelin tarifeye ait saat dilimleri arasında kaç saat çalışması gerektigini kotrol ediyoruz*/
	 	foreach ( $KullanilanSaatler as $saatkey => $saat ) {
			if( $saat[ "carpan" ] 	== $tatil_mesaisi_carpan_id ){
				$tatil_mesaisi 		+= $this->saatfarkiver( $saat[ "baslangic" ], $saat[ "bitis" ] );
			}
	 		$calismasiGerekenToplamDakika[ $saat[ "carpan" ] ] += $this->saatfarkiver( $saat[ "baslangic" ], $saat[ "bitis" ] );
	 	}

	 	/*Kullanılacak Molaların hangilerinin kullandığını kontrol ediyoruz*/
		foreach ( $kullanilacakMolalar as $molakey => $molalar ) {
			foreach ($molalar as $key => $mola) {
				foreach ( $personel_giris_cikis_saatleri as $giris ) {
					/*Personel İzinli Değilse */
					if( $giris[ "islemTipi" ]  == '' ){
						if ( $giris[ "baslangic_saat" ] <= $mola[ "baslangic" ]  AND $mola[ "bitis" ] <= $giris[ "bitis_saat" ]){
								$asilkullanilanMolalar[ $molakey ][] = $mola;
						}else if( $mola[ "bitis" ] <= $giris[ "bitis_saat" ] ){
							if ( $mola[ "baslangic" ] <= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "baslangic_saat" ] ) {
								$asilkullanilanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $giris[ "baslangic_saat" ];
								$asilkullanilanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ];
							}
						}else if ( $mola[ "bitis" ] >= $giris[ "bitis_saat" ] ){
							if ( $mola[ "baslangic" ] >= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "bitis_saat" ] AND $mola[ "baslangic" ] < $giris[ "bitis_saat" ]) {
								$asilkullanilanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $mola[ "baslangic" ];
								$asilkullanilanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $giris[ "bitis_saat" ];
							}
						}
					}else{
						/*Personel İzinli İse */
						if ( $giris[ "baslangic_saat" ] <= $mola[ "baslangic" ]  AND $mola[ "bitis" ] <= $giris[ "bitis_saat" ]){
								$kullanilmayanMolalar[ $molakey ][] = $mola;
						}else if( $mola[ "bitis" ] <= $giris[ "bitis_saat" ] ){
							if ( $mola[ "baslangic" ] <= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "baslangic_saat" ] ) {
								$kullanilmayanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $giris[ "baslangic_saat" ];
								$kullanilmayanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ];
							}
						}else if ( $mola[ "bitis" ] >= $giris[ "bitis_saat" ] ){
							if ( $mola[ "baslangic" ] >= $giris[ "baslangic_saat" ] AND $kullanilacakMolalar[ $molakey ][ $key ][ "bitis" ] > $giris[ "bitis_saat" ] AND $mola[ "baslangic" ] < $giris[ "bitis_saat" ]) {
								$kullanilmayanMolalar[ $molakey ][ $key ][ "baslangic" ] 	= $mola[ "baslangic" ];
								$kullanilmayanMolalar[ $molakey ][ $key ][ "bitis" ] 		= $giris[ "bitis_saat" ];
							}
						}
					}
				}
			}
		}
		/*Kullanılan Molaların Toıoplam Süresi Dakika HEsaplaması*/
	 	foreach ( $asilkullanilanMolalar as $molakey => $molalar ) {
	 		foreach ($molalar as  $mola) {
	 			$kullanilanToplamMola[ $molakey ] += $this->saatfarkiver( $mola[ "baslangic" ], $mola[ "bitis" ] ); 
	 		}
	 	}

	 	/*Personel giriş çıkış yapmış ise çıkış giriş arasında kullanmadığı molaları hesaplıyoruz*/
	 	foreach ( $kullanilmayanMolalar as $molakey => $molalar ) {
	 		foreach ($molalar as  $mola) {
	 			$kullanilmayanMolaToplami[ $molakey ] += $this->saatfarkiver( $mola[ "baslangic" ], $mola[ "bitis" ] ); 
	 		}
	 	}
	 	/*İlk Giriş Saatini aliyoruz */
		if ( $ilkGirisSaat[ 0 ] < $mesai_baslangic ) {
			$ilkGirisSaat[ 0 ] = $mesai_baslangic;
		} 

		/*son Çıkış Saatini aliyoruz */
	 	if ( $SonCikisSaat[0] >= $KullanilanSaatler[ count( $KullanilanSaatler ) - 1 ][ "bitis" ] ) {
			$SonCikisSaat[ 0 ] = $KullanilanSaatler[ count( $KullanilanSaatler ) - 1 ][ "bitis" ];
		}

		ksort($KullanilanSaatler);
		$i 				= 0; //Saatlere ait index
		$kullanildi 		= 0; // ilk giriş şim hesaplanması yapıldımı kontrol için 
		/*Tarifenin başlangıc saati yani normal mesai saat aralığı*/
		$ilkUygulanacakSaat = $normal_carpan_id;
		/*Personelin Toplam Çalışma Sürelerini Hesaplama*/
	 	foreach ( $personel_giris_cikis_saatleri as $girisKey => $giris ) {
	 		$i = 0;	
	 		if ( $giris[ "islemTipi" ]  != '' AND  $girisKey == 0  ){
	 			$kullanildi = 1;
	 		}else{
	 			foreach ($KullanilanSaatler as $saatkey => $saat) {

		 			if ( $kullanildi == 0 ) {

		 				if ( $giris["bitis_saat"] > $saat["bitis"] ){
		 					$fark = $this->saatfarkiver( date("H:i",strtotime($saat[ "bitis" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );;
		 					$calisilanToplamDakika[ $saat["carpan"] ] += $this->saatfarkiver( date("H:i",strtotime($ilkGirisSaat[0])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) ) - $fark;
		 				}else{
		 					$calisilanToplamDakika[ $saat["carpan"] ] += $this->saatfarkiver( date("H:i",strtotime($ilkGirisSaat[0])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
		 				}
		 				
		 				$kullanildi = 1;

		 			}else if( $giris["bitis_saat" ] < $saat["bitis"] and $kullanildi == 1  and $giris["bitis_saat" ] >= $saat["baslangic"]  ){

		 				$fark = $this->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($saat[ "baslangic" ] ) ) );;
		 				if ( $giris[ "islemTipi" ]  == '' ){
		 					if( $fark > 0 ){
			 					$calisilanToplamDakika[ $saat["carpan"] ] += $this->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) ) - $fark;
			 					if ( $girisKey != 0 ) {
			 						$calisilanToplamDakika[ $KullanilanSaatler[$i - 1]["carpan"] ] += $fark;
			 					}
			 					
			 				}else{
			 					$calisilanToplamDakika[ $saat["carpan"] ] += $this->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
			 				}
		 				}
			 				
		 			}
		 			$i++;

		 			/*
		 				personelin maas kesintisi degeri 0 veya boş işe ücretli izin veya normal giriş çıkış yapmıştır personelin masından kesinti yapılmayacaktır
						personelin maas Kesintisi degeri 1 olması halinde ücretsiz izin aldığını belirtir 
		 			*/
		 		}
	 		}
		 		
	 		if( $giris[ "maas_kesintisi" ]  == 0 AND $giris[ "islemTipi" ]  != ''  ){	
 				$izin[ "ucretli" ] += $this->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
 			}else if( $giris[ "maas_kesintisi" ]  == 1 ) {
 				$izin[ "ucretsiz" ] += $this->saatfarkiver( date("H:i",strtotime($giris[ "baslangic_saat" ])), date("H:i",strtotime($giris[ "bitis_saat" ] ) ) );
 			}
	 	}


	 	/*tarifeye ait molaların hangilerinin kullandığını kontrol edip toplam kaç dakika mola kullanılmış kontrol sağlıyoruz*/
		foreach ($KullanilanSaatler as $saatkey => $saat) {
			if ( $calisilanToplamDakika[ $saat[ "carpan" ] ] >= $kullanilanToplamMola[ $saat[ "carpan" ] ] ) {
				$calisilanToplamDakika[ $saat[ "carpan" ] ] -= $kullanilanToplamMola[ $saat[ "carpan" ] ];
			}else{
				$calisilanToplamDakika[ $saat[ "carpan" ] ] = '0';
			}
		}

		/*Tüm Günlerin calışma sürelerini carpani ile birlikte dizide topluyoruz*/
		foreach ($calisilanToplamDakika as $carpan => $dakika) {
			if ( $dakika > 0 AND $sayi == 31 AND $ilkUygulanacakSaat == $carpan)
				echo "";
			else
				$genelCalismaSuresiToplami[ $carpan ] += $dakika;	
		}

		foreach ($kullanilacakMolalar[ $ilkUygulanacakSaat ] as $molakey => $mola) {
			$kullanilmasiGerekenToplamMola += $this->saatfarkiver($mola[ "baslangic" ], $mola[ "bitis" ]);
		}
		
		$calisilanToplamDakika[ $ilkUygulanacakSaat ] += $toplamTolerans;
		$genelCalismaSuresiToplami[ $ilkUygulanacakSaat ] += $toplamTolerans;

		/*
		Eger Tatil ve Maaşa Etki edilecekse ve pazar gününe eşit ise toplam hafta byunca gelmediği günü hesaplıyoruz
		Genel Ayarlarda kaç gün gelmediğinde pazar verilmeyeceği bilgisini alıp hesaba göre kesinti yapılacak
		*/
		$gun = $this->gunVer("$tarih-$sayi");
		if( $tatil == "evet" AND $maasa_etki_edilsin == "evet" AND $gun == "Pazar" ){	
			/*Tarihi AYıl ve ve ay olarak boluyoruz*/
			$tarihBol 	= explode("-",$tarih);
			$yil 		= $tarihBol[0];
			$ay 		= $tarihBol[1];

			/*O Haftaya ait tüm puantaj bilgisini çekiyoruz*/
			$KacinciGun = intval(date("N", strtotime("$tarih-$sayi")));

			$KacinciGun = intval($KacinciGun -1 );

			$haftaBaslangici 	= date("Y-m-d", strtotime("$tarih-$sayi -$KacinciGun day"));
			
			/*
				Dönem Sonucu 1 Den büyük ise Dönemin kapatılmış oldugunu belirtir. ve genel ayarları tb_donem tablsundan çekeceğiz.
				0 ise donemin kapatılmadıgını belirtil genel ayarları tb_genel_ayarlar tablsoundan çekeceğiz.
				Kaç Gün gelmediğinden pazar verilmesin degerini alıp karsılatırma yapıp kesinti uygulayacağız"
			*/
			$donem	 		= $this->donemKontrol($yil, $ay); 
			if( $donem > 0 ){
				$ayarlar  	= $this->vt->select( self::SQL_donem_ayarlar, array( $_SESSION[ 'firma_id' ], $yil, $ay ) )[ 2 ][0]; 
			}else{
				$ayarlar 	= $this->vt->select( self::SQL_genel_ayarlar, array( $_SESSION[ 'firma_id' ] ) )[ 2 ][ 0 ];  
			}
			$eksikGunSay 	= $this->vt->select( self::SQL_eksik_gun_say, array( $personel_id, $haftaBaslangici, "$tarih-$sayi"  ) )[ 2 ][ 0 ][ "toplam" ];
			if( $eksikGunSay >= $ayarlar[ "pazar_kesinti_sayisi" ] ){
				$pazar_kesintisi = $ayarlar[ "gunluk_calisma_suresi" ];
			}

		}
		
		$sonuc["KullanilanSaatler"] 			= $KullanilanSaatler; 			 // Hangi tarilerin uygulanacağını kontrol ediyoruz
		$sonuc["kullanilacakMolalar"] 			= $kullanilacakMolalar; 		 //tarifelerer ait molalar
		$sonuc["saatSay"] 						= $saatSay; 					
		$sonuc["asilkullanilanMolalar"] 		= $asilkullanilanMolalar;		 //Personelin Kullandığı molalar
		$sonuc["calismasiGerekenToplamDakika"] 	= $calismasiGerekenToplamDakika;  //Calışması gereken toplam dakika
		$sonuc["calisilanToplamDakika"] 		= $calisilanToplamDakika; 		 //Personelin çalıştığı toplam dakika
		$sonuc["kullanilanToplamMola"] 			= $kullanilanToplamMola;		 //Asil Molaların Toplamı
		$sonuc["kullanilmayanMolaToplami"] 		= $kullanilmayanMolaToplami;	 
		$sonuc["islenenSaatler"] 				= $islenenSaatler;				 
		$sonuc["ucretli"] 						= $izin[ "ucretli" ];			 
		$sonuc["ucretsiz"] 						= $izin[ "ucretsiz" ];			 
		$sonuc["kullanilmasiGerekenToplamMola"] = $kullanilmasiGerekenToplamMola;
		$sonuc["personel_giris_cikis_sayisi"] 	= $personel_giris_cikis_sayisi;
		$sonuc["personel_giris_cikis_saatleri"] = $personel_giris_cikis_saatleri;
		$sonuc["genelCalismaSuresiToplami"] 	= $genelCalismaSuresiToplami;
		$sonuc["tatil"] 						= $tatil;
		$sonuc["maasa_etki_edilsin"] 			= $maasa_etki_edilsin;
		$sonuc["ilkUygulanacakSaat"] 			= $ilkUygulanacakSaat;
		$sonuc["tatil_mesaisi"] 				= $tatil_mesaisi;
		$sonuc["pazar_kesintisi"] 				= $pazar_kesintisi;
		
		return $sonuc;

		
	}

	/*Verilen dakikayı Saate Çevirir.  300 DK => 3.00 Saat Şeklinde verir*/
	public function dakikaSaatCevir($dakika){
		$saat 	= floor( $dakika / 60 );
		$dakika 	= $this->ikiHaneliVer(floor( $dakika % 60 ));
		return $saat.":".$dakika;
	}

	public function dakikaSaatCevirString($dakika){
		$ham_dakika = intVal($dakika);
		$saat 	= floor( $dakika / 60 );
		$dakika 	= $this->ikiHaneliVer(floor( $dakika % 60 ));
		if($ham_dakika==0)
			return "-";
		else
			return $saat."sa ".$dakika."dk (".$ham_dakika." dk)";
	}

	/*Puantajı Kaydetme Guncelleme işlemi */

	public function puantajKaydet($personel_id,$tarih,$sayi,$hesapla = array()){
		$izin = 0;
		$calismasiGerekenToplamDakika  	= $hesapla["calismasiGerekenToplamDakika"];
		$calisilanToplamDakika 		 	= $hesapla["calisilanToplamDakika"];
		$kullanilmasiGerekenToplamMola 	= $hesapla["kullanilmasiGerekenToplamMola"];
		$ilkUygulanacakSaat 		 	= $hesapla["ilkUygulanacakSaat"];
		$tatil 							= $hesapla["tatil"] 		    	== "hayir" ? 0 : 1;
		$maasa_etki_edilsin 			= $hesapla["maasa_etki_edilsin"] 	== "hayir" ? 0 : 1;
		$ucretli_izin 					= $hesapla["ucretli"];
		$ucretsiz_izin 					= $hesapla["ucretsiz"];
		$pazarKesintisi 				= $hesapla["pazar_kesintisi"];
		$tatilMesaisi 					= $hesapla["tatil_mesaisi"];
		
		if( $tatilMesaisi > 0 ){
			$tatilMesaisi = 1;
		}
													

		$toplamIzin 					= $ucretli_izin + $ucretsiz_izin;
		$cikarilacakMola 				= $kullanilmasiGerekenToplamMola;

		$toplam_kesinti 				= $calismasiGerekenToplamDakika[$ilkUygulanacakSaat] - $calisilanToplamDakika[$ilkUygulanacakSaat] - $toplamIzin  - $cikarilacakMola + $pazarKesintisi;

		/*Hesaplama işleminin Veri Tabanına Kaydedilme İşlemi*/

		/*Oncelikle o günün veri tabanında kayıtlı lolup olmadığını kontrol ediyoruz kayıt var ise guncelleme yapılacak yok ise eklemesi yapılacak*/
		$puantaj_varmi		= $this->vt->select( self::SQL_puantaj_oku, array($personel_id,$tarih."-".$sayi ) ) [2];
		$calisma 			= json_encode($calisilanToplamDakika);

		$veriler = array(
			$personel_id,
			$tarih."-".$sayi,
			$izin,
			$calisma,
			$ucretli_izin,
			$ucretsiz_izin,
			$toplam_kesinti, 
			$tatil,
			$maasa_etki_edilsin,
			$tatilMesaisi
		);

		if( count($puantaj_varmi) > 0 ){
			array_push( $veriler, $puantaj_varmi[ 0 ][ 'id' ] );
			$this->vt->update(self::SQL_puantaj_guncelle, $veriler );
		}else{
			/*Yeni puantaj ekelenecek*/
			
			$this->vt->insert( self::SQL_puantaj_kaydet, $veriler );
		}
		return true;
	}
	
	public function donemKontrol( $yil, $ay ){
		$donem = $this->vt->select( self::SQL_donem_kontrol, array( $_SESSION[ 'firma_id' ], $yil, $ay ) )[ 3 ];
		return $donem;
	}

	public function bosSatir($sayi,$gun,$sutunSayisi){
		$sutunlar 	= "";
		$sutunlar  	.=  "<td>$sayi</td>";
		$sutunlar  	.=  "<td>$gun</td>";
		$sutunlar  	.=  "<td colspan='$sutunSayisi' class='text-center' > - </td>";
		$i = 1;
		while ( $i <= $sutunSayisi - 1 ) {
			$sutunlar .= "<td class='d-none'></td>";
			$i++;
		}
		return $sutunlar;
	}

	public function agacListeleSelect( $kategoriler, $sayi = 0, $cizgi, $ust_id,  $birlestir = ""){
		
		foreach( $kategoriler[ $sayi ] as $kategori ){
			
			$ciz 		= $cizgi == 0 ? '' : str_repeat("&#xf054; ",$cizgi);

			$selected  = $ust_id == $kategori["id"] ? 'selected' : ''; 

			$birlestir .= "<option value='$kategori[id]' $selected class='font-awesome'><i class=''>$ciz</i> $kategori[adi]</option>"; 
			
			if( array_key_exists( $kategori[ "id" ], $kategoriler ) ){

				$cizgi += 1;			
				$birlestir = $this->agacListeleSelect( $kategoriler, $kategori[ "id" ], $cizgi, $ust_id, $birlestir );
				$cizgi -= 1;

			}
			$cizgi += 0;	
		}

		return $birlestir;
	}

	public function agacListeleTablo( $kategoriler, $katid = 0, $cizgi, $birlestir = "",$sayi = 0,$aktifDT,$alt = 0, $altListe, $linkAltListe ){
		$sayi = $katid == 0 ? $sayi += 1 : ""; 

		$islemGenislik = array(
			1 => 180,
			2 => 160,
			3 => 197,
		);
		$renkler = array(
			1 => "table-secondary",
			2 => "table-primary",
			3 => "table-active",
			4 => "table-primary",
			5 => "table-secondary",
			6 => "table-primary",
			7 => "table-active",
			8 => "table-danger"
		);

		foreach( $kategoriler[ $katid ] as $kategori ){

			$ciz 		= $cizgi == 0 ? '' : str_repeat("<i class='fas fa-level-up-alt' style='transform: rotate(90deg);'></i>&nbsp; &nbsp;",$cizgi);

			$suanki_tarih 				= date_create(date('Y-m-d'));
			$hatirlanacak_tarih 		= date_create($kategori[ 'tarih' ]);
			if ( $kategori[ 'tarih' ] 	!= '0000-00-00' AND $suanki_tarih < $hatirlanacak_tarih ) {
				$kalan_gun 				= date_diff($suanki_tarih,$hatirlanacak_tarih);
				$gunBelirt 				=  $kalan_gun->format("%a Gün Kaldı");
			}
			/*Secilmiş olan satırı sarı yapar*/
			$satirRenk 		= $kategori[ "id" ] == $aktifDT ? "table-warning" : "";
			/*Alt dosya kategorileri mavi yapar*/
			$satirRenk2 	= $katid > 0 ? "table-info": "";
			/*alt kategori varsa tıklanır yapacak satırı ve acılmasını sağlayacak*/
			$acilir 		= array_key_exists( $kategori[ "id" ], $kategoriler ) ? "data-widget='expandable-table'" : "";
			$satirRenk3		= "";
			if (array_key_exists( $kategori[ "id" ], $kategoriler ) AND $katid != 0 )  
				$satirRenk3 = "table-success" ;

			$altListeBirlestir = implode("-", $altListe);

			$altKategori =explode( "-", $linkAltListe);
			$expanded 	= in_array( $kategori[ "id" ], $altKategori ) ? "true" : "false";
			$style 		= in_array( $kategori[ "id" ], $altKategori ) ? "display:table !important;" : "";


			if( $alt == 1 ){
				$birlestir .= "<tr class='mouseSagTik $renkler[$cizgi] $satirRenk3 $satirRenk ' $acilir aria-expanded='$expanded' data-id='$kategori[id]' data-alt-liste='$altListeBirlestir'> 
									<td>$ciz</td>
									<td>$kategori[adi]</td>
									<td>$gunBelirt</td>
									<td>$kategori[dosyaSayisi]</td>
									<td>$kategori[altKategoriSayisi]</td>
									<td>
										<a class='nav-link btn btn-xs btn-light ' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
											<i class='fas fa-bars'></i>
										</a>
										<div class='dropdown-menu p-1 dropdown-menu-right' aria-labelledby='navbarDropdown'>
											<a data-toggle='modal' data-target='#dosyaTuru' class=' my-1 text-center btn btn-light w-100'><i class='fas fa-plus'></i>&nbsp; Kategori Ekle</a>
											<a modul = 'firmaDosyalari' yetki_islem='evraklar' class=' my-1 btn btn-dark text-white w-100' href = '?modul=firmaDosyalari&islem=evraklar&ust_id=$kategori[kategori]&kategori_id=$kategori[id]&dosyaTuru_id=$kategori[id]&alt-liste=$altListeBirlestir '>Evraklar</a>
											<a modul = 'firmaDosyalari' yetki_islem='duzenle' class=' my-1 btn  btn-warning w-100' href = '?modul=firmaDosyalari&islem=guncelle&ust_id=$kategori[kategori]&kategori_id=$kategori[id]&dosyaTuru_id=$kategori[id]&alt-liste=$altListeBirlestir' >Düzenle</a>
											<button modul= 'firmaDosyalari' yetki_islem='sil' class=' my-1 btn btn-danger w-100' data-href='_modul/firmaDosyalari/firmaDosyalariSEG.php?islem=sil&konu=tur&dosyaTuru_id=$kategori[id]' data-toggle='modal' data-target='#kayit_sil'>Sil</button>
										</div>
									</td>
								</tr>";	
			}else{
				$birlestir .= "<tr class='mouseSagTik $renkler[$cizgi] $satirRenk3 $satirRenk  ' $acilir aria-expanded='$expanded' data-id='$kategori[id]' data-alt-liste='$altListeBirlestir'> 
								<td>$sayi</td>
								<td>$kategori[adi]</td>
								<td>$gunBelirt</td>
								<td>$kategori[dosyaSayisi]</td>
								<td>$kategori[altKategoriSayisi]</td>
								<td>
									<a class='nav-link btn btn-xs btn-light ' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
										<i class='fas fa-bars'></i>
									</a>
									<div class='dropdown-menu p-1 dropdown-menu-right' aria-labelledby='navbarDropdown'>
										<a data-toggle='modal' data-target='#dosyaTuru' class=' my-1 text-center btn btn-light  w-100'><i class='fas fa-plus'></i>&nbsp; Kategori Ekle</a>
										<a modul = 'firmaDosyalari' yetki_islem='evraklar' class=' my-1 btn btn-dark  text-white w-100' href = '?modul=firmaDosyalari&islem=evraklar&ust_id=$kategori[kategori]&kategori_id=$kategori[id]&dosyaTuru_id=$kategori[id]&alt-liste=$altListeBirlestir '>Evraklar</a>
										<a modul = 'firmaDosyalari' yetki_islem='duzenle' class=' my-1 btn  btn-warning  w-100' href = '?modul=firmaDosyalari&islem=guncelle&ust_id=$kategori[kategori]&kategori_id=$kategori[id]&dosyaTuru_id=$kategori[id]&alt-liste=$altListeBirlestir' >Düzenle</a>
										<button modul= 'firmaDosyalari' yetki_islem='sil' class=' my-1 btn  btn-danger w-100' data-href='_modul/firmaDosyalari/firmaDosyalariSEG.php?islem=sil&konu=tur&dosyaTuru_id=$kategori[id]' data-toggle='modal' data-target='#kayit_sil'>Sil</button>
									</div>
								</td>
							</tr>";	
			}
			 
			if( array_key_exists( $kategori[ "id" ], $kategoriler ) ){
				array_push( $altListe, $kategori[ "id" ]  );
				
				$cizgi 		+= 1;
				$ciz 		= $cizgi == 0 ? '' : str_repeat("<i class='fas fa-level-up-alt' style='transform: rotate(90deg);'></i>&nbsp; &nbsp;",$cizgi);
				$birlestir 	.= "<tr class='expandable-body'> 
								<td colspan='8'>
									<table class=' table-hover w-100' style='$style'>
										<th>$ciz</th>
										<th>Adı</th>
										<th>Kalan G.S.</th>
										<th>Dosya S.</th>
										<th>Kategori S.</th>
										<th style='width:75px;'>İşlemler</th>";	
				$birlestir 	= $this->agacListeleTablo( $kategoriler, $kategori[ "id" ], $cizgi,$birlestir,$sayi, $aktifDT,1,$altListe, $linkAltListe);

				$birlestir 	.= "</table></td></tr>";

				array_pop($altListe);

				$cizgi -= 1;

			}
		}

		return $birlestir;
	}

	public function oturumOlustur( $id ){

		$sorguSonuc = $this->vt->selectSingle( self::SQL_kontrol, array( $id ) );
		if( !$sorguSonuc[ 0 ] ) {
			$kullaniciBilgileri	= $sorguSonuc[ 2 ];

			if( $kullaniciBilgileri[ 'id' ] * 1 > 0 ) {
				$_SESSION[ 'kullanici_id' ]		= $kullaniciBilgileri[ 'id' ];
				$_SESSION[ 'adi' ]				= $kullaniciBilgileri[ 'adi' ];
				$_SESSION[ 'soyadi' ]			= $kullaniciBilgileri[ 'soyadi' ];
				$_SESSION[ 'ad_soyad' ]			= $kullaniciBilgileri[ 'adi' ] . ' ' . $kullaniciBilgileri[ 'soyadi' ];
				$_SESSION[ 'kullanici_resim' ]	= $kullaniciBilgileri[ 'resim' ];
				$_SESSION[ 'rol_id' ]			= $kullaniciBilgileri[ 'rol_id' ];
				$_SESSION[ 'rol_adi' ]			= $kullaniciBilgileri[ 'rol_adi' ];
				$_SESSION[ 'sube_id' ]			= $kullaniciBilgileri[ 'sube_id' ];
				$_SESSION[ 'subeler' ]			= $kullaniciBilgileri[ 'subeler' ];
				$_SESSION[ 'giris' ]			= true;
				$_SESSION[ 'giris_var' ]		= 'evet';
				$_SESSION[ 'yil' ]				= date('Y');
				$_SESSION[ 'super' ]			= $kullaniciBilgileri[ 'super' ];
				$_SESSION[ 'firmalar' ]			= explode(",",$kullaniciBilgileri[ "firmalar" ]);
				
				if( $_COOKIE[ 'firma_id' ] > 0 && $_COOKIE[ 'firma_adi' ] > 0 && array_key_exists($_COOKIE[ 'firma_id' ], $_SESSION[ 'firmalar' ] ) ){
					$_SESSION[ 'firma_id'] = $_COOKIE[ 'firma_id' ];
					$_SESSION[ 'firma_adi' ] = $_COOKIE[ 'firma_adi' ];
				}
			}
		}


	}

	public function gunSayisi ($istenCikisTarihi, $listelenecekAy, $yil, $ay  ){
		if( $istenCikisTarihi != "" AND date("Y-m",strtotime($istenCikisTarihi)) == $listelenecekAy  ){
			/*Personel Çıkış yapmış ve çıkış ayı listelecenek olan aya esit ise çıkış yapmış güne kadar dönder*/
			$gunSayisi = date("d", strtotime($istenCikisTarihi));	
		}else if( $istenCikisTarihi != "" AND date( "Y-m", strtotime( $istenCikisTarihi ) ) <= $listelenecekAy ){
			/*Personel Çıkış Yapmış ve listelecek tarih personelin çıkış tarihinden büyük ise */
			$gunSayisi =  0;
		}else if( $istenCikisTarihi != "" AND date( "Y-m",strtotime( $istenCikisTarihi ) ) <= $listelenecekAy ){
			/*Personel Çıkış Yapmış ve listelecek tarih personelin çıkış tarihinden büyük ise */
			$gunSayisi = 0;	
		}else if( $listelenecekAy > date( "Y-m" ) ){
			/*Suanki tarih listelecen aydan daha */
			$gunSayisi = 0;	
		}else if( $listelenecekAy == date( "Y-m" ) ){
			/*Suanki tarih listelecen aydan daha */
			$gunSayisi = date( "d" );	
		}else{
			$gunSayisi = date("t",mktime(0,0,0,$ay,01,$yil));	
		}

		return intval($gunSayisi);
	}

}
