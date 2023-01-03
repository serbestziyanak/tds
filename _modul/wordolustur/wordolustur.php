<?php
include "../../_cekirdek/fonksiyonlar.php";
include "../../_cekirdek/word.class.php";

$htd 	= new HTML_TO_DOC();
$fn		= new Fonksiyonlar();
$vt 	= new VeriTabani();

$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';

//Gelen Personele Ait Bilgiler
$SQL_tek_personel_oku = <<< SQL
SELECT
	*,
	CONCAT(adi, ' ', soyadi) AS adsoyad
FROM
	tb_personel
WHERE
	id = ? AND firma_id =? AND aktif = 1
SQL;

//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_personel_gun_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	personel_id = ? AND tarih = ? 
SQL;

$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id,$_SESSION['firma_id']) )[2];
$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id,$tarih) )[2];

if(count($personel)<1){
	echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
			<h5><i class="icon fas fa-ban"></i> Hata!</h5>
			aHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
		</div>';
	die();
}
if ( $_REQUEST['tip'] == "gunluk" ) {
	if(count($giriscikis)>0){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}
}else{
	if(count($giriscikis)<1){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}
}


//saatin sonuna de veya da bırakacağımızı belirliyoruz
$saat_son_rakam = substr($_REQUEST['saat'], -1);
$ek = "de";
if ($saat_son_rakam == 0 or $saat_son_rakam == 6 or $saat_son_rakam == 9 ) {
	$ek = "da";
}

if ( $_REQUEST['tip'] == "gunluk" ) {
	$text = 'Yukarıda Unvanı adresi yazılı işyerimizde çalışan '.$personel[0]["tc_no"].' T.C. kimlik numaralı '.$personel[0]["adsoyad"].' isimli işçimiz '.date("d.m.Y", strtotime($tarih)).' tarihinde iznimiz ve bilgimiz dışında mazeretsiz olarak mesaisine gelmemiştir';
}else if ( $_REQUEST['tip'] == "gecgelme" ){
	$text = 'Yukarıda Unvanı adresi yazılı işyerimizde çalışan '. $personel[0]["tc_no"].' T.C. kimlik numaralı '. $personel[0]["adsoyad"].' isimli işçimiz '. date("d.m.Y", strtotime($tarih)).' tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat '. $_REQUEST["saat"]."'".$ek.' mesaisine gelmiştir';
}else{
	$text = 'Yukarıda Unvanı adresi yazılı işyerimizde çalışan '. $personel[0]["tc_no"].' T.C. kimlik numaralı '. $personel[0]["adsoyad"].' isimli işçimiz '. date("d.m.Y", strtotime($tarih)).' tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat '. $_REQUEST["saat"]."'".$ek.' iş yerimizden ayrılmıştır';
}




$htmlContent = ' 
    <p style="    
    	display: block;
    	font-weight: bold;
    	text-align: center;
    	font-size: 21;">GÜNLÜK DEVAMSIZLIK<p>

    <br><br>

    <p 	style="    
    	display: block;
    	font-size:19px;
    	font-weight:bold;
    	text-align: center;">Tutanaktır</p>
    <p  style="
    	text-decoration: underline;
	    font-weight: bold;
	    font-size: 17px;
    	">İŞVERENİN</p>
    <table border="0" style="width: 100%;height: 150px;">
    	<tr>
    		<td style="padding:10px 0px;">Ünvanı</td>
    		<td width="10px">:</td>
    		<td>TUŞBA KONUT İNŞ.TUR.SAN. VE TİC.LTD.ŞTİ.</td>
    	</tr>
    	<tr>
    		<td style="padding:10px 0px;">Adresi</td>
    		<td width="10px">:</td>
    		<td>Şemsibey Osb Mahallesi Ahtamar Caddesi No:17. Tuşba / VAN</td>
    	</tr>
    	<tr>
    		<td style="padding:10px 0px;">SGK İşyeri Sicil Numarası</td>
    		<td width="10px">:</td>
    		<td>2 1413 01 01 1045408 065 14 32 000 </td>
    	</tr>
    	
    </table>
    <br>
    <p>&#9;'.$text.'.</p></pre>
    <p>&#9;İşbu tutanak '.date("d.m.Y").' tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve
müştereken imza altına alınmıştır.</p>';


$htd->createDoc($htmlContent, "belge", 1);
