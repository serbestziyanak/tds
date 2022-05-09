<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$giriscikis_id	= array_key_exists( 'giriscikis_id', $_REQUEST )		? $_REQUEST[ 'giriscikis_id' ]	: 0;
$alanlar		= array();
$degerler		= array();


$SQL_ekle		= "INSERT INTO tb_giris_cikis SET ";
$SQL_guncelle 	= "UPDATE tb_giris_cikis SET ";

$SQL_tum_personel_oku = <<< SQL
SELECT
	p.*
FROM
	tb_personel AS p
WHERE
	p.firma_id = ? AND 
	p.aktif = 1
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
$SQL_personel_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	id 		   = ? AND
	aktif 	   = 1
SQL;


//Giriş çıkış idsine ve personel idsine göre veri olup olmadığını kontrol etme
$SQL_giris_cikis_oku = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	id 		   	= ? AND 
	personel_id = ? AND
	aktif 		= 1 
SQL;

$SQL_sil = <<< SQL
UPDATE tb_giris_cikis
SET 
	aktif 				 = 0,
	kaydi_silen_personel = ?
WHERE
	id 					 = ?  
SQL;


//Baslangıc ve Bitiş Tarihlerini Karşılastırıyoruz
@$baslangicTarihi 	        = new DateTime($_REQUEST["baslangicTarihSaat"]);
@$ikiTarihArasindakFark 	= $baslangicTarihi->diff(new DateTime($_REQUEST["bitisTarihSaat"]));
@$ikiTarihArasindakFark 	= $ikiTarihArasindakFark->days+1;

/* Alanları ve değerleri ayrı ayrı dizilere at. */
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'baslangicTarihSaat' or  $alan == 'bitisTarihSaat' or $alan == 'toplu' ) continue;

	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

//başlangıc ve bitiş saatlerini aldık
@$baslangicSaat 		= explode(" ", $_REQUEST['baslangicTarihSaat']);
@$bitisSaat 			= explode(" ", $_REQUEST['bitisTarihSaat']);

if($ikiTarihArasindakFark == 1){
	$alanlar[] 			= "baslangic_saat";
	$alanlar[] 			= "bitis_saat";
	$alanlar[] 			= "tarih";

	$degerler[] 		= $baslangicSaat[1];
	$degerler[] 		= $bitisSaat[1];
	if(array_key_exists("toplu", $_REQUEST)){
		$alanlar[] 		= "personel_id";
	}
}else{
	//degerler sabit oldugu ıcın onceden aldık
	$alanlar[] 			= "baslangic_saat";
	$alanlar[] 			= "bitis_saat";
	$degerler[] 		= $baslangicSaat[1];
	$degerler[] 		= $bitisSaat[1];

	//Son iki alan degerleri değişiklik göstereceği için
	array_key_exists("toplu", $_REQUEST) ? $alanlar[] = "personel_id" : ''; // Toplu Ekleme Yapulıp Yapılmadığı Kontrol edilip Alan Ekliyoruz
	$alanlar[] 			= "tarih";
}

if ($islem == "saatguncelle") {
	$alanlar 			= array();
	$alanlar[] 			= 'islem_yapan_personel';
	$alanlar[] 			= 'baslangic_saat_guncellenen';
	$alanlar[] 			= 'bitis_saat_guncellenen';

	$degerler			= array();
	$degerler[]			= $_SESSION['kullanici_id'];
}

// print_r($alanlar);
// die();
//PErsonel Giriş Yapmış ise ama cıkış yapmamış ise personel_cikis_varmi verisi bize true doner
if($islem == "ekle"){
	$personel_cikis_varmi 	= $vt->select($SQL_personel_gun_cikis, array($_REQUEST['personel_id'],$baslangicSaat[0]))[2];

	if (count($personel_cikis_varmi) > 0){
		$alanlar 		= array();
		$alanlar[]   	= 'bitis_saat';
		$islem 			= "guncelle";

		$degerler 		= array();
		$degerler[]		= $baslangicSaat[1];
	}
}

$personeller 			= $vt->select($SQL_tum_personel_oku, array($_SESSION['firma_id']))[2];
$personel_id 			= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];


$___islem_sonuc  		= array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );

$SQL_ekle				.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 			.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle			.= " WHERE aktif = 1 AND id = ?";

//Saat güncellemesi yapılıp yapılmadıgını kontrol ediyoruz
if ( $islem == "saatguncelle" ) {
	$islem 				= "guncelle";
	$islem_turu 		= 'saat_guncelle'; //Hareketeler sayfasında duzenle butonuna tıklanarak guncelleme yapılmış ise yapılacak işlem
}else{
	$islem_turu 		= 'saat_ekle';
}


switch( $islem ) {
	case 'ekle':
		if(array_key_exists("toplu", $_REQUEST)){
			foreach ($personeller as $personel) {
				$i = 1;
				$tarih 					= $baslangicSaat[0];
				$degerler[] 			= $tarih;
				$degerler[] 			= $personel["id"];
				while ($i <= $ikiTarihArasindakFark) {
					$sonuc 				= $vt->insert( $SQL_ekle, $degerler );
					$tarih				= date('Y-m-d', strtotime($tarih . ' +1 day'));
					array_pop($degerler);
					array_pop($degerler);
					$degerler[] 		= $tarih;
					$degerler[] 		= $personel["id"];
					$i++;
				}
				array_pop($degerler);
				array_pop($degerler);
			}
		}else{
			$i = 1;
			$degerler[] 		= $baslangicSaat[0]; // Tarih Alanına Deger Atıyoruz

			while ($i <= $ikiTarihArasindakFark) {
				$sonuc 				= $vt->insert( $SQL_ekle, $degerler );
				$baslangicSaat[0] 	= date('Y-m-d', strtotime($baslangicSaat[0] . ' +1 day'));
				array_pop($degerler);
				$degerler[] 		= $baslangicSaat[0];
				$i++;
			}
		}

		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
	break;
	case 'guncelle':

		if ( $islem_turu == "saat_guncelle" ) {
			foreach ($_REQUEST["giriscikis_id"] as $alan => $deger) {
				//DEgısen girişe ait kayıtları getirip katşılaştırmasını yapıyoruz
				$giriscikis = $vt->select($SQL_personel_giris_cikis, array($_REQUEST["giriscikis_id"][$alan]))[2];
				
				$degerler[] = date( 'H:i', strtotime($giriscikis[0]["baslangic_saat"])) == $_REQUEST["baslangic_saat"][$alan] ? '' : $_REQUEST["baslangic_saat"][$alan]; 
				$degerler[] = date( 'H:i', strtotime($giriscikis[0]["bitis_saat"]))     == $_REQUEST["bitis_saat"][$alan] ?     '' : $_REQUEST["bitis_saat"][$alan]; 
				$degerler[] = $_REQUEST["giriscikis_id"][$alan];

				$sonuc = $vt->update( $SQL_guncelle, $degerler );

				array_pop($degerler); // Id yı array den  cıkardık
				array_pop($degerler); // bitis_saati array den cıkardık
				array_pop($degerler); // Baslangic_saati array den  cıkardık

			}	
		}else{
			$degerler[] = $personel_cikis_varmi[0]["id"];
			$sonuc = $vt->update( $SQL_guncelle, $degerler );
		}
		
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$giris_cikis_varmi = $vt->select( $SQL_giris_cikis_oku, array( $giriscikis_id, $personel_id ) );
		if ( count( $giris_cikis_varmi ) > 0 ) {
			$sonuc = $vt->delete( $SQL_sil, array( $_SESSION['kullanici_id'], $giriscikis_id ) );
		}
		
	break;
}
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=giriscikis&personel_id=".$personel_id );
?>