<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$giriscikis_id	= array_key_exists( 'giriscikis_id', $_REQUEST )		? $_REQUEST[ 'giriscikis_id' ]	: 0;
$alanlar		= array();
$degerler		= array();
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "giriscikis", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}


$SQL_ekle		= "INSERT INTO tb_giris_cikis SET ";
$SQL_guncelle 	= "UPDATE tb_giris_cikis SET ";

$SQL_tum_personel_oku = <<< SQL
SELECT
	p.id,
	p.grup_id
FROM
	tb_personel AS p
WHERE
	p.firma_id = ? AND 
	p.aktif = 1
SQL;

/*Tek Personele Ait Veriler*/
$SQL_tek_personel_oku = <<< SQL
SELECT
	id,
	grup_id
FROM
	tb_personel
WHERE
	id = ? AND 
	firma_id 	= ? AND 
	aktif 		= 1
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

//Tatil ve normal Mesai id Getirme
$SQL_genel_ayarlar = <<< SQL
SELECT
	tatil_mesai_carpan_id,
	normal_carpan_id
FROM
	tb_genel_ayarlar
WHERE 
	firma_id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE tb_giris_cikis
SET 
	aktif 				 = 0,
	kaydi_silen_personel = ?
WHERE
	id 					 = ?  
SQL;

$baslangic_yil 	= intval(date( "Y", strtotime( $_REQUEST["baslangicTarihSaat"] ) ));
$baslangic_ay  	= intval(date( "m", strtotime( $_REQUEST["baslangicTarihSaat"] ) ));

$bitis_yil 	= intval( date( "Y", strtotime( $_REQUEST["bitisTarihSaat"] ) ) );
$bitis_ay  	= intval( date( "m", strtotime( $_REQUEST["bitisTarihSaat"] ) ) );



@$donem_baslangic = $fn->donemKontrol( $baslangic_yil, $baslangic_ay );
@$donem_bitis = $fn->donemKontrol( $bitis_yil, $bitis_ay );

if( $donem_baslangic > 0 OR $donem_bitis > 0 ) {
	$___islem_sonuc 		= array( 'hata' => True, 'mesaj' => 'İşlem Yapmak istediğiniz dönem kapatılmış. Sistem Yöneticisi ile iletişime geçiniz.' );
	$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
	header( "Location:../../index.php?modul=giriscikis" );
	die();
}


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
	$alanlar[] 			= 'baslangic_saat';
	$alanlar[] 			= 'baslangic_saat_guncellenen';
	$alanlar[] 			= 'bitis_saat';
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


$genel_ayarlar 			= $vt->select( $SQL_genel_ayarlar, array($_SESSION['firma_id'] ) )[ 2 ][ 0 ];
$normal_carpan_id		= $genel_ayarlar[ "normal_carpan_id" ];
$tatil_mesai_carpan_id	= $genel_ayarlar[ "tatil_mesai_carpan_id" ];

$personeller 			= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id'] ) )[ 2 ];
$personel_id 			= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];
$tek_personel 			= $vt->select($SQL_tek_personel_oku, array( $personel_id, $_SESSION['firma_id'] ) )[ 2 ];

$___islem_sonuc  		= array( 'hata' => false, 'mesaj' => 'İşlem Başarılı');


if ( count( $tek_personel )   < 1  ) {

	$_SESSION[ 'sonuclar' ] = array( 'hata' => true, 'mesaj' => 'Hatalı İşlem Yapmaktasınız.', 'id' => 0 );
	$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
	header( "Location:../../index.php?modul=giriscikis&personel_id=".$personel_id );
	die();
}

$SQL_ekle				.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_ekle				.= ", grup_id = ? ";

$SQL_guncelle 			.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle			.= " WHERE aktif = 1 AND id = ?";

//Saat güncellemesi yapılıp yapılmadıgını kontrol ediyoruz
if ( $islem == "saatguncelle" ) {
	$islem 				= "guncelle";
	$islem_turu 		= 'saat_guncelle'; //Hareketeler sayfasında duzenle butonuna tıklanarak guncelleme yapılmış ise yapılacak işlem
}else{
	$islem_turu 		= 'saat_ekle';
}
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		if(array_key_exists("toplu", $_REQUEST)){
			foreach ($personeller as $personel) {
				$i = 1;
				$tarih 					= $baslangicSaat[0];
				$degerler[] 			= $personel["id"];
				$degerler[] 			= $tarih;
				$degerler[] 			= $personel["grup_id"];
				while ($i <= $ikiTarihArasindakFark) {
					$sonuc 				= $vt->insert( $SQL_ekle, $degerler );
					
					$tarihAl 	= date( "Y-m", strtotime( $tarih ) );
					$sayiAl 	= intval(date( "d", strtotime( $tarih ) ));

					$hesapla 	= $fn->puantajHesapla(  $personel["id"], $tarihAl, $sayiAl, $personel["grup_id"], array(), $tatil_mesai_carpan_id, $normal_carpan_id );

					/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
					$fn->puantajKaydet( $personel["id"],$tarihAl, $sayiAl, $hesapla);
					
					$tarih				= date( 'Y-m-d', strtotime($tarih . ' +1 day') );
					array_pop($degerler);
					array_pop($degerler);
					array_pop($degerler);
					$degerler[] 		= $personel["id"];
					$degerler[] 		= $tarih;
					$degerler[] 		= $personel["grup_id"];
					$i++;

				}
				array_pop($degerler);
				array_pop($degerler);
				array_pop($degerler);
			}
			$gelenTarih = $baslangicSaat[0];
		}else{
			$i = 1;
			$degerler[] 		= $baslangicSaat[0]; // Tarih Alanına Deger Atıyoruz
			$degerler[] 		= $tek_personel[ 0 ][ 'grup_id' ];

			while ($i <= $ikiTarihArasindakFark) {
				$sonuc 			= $vt->insert( $SQL_ekle, $degerler );

				/*Başlangıc ve bitiş saati var ise paketi alıp hesaplama işlemi yapılacak*/
				$tarihAl 	= date( "Y-m", strtotime( $gelenTarih ) );
				$sayi 		= date( "d", strtotime( $gelenTarih ) );

				$hesapla 	= $fn->puantajHesapla(  $personel_id, $tarihAl, $sayi, $tek_personel[ 0 ][ 'grup_id' ], array(), $tatil_mesai_carpan_id, $normal_carpan_id );

				/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
				$fn->puantajKaydet( $personel_id, $tarih ,$sayi, $hesapla);
				
				$baslangicSaat[0] 	= date('Y-m-d', strtotime($baslangicSaat[0] . ' +1 day'));

				array_pop($degerler);
				array_pop($degerler);
				$degerler[] 		= $baslangicSaat[0];
				$degerler[] 		= $tek_personel[ 0 ][ 'grup_id' ];
				$i++;
			}
			$gelenTarih = "$tarihAl-$sayi";

		}
		
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
	break;
	case 'guncelle':

		if ( $islem_turu == "saat_guncelle" ) {

			/*Guncellenecek giriş Çıkışın tarihini Alıyoruz*/
			$gelenTarih 	= $vt->select( $SQL_personel_giris_cikis, array( $_REQUEST[ "giriscikis_id" ][ 0 ] ) ) [ 2 ] [ 0 ] [ "tarih" ];

			foreach ($_REQUEST["giriscikis_id"] as $alan => $deger) {
				//DEgısen girişe ait kayıtları getirip katşılaştırmasını yapıyoruz

				/*alanlar =>  işlem yapan id, baslanngic saat, baslangic saat guncellenen bitis saat bitis saat guncellenen  */

				$giriscikis = $vt->select($SQL_personel_giris_cikis, array($_REQUEST["giriscikis_id"][$alan]))[2];

				/*Başlangıc DEgerini değiştirdik*/
				$degerler[] = date( 'H:i', strtotime( $_REQUEST["baslangic_saat"][$alan] )); 

				/*Başlangıc saatinin guncellenip guncellenmediğini kontrol ediyoruz */
				if ( date( 'H:i', strtotime($giriscikis[0]["baslangic_saat"])) == $_REQUEST["baslangic_saat"][$alan] ){

					if ($giriscikis[0]["baslangic_saat_guncellenen"] == '' OR $giriscikis[0]["baslangic_saat_guncellenen"] == '00:00:00' OR date( 'H:i', strtotime($giriscikis[0]["baslangic_saat_guncellenen"])) == $_REQUEST["baslangic_saat"][$alan] ) {
						$degerler[] = '';
					}else{
						$degerler[] = $giriscikis[0]["baslangic_saat_guncellenen"];
					}

				}else{
					if ($giriscikis[0]["baslangic_saat_guncellenen"] == '' OR $giriscikis[0]["baslangic_saat_guncellenen"] == '00:00:00' ) {
						$degerler[] = $giriscikis[0]["baslangic_saat"];
					}else{
						$degerler[] = '';
					}
				}
				/*Bitiş Saatinin degerini degiştirdik*/
				$degerler[] = date( 'H:i', strtotime($_REQUEST["bitis_saat"][$alan])); 

				/*Bitiş Saatinin guncellenip guncellenmediğini kontrol ediyoruz*/
				if ( date( 'H:i', strtotime($giriscikis[0]["bitis_saat"])) == $_REQUEST["bitis_saat"][$alan] ){

					if ($giriscikis[0]["bitis_saat_guncellenen"] == '' OR $giriscikis[0]["bitis_saat_guncellenen"] == '00:00:00' OR date( 'H:i', strtotime($giriscikis[0]["bitis_saat_guncellenen"])) == $_REQUEST["bitis_saat"][$alan] ) {
						$degerler[] = '';
					}else{
						$degerler[] = $giriscikis[0]["bitis_saat_guncellenen"];
					}

				}else{
					if ($giriscikis[0]["bitis_saat_guncellenen"] == '' OR $giriscikis[0]["bitis_saat_guncellenen"] == '00:00:00' ) {
						$degerler[] = $giriscikis[0]["bitis_saat"];
					}else{
						$degerler[] = '';
					}
				}
				
				$degerler[] = $_REQUEST["giriscikis_id"][$alan];

				$sonuc = $vt->update( $SQL_guncelle, $degerler );

				array_pop($degerler); // Id yı array den  cıkardık
				array_pop($degerler); // bitis_saat_guncellenen array den cıkardık
				array_pop($degerler); // bitis_saat array den  cıkardık
				array_pop($degerler); // Baslangic_saat_guncellenen array den  cıkardık
				array_pop($degerler); // Baslangic_saat array den  cıkardık

			}

			/*Puantaj Güncelleme İşlemi*/

			$_SESSION['anasayfa_durum'] = 'guncelle';

		}else{
			
			/*Hareket Ekleme İşlemi Yapılsıysa yapılacak işlem*/
			$gelenTarih = $personel_cikis_varmi[0][ "tarih" ];
			$degerler[] = $personel_cikis_varmi[0][ "id" ];
			$sonuc 		= $vt->update( $SQL_guncelle, $degerler );
			$_SESSION['anasayfa_durum'] = 'guncelle';

		}
		
		/*Başlangıc ve bitiş saati var ise paketi alıp hesaplama işlemi yapılacak*/
		$tarih 		= date( "Y-m", strtotime( $gelenTarih ) );
		$sayi 		= date( "d", strtotime( $gelenTarih ) );

		$hesapla 	= $fn->puantajHesapla(  $personel_id, $tarih, $sayi, $tek_personel[ 0 ][ 'grup_id' ],array(),$tatil_mesai_carpan_id,$normal_carpan_id);

		/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
		$fn->puantajKaydet( $personel_id, $tarih ,$sayi, $hesapla);

		
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$giris_cikis_varmi = $vt->select( $SQL_giris_cikis_oku, array( $giriscikis_id, $personel_id ) )[2];

		if ( count( $giris_cikis_varmi ) > 0 ) {
			$sonuc = $vt->delete( $SQL_sil, array( $_SESSION['kullanici_id'], $giriscikis_id ) );
			
			$gelenTarih = $giris_cikis_varmi[0][ 'tarih' ];

			/*Puantaj Tekrar Hesaplanacak*/
			$tarih 	= date('Y-m', strtotime( $giris_cikis_varmi[0][ 'tarih' ] ) );
			$sayi 	= date('d', strtotime( $giris_cikis_varmi[0][ 'tarih' ] ) );
			
			$hesapla 	= $fn->puantajHesapla(  $personel_id, $tarih, $sayi, $tek_personel[0][ 'grup_id' ],array(),$tatil_mesai_carpan_id,$normal_carpan_id);
			/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
			$fn->puantajKaydet( $personel_id, $tarih ,$sayi, $hesapla);
		}
		
	break;
}
$vt->islemBitir();


$tarih = "&tarih=".date('Y-m',strtotime($gelenTarih)); 

$_SESSION[ 'anasayfa_durum' ] = 'guncelle';
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=giriscikis&personel_id=".$personel_id.$tarih );
?>