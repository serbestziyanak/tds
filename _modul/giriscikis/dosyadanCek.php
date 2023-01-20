<?php 
	
	include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
error_reporting( E_ALL );


$dizin1 =  "../../dosyadanCek/".$_SESSION["firma_id"];
$dizin =  "../../dosyadanCek/".$_SESSION["firma_id"]."/".date("dmY");
//personel id sine göre klasor oluşturulmu diye kontrol edip yok ise klador oluşturuyoruz
if (!is_dir($dizin1)) {
	if(!mkdir($dizin, '0777', true)){
		$sonuc["sonuc"] = "hata - 2";
	}else{	
		chmod($dizin, 0777);
	}
}

if (!is_dir($dizin)) {
	if(!mkdir($dizin, '0777', true)){
		$sonuc["sonuc"] = "hata - 2";
	}else{	
		chmod($dizin, 0777);
	}
}
if( isset( $_FILES[ "file"]["tmp_name"] ) and $_FILES[ "file"][ 'size' ] > 0 ) {
	$firma	= uniqid() ."." . pathinfo( $_FILES[ "file"][ 'name' ], PATHINFO_EXTENSION );
	$hedef_yol		= $dizin.'/'.$firma;
	move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ], $hedef_yol );
}else{
	die("Dosya Yüklenmedi");
}

$SQL_giris_cikis_kaydet = <<< SQL
INSERT INTO
	tb_giris_cikis
SET
	personel_id		= ?,
	tarih			= ?,
	baslangic_saat	= ?
SQL;

//Personel Olup Olmadıgını kontrol etme 
$SQL_personel_oku = <<< SQL
SELECT
	p.id,
	p.grup_id
FROM
	tb_personel AS p
WHERE
	p.firma_id 	= ? AND 
	p.kayit_no 	= ? AND
	p.aktif 	= 1
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

$SQL_bitis_saat_guncelle = <<< SQL
UPDATE tb_giris_cikis
SET 
	bitis_saat 	= ?
WHERE
	id 			= ?  
SQL;

/*Genel Ayarlar*/
$SQL_genel_ayarlar = <<< SQL
SELECT 
	*
FROM 
	tb_genel_ayarlar
WHERE 
	firma_id 	= ?
SQL;

$genel_ayarlar				= $vt->select( $SQL_genel_ayarlar, array( $_SESSION["firma_id"] ) )[ 2 ];

$tatil_mesai_carpan_id 		= $genel_ayarlar[ 0 ][ "tatil_mesai_carpan_id" ];
$normal_carpan_id 			= $genel_ayarlar[ 0 ][ "normal_carpan_id" ];


$_SESSION[ "bosOlanKayitNumalarari" ] = array();
$Dosya = fopen( $hedef_yol, "r" ) or exit( "Dosya Açılamadı !" );

while( !feof( $Dosya ) )
{
	$satir 		= fgets( $Dosya );
	$satir_bol 	= explode( ",", $satir );
	$dizi_test[]=$satir_bol;
}
fclose($Dosya);
$vt->islemBaslat();
foreach($dizi_test as $alt_dizi){
	$bosTemizle = array_filter($alt_dizi);

	if( count( $bosTemizle ) > 0 ){

		$tarih_bol  = explode( " ", $alt_dizi[3] );
		$tarih 		= str_replace(".","-", $tarih_bol[0]  );
		$saat 		= $tarih_bol[1];
		$personel_kayit_numarasi = intval( $alt_dizi[1] ); 

		$time_input = strtotime($tarih); 
		$date_input = getDate($time_input);    

		$tarihAl 	= $date_input["year"]."-".$date_input["mon"];
		$sayi 		= $date_input["mday"];

		//Gelen kayıt numarasına göre personelli çağırıyoruz
		$personel_varmi = $vt->select( $SQL_personel_oku, array($_SESSION['firma_id'], $personel_kayit_numarasi ) ) [2];

		//Personel Varsa işlmelere devam ediliyor
		if ( count( $personel_varmi ) > 0 ){
			
			//Personel giriş yapıp cıkış yapmadığını kontrol ediyoruz
			$girisvarmi  = $vt->select($SQL_personel_gun_cikis, array( $personel_varmi[ 0 ][ 'id' ], $tarih ))[ 2 ];
			
			if ( count( $girisvarmi ) > 0 ){
				$update = $vt->update($SQL_bitis_saat_guncelle, array( $saat, $girisvarmi[0][ 'id' ] ));
				$hesapla 	= $fn->puantajHesapla(  $personel_varmi[ 0 ][ 'id' ], $tarihAl, $sayi, $personel_varmi[0][ 'grup_id' ], array(), $tatil_mesai_carpan_id, $normal_carpan_id );
				/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
				$sonuc = $fn->puantajKaydet( $personel_varmi[ 0 ][ 'id' ], $tarihAl ,$sayi, $hesapla);
			}else{
				$ekle = $vt->insert( $SQL_giris_cikis_kaydet, array( $personel_varmi[ 0 ][ 'id' ], $tarih, $saat ) );
			}
		}else{
			if( !in_array( $personel_kayit_numarasi, $_SESSION[ "bosOlanKayitNumalarari" ] ) ){
				$_SESSION[ "bosOlanKayitNumalarari" ][ $personel_kayit_numarasi ] = $personel_kayit_numarasi;
			}
		}
	}
}
if( count( $_SESSION[ "bosOlanKayitNumalarari" ] ) ){
	$mesaj = "Dosya Yazılmadı Eklenmesi gereken personel mevcut personel eklendikten sonra tekrar deneyiniz<br>
	Personel Kayıt Numaraları Asağıdadır.<br>";
	foreach ($_SESSION[ "bosOlanKayitNumalarari" ] as  $numara) {
		$mesaj .= "<h4>$numara</h4>";
	}
	unlink($hedef_yol);
}else{
	$mesaj = 'Dosya Okuma Başarılı Veriler Eklendi';
}
$_SESSION[ 'anasayfa_durum' ] = "guncelle";
$vt->islemKontrol();
?>

<html>
	<head>
		<title>

		</title>
	</head>
	<body>
		<div class="mesaj" style='width: 650px; margin: auto; text-align: center;font-size: 21px;'><?php echo $mesaj ?></div>
	</body>
</html>