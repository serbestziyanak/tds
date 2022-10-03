<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$tip_id			= $_REQUEST[ 'tip_id' ];
$alanlar		= array();
$degerler		= array();
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "giriscikis", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}
 
$SQL_ekle		= "INSERT INTO tb_giris_cikis_tipi SET ";
$SQL_guncelle 	= "UPDATE tb_giris_cikis_tipi SET ";


/* Alanları ve değerleri ayrı ayrı dizilere at. */
$alanlar[] 		= 'firma_id';
$degerler[] 	= $_SESSION['firma_id'];
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' ) continue;


		if ($alan == "tip_id" ) {
			$tipIdler[0] = $deger;
			$alanlar[1]	= $alan;
		}else{
			$tipIdler[1] = $deger;
			$alanlar[2]	= $alan;
		}
}

// print_r($tipIdler); //GELEN TİP IDLERİ LİSTELE
// die();
ksort($alanlar);
$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $id;

$SQL_tum_personel_oku = <<< SQL
SELECT
	p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


//Silinecek Giriş Tipi Personele Uygulanmış mı Kontrol SQL 
$SQL_tum_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis
INNER JOIN tb_giris_cikis_tipi ON tb_giris_cikis_tipi.id = tb_giris_cikis.islem_tipi
WHERE
	firma_id = ? AND islem_tipi = ? 
SQL;

//Tip Silme
$SQL_sil = <<< SQL
DELETE FROM
	tb_giris_cikis_tipi
WHERE
	id = ?
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );
$vt->islemBaslat();

switch( $islem ) {
	case 'ekle':
		foreach ($tipIdler[0] as $alan => $tipId) {
			$degerler[1] = $tipId; 
			if (!empty($tipIdler[1])) {
				// Kesinti yapılacak diye secenek seçilmiş mi diye kontrol ediliyor
				$degerler[2] = in_array($tipId, $tipIdler[1]) ?  1 : 0; 
			}else{
				$degerler[2] = 0;
			}
			
			ksort($degerler); // Dizi anahtarına göre sıraladık
			$sonuc = $vt->insert( $SQL_ekle, $degerler );
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
		$tip_uygulanmis_mi			= $vt->select( $SQL_tum_giris_cikis, array($_SESSION['firma_id'],$tip_id) )[2]; // Tip Uygulanmıs mı Personele

		if (count($tip_uygulanmis_mi)<1) {
			$sonuc = $vt->delete( $SQL_sil, array( $tip_id) );
		}

	break;
}
$vt->islemBitir();
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=giriscikis");
?>