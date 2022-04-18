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

//Baslangıc ve Bitiş Tarihlerini Karşılastırıyoruz
$baslangicTarihi = new DateTime($_REQUEST["baslangicTarihSaat"]);
$fark = $baslangicTarihi->diff(new DateTime($_REQUEST["bitisTarihSaat"]));
$fark = $fark->days+1;

/* Alanları ve değerleri ayrı ayrı dizilere at. */
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' or  $alan == 'baslangicTarihSaat' or  $alan == 'bitisTarihSaat' or $alan == 'toplu' ) continue;

	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

if(array_key_exists("toplu", $_REQUEST)){
	$alanlar[] 		= "personel_id";
}

$baslangicSaat 	= explode(" ", $_REQUEST['baslangicTarihSaat']);
$bitisSaat 		= explode(" ", $_REQUEST['bitisTarihSaat']);

if($fark == 1){
	$alanlar[] 		="tarih";
	$alanlar[] 		="baslangic_saat";
	$alanlar[] 		="bitis_saat";

	$degerler[] 	= $baslangicSaat[0];
	$degerler[] 	= $baslangicSaat[1];
	$degerler[] 	= $bitisSaat[1];
}else{
	$alanlar[] 		="tarih";
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $personel_id;

$SQL_tum_personel_oku = <<< SQL
SELECT
	p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


$SQL_sil = <<< SQL
DELETE FROM
	tb_giris_cikis
WHERE
	id = ?
SQL;
$personeller 	= $vt->select($SQL_tum_personel_oku, array($_SESSION['firma_id']))[2];

$personel_id 	= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );


switch( $islem ) {
	case 'ekle':
		if(array_key_exists("toplu", $_REQUEST)){
			foreach ($personeller as $personel) {
				$i = 1;
				$degerler[] 			= $personel["id"];
				$tarih 					= $baslangicSaat[0];
				while ($i <= $fark) {
					$degerler[] 		= $tarih;
					$sonuc 				= $vt->insert( $SQL_ekle, $degerler );
					$tarih				= date('Y-m-d', strtotime($tarih . ' +1 day'));
					array_pop($degerler);
					$i++;
				}
				array_pop($degerler);
			}
		}else{
				$i = 1;
				while ($i <= $fark) {
					
					$degerler[] 		= $baslangicSaat[0];
					$sonuc = $vt->insert( $SQL_ekle, $degerler );
					$baslangicSaat[0] 	= date('Y-m-d', strtotime($baslangicSaat[0] . ' +1 day'));
					array_pop($degerler);
					$i++;
				}
		}

		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, $degerler );
		$resim_adi = "resim_yok.jpg";
		if( isset( $_FILES[ 'input_personel_resim' ] ) and $_FILES[ 'input_personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $personel_id . "." . pathinfo( $_FILES[ 'input_personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'input_personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_giris_cikis SET resim = ? WHERE id = ?', array( $resim_adi, $personel_id ) );
			}
		}
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$sonuc = $vt->delete( $SQL_sil, array( $giriscikis_id ) );
	break;
}
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=giriscikis&personel_id=".$personel_id );
?>