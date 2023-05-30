<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$SQL_aktif_is = <<<SQL
SELECT
	id
FROM
	sayac_isler
WHERE
	aktif = 1 AND bitis_tarihi IS NULL
LIMIT 1
SQL;


$SQL_sayac_cihaz = <<<SQL
SELECT
	id
FROM
	sayac_sayac_cihazlari
WHERE
	sayac_mac = ?
SQL;


$SQL_makina = <<<SQL
SELECT
	 id
	,personel_id
	,is_parca_id
FROM
	sayac_makina
WHERE
	sayac_cihaz_id = ?
SQL;


$SQL_log_ekle = <<<SQL
INSERT INTO
	sayac_is_loglari_gunluk
SET
	 is_id				= ?
	,personel_id		= ?
	,makina_id			= ?
	,is_parca_id		= ?
	,tarih				= now()
SQL;


$SQL_sayac_cihaz_en_son_tamamlanan_kesim_sayisi = <<<SQL
SELECT
	FLOOR( COUNT(sc.id) / m.is_basina_sayac_sayisi ) AS tamamlanan
FROM
	sayac_is_loglari_gunluk AS ilg
LEFT JOIN
	sayac_makina AS m ON ilg.makina_id = m.id
LEFT JOIN
	sayac_sayac_cihazlari AS sc ON m.sayac_cihaz_id = sc.id
LEFT JOIN
	sayac_isler AS si ON ilg.is_id = si.id
WHERE
	sc.sayac_mac = ? AND si.aktif = 1
SQL;


$sayac_mac				= array_key_exists( 'sayac_mac', $_REQUEST ) ? $_REQUEST[ 'sayac_mac' ] : "";
$kesim_sayisi			= array_key_exists( 'kesim_sayisi', $_REQUEST ) ? $_REQUEST[ 'kesim_sayisi' ] : 0;
$ilk_defa_calisma		= array_key_exists( 'ilk_defa_calisma', $_REQUEST ) ? $_REQUEST[ 'ilk_defa_calisma' ] * 1 : 0;


/* Geçerli bir mac adresi geldiyse işlem yap*/
if ( strlen( $sayac_mac ) > 0 ) {
	
	if( $ilk_defa_calisma == 1 ) {
		$kesim_sayisi_sonuc = $vt->select( $SQL_sayac_cihaz_en_son_tamamlanan_kesim_sayisi, array( $sayac_mac ) );
		$toplam_kesim_sayisi = $kesim_sayisi_sonuc[ 2 ][ 0 ][ "tamamlanan" ]; 
		echo $toplam_kesim_sayisi;
	} else {
		/* Anlık bilgi gönderen cihazın idsini bul */
		$cihaz			= $vt->select( $SQL_sayac_cihaz, array( $sayac_mac ) );
		$sayac_cihaz_id	= $cihaz[ 2 ][ 0 ][ "id" ];

		/* Aktif olan işin bilgileri */
		$aktif_is	= $vt->select( $SQL_aktif_is );
		$is_id		= $aktif_is[ 2 ][ 0 ][ "id" ];

		/* Makina bilgileri */
		$makina			= $vt->select( $SQL_makina, array( $sayac_cihaz_id ) );
		$makina_id		= $makina[ 2 ][ 0 ][ "id" ]; 
		$personel_id	= $makina[ 2 ][ 0 ][ "personel_id" ]; 
		$is_parca_id	= $makina[ 2 ][ 0 ][ "is_parca_id" ];

		for($i=0;$i<$kesim_sayisi;$i++){
			$sorgu_sonuc = $vt->insert( $SQL_log_ekle, array(
				$is_id
				,$personel_id
				,$makina_id
				,$is_parca_id
			) );
		}
		//echo date("H:i:s", time());
		$kesim_sayisi_sonuc = $vt->select( $SQL_sayac_cihaz_en_son_tamamlanan_kesim_sayisi, array( $sayac_mac ) );
		$toplam_kesim_sayisi = $kesim_sayisi_sonuc[ 2 ][ 0 ][ "tamamlanan" ]; 
		echo $toplam_kesim_sayisi;
	}
}

?>