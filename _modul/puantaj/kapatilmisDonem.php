<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'personel_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$islem			= array_key_exists( 'islem'		,$_REQUEST ) 		? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) 	? $_REQUEST[ 'personel_id' ]	: 0;
$detay			= array_key_exists( 'detay'		,$_REQUEST ) 		? $_REQUEST[ 'detay' ]			: null;
//Personele Ait Listelenecek Hareket Ay
@$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) 		? $_REQUEST[ 'tarih' ]			: date("Y-m");
 
$tarih = $listelenecekAy;

$tarihBol = explode("-", $tarih);
$ay = intval($tarihBol[1]);
$yil = $tarihBol[0];

$satir_renk					= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi			= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls			= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
	DATE_FORMAT(tarih,'%Y-%m') 	= ?  AND 
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
$SQL_kapatilan_carpan_oku = <<< SQL
SELECT 
	*
FROM 
	tb_kapatilan_carpanlar
WHERE 
	firma_id  	= ? AND
	yil 		= ? AND 
	ay 			= ? 
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
	tb_donem
WHERE 
	firma_id 	= ? AND 
	yil 		= ? AND
	ay 			= ? 
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

/*Kapatilmış Doneme Ait Personel Grup id*/
$SQL_personel_grup_id = <<< SQL
SELECT
	grup_id
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND
	tarih  		= ? AND
	aktif 		= 1
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];

$donem						= $vt->select( $SQL_donum_oku, array( $_SESSION["firma_id"], $yil,$ay ) )[ 2 ];

if ( count( $donem ) == 0 ) {
	echo '<meta http-equiv="refresh" content="0; url=index.php?modul=puantaj&personel_id='.$personel_id.'&tarih='.$tarih.'">';
	die();
}

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];
$firma_giris_cikis_tipleri	= $vt->select( $SQL_firma_giris_cikis_tipi,array($_SESSION["firma_id"]))[2];
$giris_cikislar				= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy) )[2];
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array($personel_id) )[ 2 ][ 0 ];
$carpan_listesi				= $vt->select( $SQL_kapatilan_carpan_oku, array($_SESSION["firma_id"], $yil, $ay) )[ 2 ];
$genel_ayarlar				= $vt->select( $SQL_genel_ayarlar, array( $_SESSION["firma_id"], $yil, $ay ) )[ 2 ];
$personel_ucret				= $vt->select( $SQL_personel_maas, array($personel_id,$listelenecekAy) )[2][ 0 ];
/*
Seçili ay için AVANS KESNİNTİ ÜZERİNDEN EKLENECEK ÖDEMELER VAR İSE ÜCRETE EKLEMESİ YAPILACAKTIR
MAAŞ KESİNTİ DEGERİ 0 OLURSA MAAŞA EKLEMESİ YAPILACAKTIR 1 OLMASI HALİNDE MAASTAN DÜŞÜM YAPILACAKTIR
*/
$kazanilan 					= $vt->select( $SQL_toplam_avans_kesinti, array( $listelenecekAy, $personel_id, 0 ) ) [ 2 ][ 0 ];
$kesinti 					= $vt->select( $SQL_toplam_avans_kesinti, array( $listelenecekAy, $personel_id, 1 ) ) [ 2 ][ 0 ];

//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
foreach($giris_cikislar AS $giriscikis){
	$tarihSayisi[] = $giriscikis["tarihSayisi"]; 
}

@$tarihSayisi = max($tarihSayisi); 

$aylik_calisma_saati		= $genel_ayarlar[ 0 ][ 'aylik_calisma_saati' ];
$pazar_kesinti_sayisi		= $genel_ayarlar[ 0 ][ 'pazar_kesinti_sayisi' ];
$beyaz_yakali_personel 		= $genel_ayarlar[ 0 ][ "beyaz_yakali_personel" ];
$tatil_mesai_carpan_id 		= $genel_ayarlar[ 0 ][ "tatil_mesai_carpan_id" ];
$normal_carpan_id 			= $genel_ayarlar[ 0 ][ "normal_carpan_id" ];

$gunluk_calisma_suresi 		= $genel_ayarlar[ 0 ][ "gunluk_calisma_suresi" ];
$yarim_gun_tatil_suresi 	= $genel_ayarlar[ 0 ][ "yarim_gun_tatil_suresi" ];
$personel_maas 				= $personel_ucret[ 'maas' ];

if ( $beyaz_yakali_personel == $tek_personel[ 'grup_id' ] ) {
	$beyaz_yakali = "evet";
}
//Carpanlarıın değerlerini kolaylıkla almak için id ile diziye atip carpan_fiyat[$carpan] carpanın degerini vermektedir. 
$carpan_fiyat = array();
foreach ($carpan_listesi as $carpan) {
	$carpan_fiyat[ $carpan[ "id" ] ] = $carpan["carpan"];
	
}
$carpanSayisi 	= count( $carpan_listesi );
$sutunSayisi 	= $carpanSayisi + ( 2 * $tarihSayisi ) + 6;
?>

<section class="content" modul="puantaj" yetki_islem="goruntule">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<div class="col-sm-2 float-left" >
					<div class="form-group">
						<select class="form-control select2 btn btn-lg" id="personelAra" name = "personel_id" onchange="personelpuantaj(this.value);">
							<?php foreach( $personeller as $personel ) { ?>
								<option value="<?php echo $personel[ 'id' ]; ?>" <?php if( $tek_personel[ 'id' ] == $personel[ 'id' ] ) echo 'selected'; ?>><?php echo $personel['adi'].' '.$personel['soyadi']; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
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
				<a modul="puantaj" yetki_islem="tum_personel_verileri" class="btn btn-outline-warning btn-lg col-xs-6 col-sm-2 float-right" href="?modul=puantaj&amp;detay=tumPersonel&amp;tarih=<?php echo $tarih; ?>">Tüm Personel Verileri</a>
				
			</div>
			
			<div class="col-12" modul="puantaj" yetki_islem="goruntule">
				<div class="card card-secondary" id = "card_giriscikislar">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel["adi"].' '.$tek_personel["soyadi"] ?> Puantaj İşlemleri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<div style="display:none" >
							<div id="ciktiUst">
								<div class="text-right col-sm-12 mb-4">
								  	<h5><?php echo $fn->ayAdiVer($ay, 0).'-'.$yil; ?></h5>
								</div>
								<div class="col-sm-4 float-left bilgiTablosu">
									 <table style="border-top:0 !important;" border="0" class="table table-sm" width="100%">
									     <tr>
									      	<td width="50%"> <b>Adı Soyadı</b> </td>
									      	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "adi" ].' '.$tek_personel[ "soyadi" ]; ?></td>
									    	</tr>
									    	<tr>
									      	<td width="50%"> <b>Şubesi</b> </td>
									      	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "sube_adi" ]; ?></td>
									    	</tr>
									    	<tr>
									      	<td width="50%"><b>Bölümü</b> </td>
									      	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "bolum_adi" ]; ?></td>
									    	</tr>
									    	<tr>
									      	<td width="50%"><b>Grubu</b></td>
									      	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "grup_adi" ]; ?></td>
									    	</tr>
									    	<tr>
									      	<td width="50%"><b>Sicil No</b></td>
									      	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "sicil_no" ]; ?></td>
									    	</tr>
									 </table>
								</div>
								<div class="col-sm-4 float-left bilgiTablosu">
									<table style="border-top:0 !important;" border="0" class="table  table-sm" width="100%">
									    	<tr>
									      	<td width="50%"><b>İşe Girişi</b></td>
									      	<td width="3px">:</td>
									      	<td><?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "ise_giris_tarihi" ] );  ?></td>
									    	</tr>
									    	<tr>
									      	<td width="50%"><b>İşten Çıkışı</b></td>
									      	<td width="3px">:</td>
									      	<td></td>
									    	</tr>
									</table>
								</div>
								<div class="col-sm-4 float-left bilgiTablosu">
									<table style="border-top:0 !important;" border="0" class="table  table-sm" width="100%">
									    	<tr>
									      	<td width="50%"><b>Kayıt No</b> </td>
									     	<td width="3px">:</td>
									      	<td><?php echo $tek_personel[ "kayit_no" ]; ?></td>
									    	</tr>
									</table>
								</div>	
							</div>
						</div>

						<div class="clearfix"></div>
						
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Tarih</th>
									<?php
										$i = 1;

										echo $tarihSayisi == 0 ? '<th>İlk Giriş</th><th>Son Çıkış</th>':'';
										while ($i <= $tarihSayisi) {
											
											$thBaslikilk = $i == 1 ? 'İlk Giriş' : 'Giriş';

											$thBaslikSon = $i == $tarihSayisi ? 'Son Çıkış' : 'Çıkış';

											echo '<th>'.$thBaslikilk.'</th><th>'.$thBaslikSon.'</th>';
											$i++;
										}
									?>
									<th>İzin</th>
									<?php 
										foreach ( $carpan_listesi as $carpan ) {
											echo '<th>'.$carpan[ "adi" ].'</th>';
										}
									?>
									<th>Genel ve Hafta Tatili</th>
									<th>Ücretli İzin S.</th>
									<th>Ücretsiz İzin S.</th>
									<th>Toplam Kesinti S.</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$gunSayisi = $fn->ikiHaneliVer($ay) == date("m") ? date("d") : date("t",mktime(0,0,0,$ay,01,$yil));	

									/*Günlerin Saymak için*/
									$sayi = 1; 
									$genelCalismaSuresiToplami 	= array();
									$tatilGunleriToplamDakika  	= 0;
									$normalGun  			  	= 0;
									$haftaTatili  			  	= 0;
									$genelTatil   			  	= 0;
									$ucretliIzinGun      	  	= 0;
									$ucretsizIzinGun		  	= 0;
									$mazeretsizGelmeme 	      	= 0;
									while( $sayi <= $gunSayisi ) { 

										/*Personel Hesaplaması yapılan gün personel işe alınmamış ise hesaplama yapılmayacak*/
										if( $tarih."-".$fn->ikiHaneliVer($sayi) < date("Y-m-d", strtotime( $tek_personel[ "ise_giris_tarihi" ] ) ) ){
											$saatlikHesapla 	= 1;
											$eksikGun 			+= 1;
											
											echo "<tr>";
											echo $fn->bosSatir($sayi,$sayi.'.'.$fn->ayAdiVer($ay,1).''.$fn->gunVer($tarih."-".$sayi),$sutunSayisi);
											echo "</tr>";
										}else{

											$grup_id				= $vt->select( $SQL_personel_grup_id, array($personel_id,$listelenecekAy."-".$sayi) )[2][ 0 ];
											$aciklama 				= '';

											$gunHesapla = $fn->puantajHesapla($personel_id,$tarih,$sayi,$grup_id,$genelCalismaSuresiToplami,$tatil_mesai_carpan_id,$normal_carpan_id,1);

											$KullanilanSaatler 			 	= $gunHesapla["KullanilanSaatler"];
											$kullanilacakMolalar 		 	= $gunHesapla["kullanilacakMolalar"];
											$saatSay 					 	= $gunHesapla["saatSay"];
											$asilkullanilanMolalar 		 	= $gunHesapla["asilkullanilanMolalar"];
											$calismasiGerekenToplamDakika  	= $gunHesapla["calismasiGerekenToplamDakika"];
											$calisilanToplamDakika 		 	= $gunHesapla["calisilanToplamDakika"];
											$kullanilanToplamMola 		 	= $gunHesapla["kullanilanToplamMola"];
											$kullanilmayanMolaToplami 	 	= $gunHesapla["kullanilmayanMolaToplami"];
											$islenenSaatler 			 	= $gunHesapla["islenenSaatler"];
											$izin[ "ucretli" ] 			 	= $gunHesapla["ucretli"];
											$izin[ "ucretsiz" ] 		 	= $gunHesapla["ucretsiz"];
											$kullanilmasiGerekenToplamMola 	= $gunHesapla["kullanilmasiGerekenToplamMola"];
											$personel_giris_cikis_sayisi   	= $gunHesapla["personel_giris_cikis_sayisi"];
											$personel_giris_cikis_saatleri 	= $gunHesapla["personel_giris_cikis_saatleri"];
											$genelCalismaSuresiToplami 	 	= $gunHesapla["genelCalismaSuresiToplami"];
											$tatil   					 	= $gunHesapla["tatil"];
											$maasa_etki_edilsin 		 	= $gunHesapla["maasa_etki_edilsin"];
											$ToplamKesintiSaati 		 	= $gunHesapla["ToplamKesintiSaati"];
											$ilkUygulanacakSaat 		 	= $gunHesapla["ilkUygulanacakSaat"];
											$tatil_mesaisi 		 		 	= $gunHesapla["tatil_mesaisi"];

											if( $genelCalismaSuresiToplami[ '1.00' ] > 11700){
												$genelCalismaSuresiToplami[ '1.00' ] = 11700;
											}

											/*Normal Çalışma Gününü sayıyoruz*/
											if ( $tatil == 'hayir' AND $personel_giris_cikis_sayisi > 0 )
												$normalGun++;
											
											/*Ucretli ve Ucretsiz izin günlerini Hesaplıyoruz*/
											if ( $izin[ "ucretli" ] > 0 ) 
												$ucretliIzinGun++;

											if ( $izin[ "ucretsiz" ] > 0 ) 
												$ucretsizIzinGun++;
											
											/*Personel Giriş Çıkış Sayısı 0 ise Mazaretsiz Gelmeme Sayısına ekliyoruz*/
											if ( $personel_giris_cikis_sayisi == 0 AND $tatil == 'hayir' )
												$mazeretsizGelmeme++;
										?>
										<tr>
											<td><?php echo $sayi; ?></td>
											<td><?php echo $sayi.'.'.$fn->ayAdiVer($ay,1).''.$fn->gunVer($tarih."-".$sayi); ?></td>
											<?php 
												$i = 1;
												$islemtipi = array();
												if ($personel_giris_cikis_sayisi == 0 ) {
													if ($tatil == "hayir") {
														$haftalikGelmeme[ $fn->kacinciHafta( $tarih."-".$sayi ) ] += 1;
													}
													$col = ($tarihSayisi*2);
													$col = $col == 0 ? 2 : $col;
													$i = 1;
													while ($i <= $col) { 
														echo '<td class="text-center" >-</td>';
														$i++;
													}
													$islemtipi["gelmedi"] = "Gelmedi"; 
												}
												$giriscikisFarki = $tarihSayisi - $personel_giris_cikis_sayisi;
											
												//uygulanan işlem tipleri
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													$giriscikis["islemTipi"] != "" ? $islemtipi[] = $giriscikis["islemTipi"] : '';
												}
												$fark["UcretliIzin"] 	= 0;
												$fark["UcretsizIzin"] 	= 0;
												$fark["mesai"] 		= 0;
												//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
												if ($personel_giris_cikis_sayisi ==$tarihSayisi ) {
													foreach($personel_giris_cikis_saatleri AS $giriscikis){
														$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
														$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
														echo '
															<td class="text-center">'.$baslangicSaat.'</td>
															<td class="text-center">'.$bitisSaat.'</td>';

														//Giriş Çıkış Arasındakik Dakika Farkı
														$baslangicSaati = strtotime($baslangicSaat );
														$bitisSaati 	= strtotime($bitisSaat );
														$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

														if ($giriscikis["islemTipi"] == "") {
															$fark["mesai"] 	+= $ToplamDakika;
														}else{
															//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
															$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
														}
														
														
													}
												}else if($personel_giris_cikis_sayisi == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
													echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'baslangic_saat' ].'</td>';
													$i = 1;
													while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
														echo '
															<td class="text-center"> - </td>
															<td class="text-center"> - </td>	
														';
														$i++;
													}
													echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'bitis_saat' ].'</td>';

													$baslangicSaati  = strtotime($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ]);
													$bitisSaati 	 = strtotime($personel_giris_cikis_saatleri[0][ 'bitis_saat' ]);
													$ToplamDakika 	 = ($bitisSaati - $baslangicSaati) / 60;

													if ($personel_giris_cikis_saatleri[0][ 'islemTipi' ] == "") {
														$fark["mesai"] += $ToplamDakika;
													}else{
														//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
														$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
													}

												}else{ //Gündee birden fazla giriş çıkış var ise 
													$i = 1;
													foreach($personel_giris_cikis_saatleri AS $giriscikis){
														
														if($i < $personel_giris_cikis_sayisi){

															$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
															$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
															echo '
																<td class="text-center">'.$baslangicSaat.'</td>
																<td class="text-center">'.$bitisSaat.'</td>';
														}else{
															$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
															$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
															echo '<td  class="text-center">'.$baslangicSaat.'</td>';
															$j = 1;
															while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
																echo '
																	<td class="text-center"> - </td>
																	<td class="text-center"> - </td>	
																';
																$j++;
															}
															echo '<td class="text-center">'.$bitisSaat.'</td>';
														}
														$i++;
														$baslangicSaati = strtotime($baslangicSaat );
														$bitisSaati 	= strtotime($bitisSaat );
														$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

														if ($giriscikis["islemTipi"] == "") {
															$fark["mesai"] 	+= $ToplamDakika;
														}else{
															//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
															$fark["UcretsizIzin"]  += $giriscikis["maas_kesintisi"] == 1 ?  $ToplamDakika : $ToplamDakika;
														}
													}
												}

											?>
											<td>	
												<?php
													/*Tatil olup olmadığını Kontrol Ediyoruz*/ 
													if ( $tatil == 'evet' ){
														echo '<b class="text-center text-info">Tatil</b>';
													}else{

														if ( array_key_exists("gelmedi", $islemtipi) AND $beyaz_yakali != "evet" ) {
															echo '<b class="text-center text-danger">Gelmedi</b>';
														}else if( array_key_exists("gelmedi", $islemtipi) AND $beyaz_yakali == "evet" ){

															echo '<b class="text-center text-success">Mesaide</b>';

														}else{
															echo '<b class="text-center text-warning">'.implode(", ", $islemtipi).'</b>';
														}
														echo count($islemtipi) == 0  ? '<b class="text-center text-success">Mesaide</b>' : '';
													}
														
												?>
											</td>
											<?php 
												//Hangi Carpan Üzerinde Kaç Saat Çalıştığını YAzdırıyoruz
												foreach ( $carpan_listesi as $carpan ) {
													if ( $calisilanToplamDakika[ $carpan[ "id" ] ] > 0 )
														echo '<td>'.gmdate("H:i", ( $calisilanToplamDakika[ $carpan[ "id" ] ] * 60 ) ).'</td>';
													else
														echo	'<td> - </td>';
												}	
											?>
											<td>
												<?php
													//Suan Maul Olarak 450 Dakika man. genel ayarlara dinamik sekilde güncellenecbilcektir
													if ( $tatil == 'evet' and $personel_giris_cikis_sayisi == 0 ){
														//1 Olan yere genel ayarlandan geneln gelmem gün sayısını getirilecek
														if ( $maasa_etki_edilsin == 'evet' AND $haftalikGelmeme[ $fn->kacinciHafta( $tarih."-".$sayi ) ] < $pazar_kesinti_sayisi ) {
															$tatilGunleriToplamDakika += $gunluk_calisma_suresi; 
															echo gmdate("H:i", ( ( $gunluk_calisma_suresi ) * 60 ) );
															/*Eğer Gün Pazar gününne veya hafta tailine denk geliyorsa Hafta tatili olarak sayılsın degilse Resmi Tatil olarak Sayılsın*/
															if ($fn->gunVer($tarih."-".$sayi) == 'Pazar')
																$haftaTatili++;
															else
																$genelTatil++;
														}else{
															echo '-';
															if ($fn->gunVer($tarih."-".$sayi) == 'Pazar')
																$aciklama = 'Ht. Doldrulmadı.';
														}
														
													}else{
														if( $tatil_mesaisi > 0 ){
															$tatilGunleriToplamDakika  += $yarim_gun_tatil_suresi;
															echo gmdate("H:i", ( ( $yarim_gun_tatil_suresi ) * 60 ) );
														}else{
															echo '<b class="text-center">-</b>';
														}
													}
												?>
													
											</td>
											<td>
												<?php 
													if ($izin[ "ucretli" ]>0) {
														$ucretliIzinGenelToplam  += $izin[ "ucretli" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ];
														echo gmdate("H:i", ( ( $izin[ "ucretli" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ] ) * 60 ) );
													}else{
														echo '-';
													}
												?>
											</td>
											<td>
												<?php 
													if ($izin["ucretsiz"]>0) {
														$ucretsizIzinGenelToplam  += $izin[ "ucretsiz" ] - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ];
														echo gmdate("H:i", ( ( $izin[ "ucretsiz" ]  - $kullanilmayanMolaToplami[ $ilkUygulanacakSaat ] ) * 60 ) );
													}else{
														echo '-';
													}
												?>
												
											</td>
											<td>
												<?php 
													// Toplam Kesinti Saati	
													$toplamIzin 		= $izin[ "ucretli" ] + $izin[ "ucretsiz" ];
													$cikarilacakMola 	= $kullanilmasiGerekenToplamMola;
													
													if ( $toplamIzin > 0 ) {
														$cikarilacakMola -= $kullanilmasiGerekenToplamMola - $kullanilanToplamMola[ $ilkUygulanacakSaat ];
													}

													//Toplam Calısılması gereken - calıstığı süre - izin süresi - Mola
													$ToplamKesintiSaati = $calismasiGerekenToplamDakika[$ilkUygulanacakSaat] - $calisilanToplamDakika[$ilkUygulanacakSaat] - $toplamIzin  - $cikarilacakMola;
													if($tatil == 'evet'){
														if ( $fn->gunVer( $tarih."-".$sayi ) == 'Pazar' ){
															if( $haftalikGelmeme[ $fn->kacinciHafta( $tarih."-".$sayi ) ] >= $pazar_kesinti_sayisi  ){
																$gunlukCalismaSaatiDakikaOlarak = 450;
																$genelToplamKesintiSuresi += $gunlukCalismaSaatiDakikaOlarak ;
																echo gmdate("H:i", ( $gunlukCalismaSaatiDakikaOlarak * 60 ) );
															}else{
																echo '-';
															}
														}
													}else{
														
														$molaSuresi = 0;
														
														if($ToplamKesintiSaati > 0 ){
															$genelToplamKesintiSuresi += $ToplamKesintiSaati ;
															echo gmdate("H:i", ( ( $ToplamKesintiSaati) * 60 ) );
														}else{
															echo '-';
														}
													}
												?>
											</td>
											<td><?php echo $aciklama; ?></td>
											
										</tr>
									<?php 
										}
										$sayi++;
									
									}

									if( $gunSayisi == 31 AND $saatlikHesapla == 1){
										$eksikGun 	-= 1;
									}
									//Normal Çalışma Suresini Ayarladık

									$normalCalismaSuresi 								= (($aylik_calisma_saati * 60) - $tatilGunleriToplamDakika - $genelToplamKesintiSuresi )-( $eksikGun * $gunluk_calisma_suresi );
									$genelCalismaSuresiToplami[ $normal_carpan_id ] 	= $normalCalismaSuresi;
								?>
							</tbody>
							<tfoot>
								<?php 
									/*Giriş Çıkış Sayısının 2 katı kadar sütun oluşşturuyoruz ve sabit 3 tane sutun ile birleştiriyoruz */
									$tarihSayisi = $tarihSayisi == 0 ? 1 : $tarihSayisi; 
									$birlestirilecekSutunSayisi = 3 + ( $tarihSayisi  * 2 );
								?>

								<th colspan=" <?php echo $birlestirilecekSutunSayisi; ?> "> Toplam:</th>

								<?php
									/*Hangi Çarpanda Ne kadar Çalıştığını Hesaplıyoruz*/
									foreach ( $carpan_listesi as $carpan ) {
										echo '<th>'.$fn->dakikaSaatCevirString( $genelCalismaSuresiToplami[ $carpan[ "id" ] ] ).'</th>';
									}

									/*Genel ve Hafta Tatili Toplam Saat Hesaplama*/
									echo '<th>'.$fn->dakikaSaatCevirString( $tatilGunleriToplamDakika ).'</th>';

									/* Toplamda Kullandığı Ücretsiz izni Hesaplıyoruz*/
									echo '<th>'.$fn->dakikaSaatCevirString( $ucretliIzinGenelToplam ).'</th>';

									/* Toplamda Kullandığı Ücretsiz izni Hesaplıyoruz*/
									echo '<th>'.$fn->dakikaSaatCevirString( $ucretsizIzinGenelToplam ).'</th>';

									/*Toplam Kesinti Yapılan Toplam Suues*/
									echo '<th>'.$fn->dakikaSaatCevirString( $genelToplamKesintiSuresi ).'</th>';

								?>
								<th></th>
							</tfoot>
						</table>
					</div>
				</div>
			</div>	
		</div>
	</div>
</section>
<div >
	<div id="ciktiAlt">
		<hr>
		<div class="col-sm-3 float-left bilgiTablosu" modul="puantaj" yetki_islem="goruntule">
			<table class="table">
				<tr>
					<th width="80%">Açıklama</th>
					<th>Gün</th>
				</tr>
				<tr>
					<td>Normal Gün</td>
					<td><?php echo $normalGun; ?></td>
				</tr>
				<tr>
					<td>Hafta Tatili</td>
					<td><?php echo $haftaTatili ?></td>
				</tr>
				<tr>
					<td>Genel Tatil</td>
					<td><?php echo $genelTatil; ?></td>
				</tr>
				<tr>
					<td>Ücretli İzin</td>
					<td><?php echo $ucretliIzinGun; ?></td>
				</tr>
				<tr>
					<td>Ücretsiz İzin</td>
					<td><?php echo $ucretsizIzinGun; ?></td>
				</tr>
				<tr>
					<td>M.siz Gelmeme</td>
					<td><?php echo $mazeretsizGelmeme; ?></td>
				</tr>
				<tr>
					<td>Hak Ediş Günü</td>
					<td><?php echo ( $normalGun + $haftaTatili + $genelTatil + $ucretliIzinGun ); ?></td>
				</tr>
				
			</table>
		</div>
		<div class="col-sm-3 float-left bilgiTablosu">
			<table class="table">
				<tr>
					<th width="70%">Açıklama</th>
					<th>Saat</th>
				</tr>

				<?php 
					foreach ( $carpan_listesi as $carpan ) {
						echo "<tr>
							<td>$carpan[adi]</td>
							<td>".($fn->dakikaSaatCevir( $genelCalismaSuresiToplami[$carpan['id']]))."</td>
						</tr>";
					}
				?>
				<tr>
					<td>Ücretli İzin</td>
					<td><?php echo $fn->dakikaSaatCevir( $izin[ 'ucretli' ] ); ?></td>
				</tr>
				<tr>
					<td>Ücretsiz İzin</td>
					<td><?php echo $fn->dakikaSaatCevir( $izin[ 'ucretsiz' ] ) ?></td>
				</tr>
				<tr>
					<td>Toplam Kesinti</td>
					<td><?php echo $fn->dakikaSaatCevir( $genelToplamKesintiSuresi ); ?></td>
				</tr>
				<tr>
					<td>Hakediş</td>
					<td><?php echo $fn->dakikaSaatCevir( $genelCalismaSuresiToplami[ '1.00' ] + $izin[ 'ucretli' ] + $tatilGunleriToplamDakika ); ?></td>
				</tr>
				
				
			</table>
		</div>
		<div class="col-sm-3 float-left bilgiTablosu">
			<table class="table">
				<tr>
					<th width="70%">Açıklama</th>
					<th>Birim (TL)</th>
				</tr>
				<tr>
					<td>Bordro</td>
					<td><?php  echo $fn->parabirimi( $personel_maas ); ?></td>
				</tr>
				<tr>
					<td>Günlük</td>
					<td><?php echo  $fn->parabirimi( $personel_maas / 30 );  ?></td>
				</tr>
				<tr>
					<td>Saat</td>
					<td><?php echo  $fn->parabirimi( $personel_maas / $aylik_calisma_saati );  ?></td>
				</tr>
				<tr>
					<td>%50</td>
					<td><?php echo  $fn->parabirimi( ( $personel_maas / $aylik_calisma_saati ) * 1.5 );  ?></td>
				</tr>
				<tr>
					<td>%100</td>
					<td><?php echo  $fn->parabirimi( ( $personel_maas / $aylik_calisma_saati ) * 2.0 );  ?></td>
				</tr>
				<tr>
					<td>T. Mesai</td>
					<td><?php echo  $fn->parabirimi( ( $personel_maas / $aylik_calisma_saati ) * 1.5 );  ?></td>
				</tr>
			</table>
		</div>
		<div class="col-sm-3 float-left bilgiTablosu">
			<table class="table">
				<tr>
					<th width="70%">Açıklama</th>
					<th>Ücret (TL)</th>
				</tr>
				<tr>
					<td>Normal Çalışma</td>
					<td><?php echo $fn->parabirimi( $personel_maas ); ?></td>
				</tr>
				<tr>
					<td>Gelmeme Kesintisi</td>
					<td><?php echo  $gelmeme_kesintisi = $fn->parabirimi( ( ( $personel_maas / $aylik_calisma_saati) / 60 ) * $genelToplamKesintiSuresi );  ?></td>
				</tr>
				<tr>
					<td>Normal Hakediş</td>
					<td>	
						<?php
							$normalHakedis = ($personel_maas / $aylik_calisma_saati / 60 ) * ($normalCalismaSuresi+$tatilGunleriToplamDakika+$ucretliIzinGenelToplam) * 1;
						 	echo $fn->parabirimi( $normalHakedis ); 
						 ?>
						 
					</td>
				</tr>
				<tr>
					<td>Mesai Kazancı</td>
					<td>
						<?php
							
							$mesaiKazanci = 0;
							foreach ($genelCalismaSuresiToplami as $carpan => $calisma) {
							 	$mesaiKazanci += $carpan == $normal_carpan_id ? 0 : ( ( $personel_maas / $aylik_calisma_saati ) / 60 ) * $carpan_fiyat[$carpan] * $calisma ;
							}
							echo $fn->parabirimi($mesaiKazanci);

						?>
					</td>
				</tr>
				<tr>
					<td>Ek Ödeme Toplamı</td>
					<td><?php echo $fn->parabirimi( $kazanilan[ "toplamTutar" ] ); ?></td>
				</tr>
				<tr>
					<td>Ek Kesinti Toplamı</td>
					<td><?php echo $fn->parabirimi( $kesinti[ "toplamTutar" ] ); ?></td>
				</tr>
				<tr>
					<td>Avans Toplamı</td>
					<td></td>
				</tr>
				<tr>
					<td>Borç Tutarı</td>
					<td>
						<?php
							$sonuc = $normalHakedis + $mesaiKazanci +$kazanilan[ "toplamTutar" ] - $kesinti[ "toplamTutar" ];
							echo  $fn->parabirimi($sonuc < 0 ? $sonuc : 0);
						?>
					</td>
				</tr>
				<tr>
					<td>Ödeme Tutarı</td>
					<td><?php echo  $fn->parabirimi($sonuc > 0 ? $sonuc : 0); ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
<div class="clearfix"></div>

<script type="text/javascript">

	var ciktiAlt = document.getElementById( 'ciktiAlt' );
	ciktiAlt =  ciktiAlt.outerHTML;

	var ciktiUst = document.getElementById( 'ciktiUst' );
	ciktiUst =  ciktiUst.outerHTML;
	
	$(function () {
		$('#datetimepickerAy').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM',
			locale:'tr',
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
		var  url 		= window.location;
		var origin		= url.origin;
		var path		= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   	= search.get('modul');
		var personel_id = "&personel_id="+personel_id;
		
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
		"responsive": true, "autoWidth": true,
		"stateSave": true,
		"searching": false,
		"bPaginate": false,
		"bInfo": false,
		//"buttons": ["excel", "print"],

		buttons : [
			{
				extend	: 'colvis',
				text		: "Alan Seçiniz"
				
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
				text		: 'Yazdır',
				customize: function ( win ) {
                    $(win.document.body)
                        .css( 'font-size', '10pt' )
                        .prepend(
                            ciktiUst
                        );
                         $(win.document.body)
                        .css( 'font-size', '10pt' )
                        .append(
                            ciktiAlt
                        );
	 
	                    $(win.document.body).find( 'table' )
	                        .addClass( 'compact' )
	                        .css( 'font-size', 'inherit' );
	               },
				exportOptions : {
					columns : ':visible'
				},
				title: function(){
					return "";
				}
			}
		]
	} ).buttons().container().appendTo('#tbl_giriscikislar_wrapper .col-md-6:eq(0)');

</script>
