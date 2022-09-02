<?php
error_reporting( 0 );
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();


$SQL_giris_cikis_kaydet = <<< SQL
INSERT INTO
	tb_giris_cikis
SET
	personel_id	= ?,
	tarih		= ?,
	baslangic_saat	= ?
SQL;

/*tb_puantaj tablosuna veri ekleme*/
$SQL_puantaj_kaydet = <<< SQL
INSERT INTO
	tb_puantaj
SET
	personel_id			= ?,
	tarih				= ?,
	izin				= ?,
	calisma				= ?,
	hafta_tatili		= ?,
	ucretli_izin		= ?,
	ucretsiz_izin		= ?,
	toplam_kesinti		= ?,
	tatil				= ?,
	maasa_etki_edilsin	= ?
SQL;

/*Firmanın Tüm Personel Listesi*/
$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;

//Personel Olup Olmadıgını kontrol etme 
$SQL_personel_oku = <<< SQL
SELECT
	p.id,
	p.grup_id
FROM
	tb_personel AS p
WHERE
	p.firma_id = ? AND 
	p.kayit_no = ? AND
	p.aktif    = 1
SQL;

//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_personel_gun_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	personel_id 		   = ? AND 
	tarih 				   = ? AND 
	baslangic_saat IS NOT NULL AND 
	bitis_saat IS NULL AND
	aktif = 1
SQL;

//Giriş Çıkış id sine göre listeleme 
$SQL_puantaj_oku = <<< SQL
SELECT
	*
FROM
	tb_puantaj
WHERE
	personel_id 	= ? AND
	tarih 	   		= ?

SQL;

//Giriş Çıkış id sine göre listeleme 
$SQL_personel_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	id 		   = ? AND
	aktif 	   = 1
SQL;


$SQL_bitis_saat_guncelle = <<< SQL
UPDATE tb_giris_cikis
SET 
	bitis_saat 	= ?
WHERE
	id 			= ?  
SQL;

/*Puantaj Güncelleme İşlemi*/
$SQL_puantaj_guncelle = <<< SQL
UPDATE tb_puantaj
SET 
	personel_id			= ?,
	tarih				= ?,
	izin				= ?,
	calisma				= ?,
	hafta_tatili		= ?,
	ucretli_izin		= ?,
	ucretsiz_izin		= ?,
	toplam_kesinti		= ?,
	tatil				= ?,
	maasa_etki_edilsin	= ?
WHERE
	id 					= ?  
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id'] ) )[2];

$tarih = "2022-06";
$sayi  = 21;

foreach ($personeller as $personel) {
	
	while ( $sayi <= 30 ) {

	  	$hesapla 	= $fn->puantajHesapla( $personel[ "id" ],$tarih,$sayi,$personel[ "grup_id" ]);

		$calismasiGerekenToplamDakika  	= $hesapla["calismasiGerekenToplamDakika"];
		$calisilanToplamDakika 		 	= $hesapla["calisilanToplamDakika"];
		$kullanilmasiGerekenToplamMola 	= $hesapla["kullanilmasiGerekenToplamMola"];
		$ilkUygulanacakSaat 		 	= $hesapla["ilkUygulanacakSaat"];
		$tatil 							= $hesapla["tatil"] 			 == "hayir" ? 0 : 1;
		$maasa_etki_edilsin 			= $hesapla["maasa_etki_edilsin"] == "hayir" ? 0 : 1;
		$ucretli_izin 					= $hesapla["ucretli"];
		$ucretsiz_izin 					= $hesapla["ucretsiz"];

		$toplamIzın 					= $ucretli_izin + $ucretsiz_izin;
		$cikarilacakMola 				= $kullanilmasiGerekenToplamMola;

		$toplam_kesinti 				= $calismasiGerekenToplamDakika[$ilkUygulanacakSaat] - $calisilanToplamDakika[$ilkUygulanacakSaat] - $toplamIzın  - $cikarilacakMola;

		/*Hesaplama işleminin Veri Tabanına Kaydedilme İşlemi*/

		/*Oncelikle o günün veri tabanında kayıtlı lolup olmadığını kontrol ediyoruz kayıt var ise guncelleme yapılacak yok ise eklemesi yapılacak*/
		$puantaj_varmi		= $vt->select( $SQL_puantaj_oku, array($personel[ "id" ],$tarih."-".$sayi ) ) [2];
		$calisma 			= json_encode($calisilanToplamDakika);

		$veriler = array(
			$personel[ "id" ],
			$tarih."-".$sayi,
			$izin,
			$calisma,
			$hafta_tatili,
			$ucretli_izin,
			$ucretsiz_izin,
			$toplam_kesinti, 
			$tatil,
			$maasa_etki_edilsin
		);

		if( count($puantaj_varmi) > 0 ){
			array_push( $veriler, $puantaj_varmi[ 0 ][ 'id' ] );
			$vt->update($SQL_puantaj_guncelle, $veriler );
		}else{
			/*Yeni puantaj ekelenecek*/
			
			$vt->insert( $SQL_puantaj_kaydet, $veriler );

		}

		$sayi++;
	}

	$sayi = 21;
}

  	


?>