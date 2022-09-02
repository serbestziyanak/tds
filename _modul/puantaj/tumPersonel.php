<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'personel_id' ]				= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$islem			= array_key_exists( 'islem'		,$_REQUEST ) 		? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id		= array_key_exists( 'personel_id'	,$_REQUEST ) 		? $_REQUEST[ 'personel_id' ]		: 0;
$detay			= array_key_exists( 'detay'		,$_REQUEST ) 		? $_REQUEST[ 'detay' ]			: null;
//Personele Ait Listelenecek Hareket Ay
@$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) 			? $_REQUEST[ 'tarih' ]			: date("Y-m");
 
$tarih = $listelenecekAy;

$tarihBol = explode("-", $tarih);
$ay = intval($tarihBol[1] );
$yil = $tarihBol[0];

$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi			= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls			= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'		: 'btn btn-success btn-sm pull-right';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
	,ok1.adi AS ozel_kod1
	,ok2.adi AS ozel_kod2
	,u.adi AS uyruk_adi
	,od.adi AS ogrenim_duzeyi_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN tb_ozel_kod AS ok1 ON p.ozel_kod1_id = ok1.id
LEFT JOIN tb_ozel_kod AS ok2 ON p.ozel_kod2_id = ok2.id
LEFT JOIN tb_ulkeler AS u ON p.uyruk_id = u.id
LEFT JOIN tb_ogrenim_duzeyleri AS od ON p.ogrenim_duzeyi_id = od.id
WHERE
	p.id = ? AND p.aktif = 1
SQL;

//belirli bir aya göre personelin giriş çıkış hareketleri
//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	id
	,tarih
	,COUNT(tarih) AS tarihSayisi
	
FROM
	tb_giris_cikis
WHERE
	baslangic_saat  IS NOT NULL AND 
	personel_id 				= ? AND 
	DATE_FORMAT(tarih,'%Y-%m') 	=?  AND 
	aktif 					= 1
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
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
	tarih 		=? AND 
	aktif 		= 1
ORDER BY baslangic_saat ASC 
SQL;


//FirmanınSectiği Giriş Cıkış Tipleri
$SQL_firma_giris_cikis_tipi = <<< SQL
SELECT
	 tip.id
	,tipler.adi
	,maas_kesintisi
FROM
	tb_giris_cikis_tipi AS tip
INNER JOIN tb_giris_cikis_tipleri AS tipler ON tip.tip_id = tipler.id
WHERE 
	tip.firma_id = ?
ORDER BY tipler.adi ASC
SQL;

//Tüm Giriş Çıkış Tipleri
$SQL_tum_giris_cikis_tipleri = <<< SQL
SELECT
tb_giris_cikis_tipleri.id,
tb_giris_cikis_tipleri.adi,
(SELECT tip_id from tb_giris_cikis_tipi WHERE tb_giris_cikis_tipi.tip_id = tb_giris_cikis_tipleri.id AND firma_id = 2) AS varmi
FROM
	tb_giris_cikis_tipleri
ORDER BY adi ASC
SQL;

//BELİRTİLEN TARİHLER ARASI EN YÜKSEK CARPANLI TARİFE 
$SQL_giris_cikis_saat = <<< SQL
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

//TARİFEYE AİT SAAT LİSTESİ
$SQL_tarife_saati = <<< SQL
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
$SQL_mola_saati = <<< SQL
SELECT 
	*
from
	tb_molalar
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//TÜM ÇARPANLARIN LİSTESİ
$SQL_carpan_oku = <<< SQL
SELECT 
	tb_tarife_saati.* 
FROM 
	tb_tarife_saati
INNER JOIN tb_tarifeler ON tb_tarifeler.id = tb_tarife_saati.tarife_id
WHERE 
	firma_id = ?
GROUP BY carpan
ORDER BY carpan ASC
SQL;

/*AVANS KAZANÇ KESİNTİ TOPLAM TUTARI GETİRME*/
$SQL_toplam_avans_kesinti = <<< SQL
SELECT 
	SUM(tutar) AS toplamTutar
FROM 
	tb_avans_kesinti AS a
INNER JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
WHERE 
	DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND 
	a.personel_id 						= ? AND
	t.maas_kesintisi 					= ? AND 
	a.aktif 							= 1 
SQL;

/*Donem Kontrolu Kapatılıp Kapatılmadığı kontrol edilecek*/
$SQL_donum_oku = <<< SQL
SELECT 
	*
FROM 
	tb_donem
WHERE 
	firma_id 	= ?  AND 
	yil 		= ? AND
	ay 		= ? AND 
	aktif 	= 1 
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

/*Personel Maaş*/
$SQL_personel_maas = <<< SQL
SELECT
	tb_kapatilan_maas.maas
FROM
	tb_giris_cikis
INNER JOIN tb_kapatilan_maas ON tb_kapatilan_maas.id = tb_giris_cikis.maas
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') = ?  AND tb_giris_cikis.aktif = 1
GROUP BY tb_giris_cikis.maas
LIMIT 1
SQL;

/*Firmaya Ait Kullanılan Çarpan Listelerini Cektik*/
$carpan_listesi = $vt->select( $SQL_carpan_oku, array($_SESSION["firma_id"] ) )[ 2 ];

$sorgu = "";
foreach ( $carpan_listesi as $carpan ) {
	$sorgu .= 'sum(JSON_EXTRACT(calisma, \'$."'.$carpan[ "carpan" ].'"\')) AS "'.$carpan[ "carpan" ].'",';
}

/*Personelin Aylık Puantaj Hesaplaması */
$SQL_puantaj_aylik = 
"SELECT
	p.*,
	g.adi AS grup_adi,
	".$sorgu."
	sum(IF( tatil < 1, toplam_kesinti , null) ) AS toplam_kesinti,
	sum(IF( tatil = 1, IF( maasa_etki_edilsin = 1 , toplam_kesinti ,  0 )   , null) ) AS tatilGun,
	count(IF( tatil = 1, IF( maasa_etki_edilsin = 1 , toplam_kesinti ,  0 )   , null) ) AS tatilSayisi,
	(SELECT 
		tb_kapatilan_maas.maas
	from 
		tb_giris_cikis 
	RIGHT JOIN 
		tb_kapatilan_maas ON tb_kapatilan_maas.id = tb_giris_cikis.maas
	WHERE 
		tb_giris_cikis.personel_id = tb_puantaj.personel_id 
	GROUP BY tb_giris_cikis.personel_id
	LIMIT 1) AS kapali_maas,
	SUM(ucretli_izin) AS ucretli_izin,
	SUM(ucretsiz_izin) AS ucretsiz_izin,
	(
		SELECT 
			SUM(tutar) 
		FROM 
			tb_avans_kesinti AS a
		LEFT JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
		WHERE
			DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND
			a.personel_id 						= tb_puantaj.personel_id AND
			t.maas_kesintisi 					= 0 AND 
			a.aktif 							= 1 
	) AS kazanc,
	(
		SELECT 
			SUM(tutar)
		FROM 
			tb_avans_kesinti AS a
		LEFT JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
		WHERE 
			DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND 
			a.personel_id 						= tb_puantaj.personel_id AND
			t.maas_kesintisi 					= 1 AND 
			a.aktif 							= 1 
	) AS kesinti

FROM 
	tb_puantaj 
LEFT JOIN tb_personel AS p ON p.id = tb_puantaj.personel_id
LEFT JOIN tb_gruplar  AS g ON g.id = p.grup_id
where 
	p.firma_id = ? AND
	DATE_FORMAT(tb_puantaj.tarih,'%Y-%m') = ? AND
	p.aktif = 1
GROUP BY p.id";


$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id'] ) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];

$donem					= $vt->select( $SQL_donum_oku, array( $_SESSION["firma_id"], $yil,$ay ) )[ 3 ];

if ( $donem > 0 ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php?modul=kapatilmisDonem&personel_id='.$personel_id.'&tarih='.$tarih.'">';
	die();
}

if ( $detay == "tumPersonel" ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php?modul=tumPersonel&tarih='.$tarih.'">';
	die();
}

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id'] ) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];
$personel					= $vt->select( $SQL_tek_personel_oku, array($personel_id) )[ 2 ][ 0 ];
$carpan_listesi			= $vt->select( $SQL_carpan_oku, array($_SESSION["firma_id"] ) )[ 2 ];
$genel_ayarlar				= $vt->select( $SQL_genel_ayarlar, array( $_SESSION["firma_id"] ) )[ 2 ];


$kazanilan 				= $vt->select( $SQL_toplam_avans_kesinti, array( $listelenecekAy, $personel_id, 0 ) ) [ 2 ][ 0 ];
$kesinti 					= $vt->select( $SQL_toplam_avans_kesinti, array( $listelenecekAy, $personel_id, 1 ) ) [ 2 ][ 0 ];

//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
foreach($giris_cikislar AS $giriscikis){
	$tarihSayisi[] = $giriscikis["tarihSayisi"]; 
}

@$tarihSayisi = max($tarihSayisi); 

$aylik_calisma_saati		= $genel_ayarlar[ 0 ][ 'aylik_calisma_saati' ];
$pazar_kesinti_sayisi		= $genel_ayarlar[ 0 ][ 'pazar_kesinti_sayisi' ];
$personel_maas 			= $tek_personel[ 'ucret' ];
$beyaz_yakali_personel 		= $genel_ayarlar[ 0 ][ "beyaz_yakali_personel" ];

?>


<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<a class="btn btn-outline-warning btn-lg col-xs-6 col-sm-2 float-left" href="?modul=puantaj&amp;tarih=<?php echo $tarih; ?>">Tek Personele Ait Veriler</a>

				<div class="col-sm-2" style="float: right;display: flex;">
					<div class="">
						<div class="input-group date" id="datetimepickerAy" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepickerAy" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepickerAy" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy) echo $listelenecekAy; ?>"/>
						</div>
					</div>
					<div style="float: right;display: flex;">
						<button class="btn btn-success" id="listeleBtn">listele</button>
					</div>
				</div>
				
				
			</div>
			
			<div class="col-12">
				<div class="card card-secondary"  id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Tüm Personele Ait Puantaj İşlemleri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>İsim Soyisim</th>
									<th>Tc Kimlik No</th>
									<th>Ücret</th>
									<th>Çalışma Günü</th>
									<th>Hakediş Günü</th>
									<?php 
										$ekle = count( $carpan_listesi );
										foreach ( $carpan_listesi as $carpan ) {
											echo '<th>"'.$carpan[ "carpan" ].'"<br>Çalışma Saati</th>';
											echo '<th>"'.$carpan[ "carpan" ].'"<br>Hak Ediş</th>';
										}
									?>
									<th>Hafta Tatili</th>
									<th>Ücretli İzin S.</th>
									<th>Ücretsiz İzin S.</th>
									<th>Toplam Kesinti Saati</th>
									<th>Toplam Kesinti Ücreti</th>
            							<th>Toplam Kazanç</th>
            							<th>Ek Kazanç</th>
            							<th>Kesintiler</th>
            							<th>Net Ücret</th>
            							<th>Borç Tutarı</th>
            							<th>İmza</th>
            							<th>Bodro Ayı</th>
            							<th>Sicil No</th>
            							<th>Grup Adı</th>
            							<th>İşe Giriş Tarihi</th>
            							<th>işten Çıkış Tarihi</th>
            							<th>Ücret</th>
            							<th>Şubesi</th>
            							<th>Bölümü</th>
            							<th>Servis</th>
            							<th>Özel Kod 1</th>
            							<th>Özel Kod 2</th>
            							<th>Uyruk</th>
            							<th>Cinsiyet</th>
            							<th>Baba Adı</th>
            							<th>Anne Adı</th>
            							<th>Doğum Yeri</th>
            							<th>Doğum Tarihi</th>
            							<th>Medeni Hali</th>
            							<th>Kan Grubu</th>
            							<th>Öğrenim Düzeyi</th>
            							<th>Sabit Telefon</th>
            							<th>Mobil Telefon</th>
            							<th>Sigorta Başlangıcı</th>
            							<th>Sigorta Sonu</th>
            							<th>Diğer Ödemeler</th>
            							<th>Günlük Ödeme</th>
            							<th>Aylık Ek Ödeme</th>
            							<th>Banka Şubesi</th>
            							<th>Banka Hesap No</th>
            							<th>Iban</th>
            							<th>İzin Başlama Tarihi</th>
            							<th>Kalan İzin</th>
            							<th>Ödenen İzin</th>
            							<th>Adres</th>
								</tr>
							</thead>
							<tbody>
								<?php

									$ay  	 = explode("-", $tarih);
									$yil       = $ay[ 0 ];
									$ay 		 = $ay[ 1 ];
									$gunSayisi = date("t",mktime(0,0,0,$ay,01,$yil));	

									$sayi =0;
									$personelPuantaji = $vt->select( $SQL_puantaj_aylik, array( $tarih, $tarih, $_SESSION[ 'firma_id' ], $tarih ) )[ 2 ];
									foreach ( $personelPuantaji as $puantaj ) {

										;

										$toplamKazanc = 0;
										$sayi++;

										$ucret = $puantaj[ "kapali_maas" ] == "" ? $puantaj["ucret"] : $puantaj[ "kapali_maas" ];

										$haftatatiliUcreti = ( $ucret / $aylik_calisma_saati / 60 ) * 1 * $puantaj[ "tatilGun" ];
								?>
										<tr>
											<td><?php echo $sayi; ?></td>
											<td><?php echo $puantaj["adi"].' '.$puantaj["soyadi"]; ?></td>
											<td><?php echo $puantaj[ "tc_no" ]; ?></td>
											<td><?php echo $fn->parabirimi($ucret); ?></td>
											<td><?php echo $gunSayisi; ?></td>
											<td><?php echo $kazanilmis; ?></td>
											<?php 
												
												foreach ( $carpan_listesi as $carpan ) {
													
													/* -- Maaş Hesaplasması == ( personelin aylık ucreti / 225 / 60 ) * carpan --*/
													
													
													if ( $puantaj[ $carpan[ "carpan" ] ] > 0 OR $beyaz_yakali_personel != $puantaj[ "grup_id" ] ){
														$kazanc 		 = ( $ucret / $aylik_calisma_saati / 60 ) * $carpan[ "carpan" ] * $puantaj[ $carpan[ "carpan" ] ];
                											$toplamKazanc  += $kazanc;

														echo '<td>'.$fn->dakikaSaatCevir($puantaj[ $carpan[ "carpan" ] ] ).'</td>';
														echo '<td>'.$fn->parabirimi( $kazanc ).'</td>';
													}else{
														echo	'<td>  </td>';
														echo	'<td>  </td>';
													}
												}
											?>
											<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? '' : $fn->dakikaSaatCevir( $puantaj[ "tatilGun" ] ); ?></td>
											<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? '' : $fn->dakikaSaatCevir( $puantaj[ "ucretli_izin" ] ); ?></td>
											<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? '' : $fn->dakikaSaatCevir( $puantaj[ "ucretsiz_izin" ] ); ?></td>
											<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? '' : $fn->dakikaSaatCevir( $puantaj[ "toplam_kesinti" ] ); ?></td>
											<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? '' :  $fn->parabirimi( ( $ucret / $aylik_calisma_saati / 60 ) * 1.00 * $puantaj[ "toplam_kesinti" ] ); ?></td>
		            							<td><?php echo $beyaz_yakali_personel == $puantaj[ "grup_id" ] ? $fn->parabirimi( $ucret) : $fn->parabirimi( $toplamKazanc + $haftatatiliUcreti ); ?></td>
		            							<td><?php echo $fn->parabirimi( $puantaj[ "kazanc" ] ); ?></td>
		            							<td><?php echo $fn->parabirimi( $puantaj[ "kesinti" ] ); ?></td>
		            							<td>
		            								<?php 
		            									if ( $beyaz_yakali_personel == $puantaj[ "grup_id" ] ){
														echo $fn->parabirimi( $ucret + $puantaj[ "kazanc" ] - $puantaj[ "kesinti" ] ); 
		            									}else{
		            										echo $fn->parabirimi( $toplamKazanc + $puantaj[ "kazanc" ] + $haftatatiliUcreti - $puantaj[ "kesinti" ] ); 
		            									}
		            								?>	
		            							</td>
		            							<td></td>
		            							<td></td>
		            							<td><?php echo $ay; ?></td>
		            							<td><?php echo $puantaj[ "sicil_no" ]; ?></td>
		            							<td><?php echo $puantaj[ "grup_adi" ]; ?></td>
		            							<td><?php echo $puantaj[ "ise_giris_tarihi" ]; ?></td>
		            							<td><?php echo $puantaj[ "isten_cikis_tarihi" ]; ?></td>
		            							<td><?php echo $ucret; ?></td>
		            							<td><?php echo $puantaj[ "sube_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "bolum_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "servis" ]; ?></td>
		            							<td><?php echo $puantaj[ "ozel_kod1_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "ozel_kod2_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "uyruk_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "cinsiyet" ]; ?></td>
		            							<td><?php echo $puantaj[ "baba_adi" ]; ?></td>
		            							<td><?php echo $puantaj[ "ana_adi" ]; ?></td>
		            							<td><?php echo $puantaj[ "dogum_yeri" ]; ?></td>
		            							<td><?php echo $puantaj[ "dogum_tarihi" ]; ?></td>
		            							<td><?php echo $puantaj[ "medeni_hali" ]; ?></td>
		            							<td><?php echo $puantaj[ "kan_grubu" ]; ?></td>
		            							<td><?php echo $puantaj[ "ogrenim_duzeyi_id" ]; ?></td>
		            							<td><?php echo $puantaj[ "sabit_telefon" ]; ?></td>
		            							<td><?php echo $puantaj[ "mobil_telefon" ]; ?></td>
		            							<td><?php echo $puantaj[ "sigarta_basi" ]; ?></td>
		            							<td><?php echo $puantaj[ "sigorta_sonu" ]; ?></td>
		            							<td><?php echo $puantaj[ "diger_odeme" ]; ?></td>
		            							<td><?php echo $puantaj[ "gunluk_odeme" ]; ?></td>
		            							<td><?php echo $puantaj[ "aylik_ek_odeme" ]; ?></td>
		            							<td><?php echo $puantaj[ "banka_sube" ]; ?></td>
		            							<td><?php echo $puantaj[ "banka_hesap_no" ]; ?></td>
		            							<td><?php echo $puantaj[ "iban" ]; ?> 		</td>
		            							<td><?php echo $puantaj[ "izin_baslama_tarihi" ]; ?></td>
		            							<td><?php echo $puantaj[ "kalan_izin" ]; ?></td>
		            							<td><?php echo $puantaj[ "odenen_izin" ]; ?></td>
		            							<td><?php echo $puantaj[ "adres" ]; ?></td>
										</tr>
								<?php } ?>
							</tbody>
							<tfoot>
					            	<tr>
					                	<th colspan="3" style="text-align:right">Total:</th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					                	<th></th>
					            	</tr>
					        </tfoot>
						</table>
					</div>
				</div>
			</div>	
		</div>
	</div>
</section>

<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/colreorder/1.5.6/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript">


	$(function () {
		$('#datetimepickerAy').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	function personelpuantaj(personel_id){
		var tarih 		= $("#tarihSec").val();
		var  url 			= window.location;
		var origin		= url.origin;
		var path			= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   		= search.get('modul');
		var personel_id  	= "&personel_id="+personel_id;
		
		window.location.replace(origin + path+'?modul='+modul+''+personel_id+'&tarih='+tarih);
	}	

	$(function () {
		$(":input").inputmask();

		//Initialize Select2 Elements
		$('.select2').select2()

		//Initialize Select2 Elements
		$('.select2bs4').select2({
		  theme: 'bootstrap4'
		})


		$("input[data-bootstrap-switch]").each(function(){
			$(this).bootstrapSwitch('state', $(this).prop('checked'));
		});
	})

	$("body").on('click', '#listeleBtn', function() {
		var tarih 		= $("#tarihSec").val();
		var  url 			= window.location;
		var origin		= url.origin;
		var path			= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   		= search.get('modul');
		var detay   		= search.get('detay');
		var personel_id   = search.get('personel_id');
		if(detay == null) {
			detay 	= ''; 
		}else{
			detay  	= "&detay="+detay;
		}
		if(personel_id == null) {
			personel_id 	= ''; 
		}else{
			personel_id  	= "&personel_id="+personel_id;
		}
		
		window.location.replace(origin + path+'?modul='+modul+''+personel_id+''+detay+'&tarih='+tarih);
	})


	var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable( {
		"responsive": false, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 50,
		"order" 	   : false,
		"scrollX"    : true,
		"colReorder" : true,
		"select"	   : true,
		buttons : [
			{
				extend	: 'colvis',
				text	: "Alan Seçiniz"
				
			},
			{
				extend	: 'excel',
				text 	: 'Excel',
				exportOptions: {
					columns: ':visible'
				},
				title: function(){
					return "Giriş Çıkış Bilgileri";
				}
			},
			{
				extend	: 'print',
				text	: 'Yazdır',
				exportOptions : {
					columns : ':visible'
				},
				title: function(){
					return "Giriş Çıkış Bilgileri";
				}
			}
		],
		"columnDefs": [
			{
				"targets" : [26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58],
				"visible" : false
			}
		],	
		"language": {
			"decimal"			: "",
			"emptyTable"		: "Gösterilecek kayıt yok!",
			"info"				: "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
			"infoEmpty"			: "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
			"infoFiltered"		: "",
			"infoPostFix"		: "",
			"thousands"			: ",",
			"lengthMenu"		: "Show _MENU_ entries",
			"loadingRecords"	: "Yükleniyor...",
			"processing"		: "İşleniyor...",
			"search"			: "Ara:",
			"zeroRecords"		: "Eşleşen kayıt bulunamadı!",
			"paginate"			: {
				"first"		: "İlk",
				"last"		: "Son",
				"next"		: "Sonraki",
				"previous"	: "Önceki"
			}
		},
		"footerCallback": function (row, data, start, end, display) {

              var api = this.api(), data;
              //  Hücrenin değerini Numbera çeviriyoruz.

              var intVal = function (i) {

                  return typeof i === 'string' ?

                      i.replace(',', '') * 1 :

                      typeof i === 'number' ?

                      i : i.replace('', '') * 1;
              };

			// Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(3).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(3).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(6).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(6).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(7).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(7).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(8).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(8).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(9).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(9).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(10).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(10).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(11).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(11).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(12).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(12).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(14).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(14).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(15).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(15).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(16).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(16).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(17).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(17).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(18).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(18).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(19).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(19).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(20).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(20).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(21).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(21).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               // Toplam Ucret Sutunu Topluyoruz
               toplamUcret = api.column(22).data().reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);
               $(api.column(22).footer()).html( toplamUcret.toFixed(2).replace('.', ',') );

               
               
          }
	} ).buttons().container().appendTo('#tbl_giriscikislar_wrapper .col-md-6:eq(0)');
	
</script>
