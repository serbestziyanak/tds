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

$islem			= array_key_exists( 'islem'		,$_REQUEST ) ? $_REQUEST[ 'islem' ]		: 'ekle';
$personel_id		= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
@$detay			= array_key_exists( 'detay'        ,$_REQUEST ) ? $_REQUEST[ 'detay' ] 		: 'ay';

//Personele Ait Listelenecek Hareket Ay
@$listelenecekAy	= array_key_exists( 'tarih'			,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date( "Y-m" );

//Hareketlerde Bulununa duzenle butununa tıklandığında listelenecek tarihi alıyoruz
@$listelenecekTarih	= array_key_exists( 'duzenlenecek_tarih'	,$_REQUEST ) ? $_REQUEST[ 'duzenlenecek_tarih' ]	: date( "Y-m" );

$tarih 			= $listelenecekAy;
$tarihBol 		= explode( "-", $tarih);
$ay 				= intval($tarihBol[1] );
$yil 			= $tarihBol[0];

if($detay == "gun" ) $listelenecekgun	= array_key_exists( 'tarih'	,$_REQUEST ) ? "'".$_REQUEST[ 'tarih' ]."'"	: "'".date( "Y-m-d" )."'";
if($detay == "gun" ) $listelenecekgun1	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]		: date( "Y-m-d" );
 
$satir_renk		= $personel_id > 0	? 'table-warning'					: '';
$kaydet_buton_yazi	= $personel_id > 0	? 'Güncelle'						: 'Kaydet';
$kaydet_buton_cls	= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
FROM
	tb_personel AS p
WHERE
	p.id = ? AND firma_id =? AND p.aktif = 1
SQL;

//belirli bir aya göre personelin giriş çıkış hareketleri
//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	COUNT(tarih) AS tarihSayisi
FROM
	tb_giris_cikis
WHERE
	baslangic_saat  IS NOT NULL AND 
	personel_id 				= ? AND 
	DATE_FORMAT(tarih,'%Y-%m') 	= ?  AND 
	aktif 						= 1
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli bir güne ait tüm personelin giriş çıkış işlemleri Detay Parametresi var ise devreye girecektir
$SQL_gunluk_giris_cikis = <<< SQL
SELECT 
	p.id,
	p.grup_id,
	CONCAT(adi ," ",soyadi) AS adSoyad,
	(select 
		COUNT(tarih) 
	FROM tb_giris_cikis  
	WHERE  
		p.id = tb_giris_cikis.personel_id AND 
		DATE_FORMAT(tarih,'%Y-%m-%d') = $listelenecekgun AND
		tb_giris_cikis.aktif = 1 ) AS tarihSayisi
FROM tb_personel AS p
WHERE p.firma_id =? AND aktif = 1 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
     gc.id
    ,gc.baslangic_saat
    ,gc.bitis_saat
	,gc.baslangic_saat_guncellenen
	,gc.bitis_saat_guncellenen
	,gc.islem_tipi
	,tp.adi AS islemTipi
FROM
	tb_giris_cikis AS gc
LEFT JOIN tb_giris_cikis_tipi AS ftp ON ftp.id =  gc.islem_tipi
LEFT JOIN tb_giris_cikis_tipleri AS tp ON tp.id =  ftp.tip_id
LEFT JOIN tb_personel AS p ON gc.personel_id =  p.id
WHERE
	gc.personel_id = ? AND 
	gc.tarih 		= ? AND 
	p.firma_id 	= ? AND 
	gc.aktif 		= 1
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
	(SELECT tip_id from tb_giris_cikis_tipi 
	WHERE 
		tb_giris_cikis_tipi.tip_id = tb_giris_cikis_tipleri.id AND firma_id = 2 ) AS varmi
FROM
	tb_giris_cikis_tipleri
ORDER BY adi ASC
SQL;

//BELİRTİLEN TARİHLER ARASI EN SON EKLENEN TARİFE LİSTESİ 
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
	tb_tarife_saati AS s
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;


$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION[ 'firma_id' ] ) );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$firma_giris_cikis_tipleri	= $vt->select( $SQL_firma_giris_cikis_tipi,array($_SESSION[ "firma_id" ] ) )[2];
$giris_cikislar			= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy ) )[2];
$gunluk_giris_cikislar		= $vt->select( $SQL_gunluk_giris_cikis, array($_SESSION[ 'firma_id' ] ) )[2];
$tum_giris_cikis_tipleri		= $vt->select( $SQL_tum_giris_cikis_tipleri)[2];

$satir_renk				= $personel_id > 0	? 'table-warning' : '';

if($detay == "gun" ){
	//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
	foreach($gunluk_giris_cikislar AS $giriscikisgun){
		$tarihSayisi[] = $giriscikisgun[ "tarihSayisi" ]; 
	}
}else{
	//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
	foreach($giris_cikislar AS $giriscikisgun){
		$tarihSayisi[] = $giriscikisgun[ "tarihSayisi" ]; 
	}
}
@$tarihSayisi = max($tarihSayisi) == 0 ? 1: max($tarihSayisi); 

//Bir tarihe ait saat düzenleme butonuna tıklandığında personel bilgisini ve personelin yaptığı giriş çıkışlar listesini alıyoruz
@$tek_personel 			= $vt->select( $SQL_tek_personel_oku, array($personel_id,$_SESSION[ 'firma_id' ] ) )[ 2 ];
if ($islem == "saatduzenle" AND count($tek_personel)>0) {
	$personel_giris_cikis 	= $vt->select( $SQL_belirli_tarihli_giris_cikis, array($personel_id,$listelenecekTarih,$_SESSION[ 'firma_id' ] ) )[2];


}

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<button modul = 'giriscikis' yetki_islem="personel_hareket_ekle" class="btn btn-outline-primary  m-0" data-toggle="modal" data-target="#PersonelHareketEkle">Personele Hareket Ekle</button>
				<button modul = 'giriscikis' yetki_islem="personel_toplu_hareket_ekle" class="btn btn-outline-success  m-0" data-toggle="modal" data-target="#TopluHareketEkle">Toplu Hareket Ekle</button>
				<?php 
					$link 		=  $detay == "gun" ? '?modul=giriscikis' 	: '?modul=giriscikis&detay=gun'; 
					$btnyazi 	=  $detay == "gun" ? 'Personele Ait Veri Getir' 	: 'Günlük Veri Getir'; 
				?>
				<a modul = 'giriscikis' yetki_islem="gunluk_giris_cikis" class="btn btn-outline-dark  m-0" href="<?php echo $link; ?>"><?php echo $btnyazi; ?></a>
				<button modul = 'giriscikis' yetki_islem="giris_cikis_tipi" class="btn btn-outline-warning    m-0" data-toggle="modal" data-target="#IslemTipi">Giriş Çıkış Tipi</button>
				<button modul = 'giriscikis' yetki_islem="giris_cikis_tipi" class="btn btn-outline-danger    m-0" data-toggle="modal" data-target="#dosyaOku">Dosyadan Yazdır</button>

				<?php if ($detay =="gun" )  { ?>
					<div class="col-sm-2 m-0 " style="float: right;display: flex;">
						<div class="">
							<div class="input-group date" id="datetimepickerGun" data-target-input="nearest">
								<div class="input-group-append" data-target="#datetimepickerGun" data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
								<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepickerGun" data-toggle="datetimepicker" id="tarihSec" value="<?php echo $listelenecekgun1; ?>"/>
							</div>
						</div>
						<div style="float: right;display: flex;">
							<button class="btn btn-success" id="listeleBtn">listele</button>
						</div>
					</div>
				<?php }else{?>
					
					<div class="col-sm-2 m-0 m-0" style="float: right;display: flex;">
						<div class="">
							<div class="input-group date" id="datetimepickerAy" data-target-input="nearest">
								<div class="input-group-append" data-target="#datetimepickerAy" data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
								<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepickerAy" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy ) echo $listelenecekAy; ?>"/>
							</div>
						</div>
						<div style="float: right;display: flex;">
							<button class="btn btn-success" id="listeleBtn">listele</button>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php if($detay != "gun" ){ ?>
			<div class="col-md-4">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $personeller[ 2 ] AS $personel ) { ?>
								<tr <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'"; ?> id="personel-Tr">
									<td><?php echo $sayi++; ?>					</td>
									<td><?php echo $personel[ 'tc_no' ]; ?>		</td>
									<td><?php echo $personel[ 'adi' ]; ?>		</td>
									<td><?php echo $personel[ 'soyadi' ]; ?>	</td>
									<td align = "center">
										<a modul = 'giriscikis' yetki_islem="hareketler" class = "btn btn-sm btn-success btn-xs" href = "?modul=giriscikis&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo $listelenecekAy; ?>" >
											Hareketler
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="card card-secondary">
					<div class="card-header p-2">
						<ul class="nav nav-pills">
							<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Personel Hareketleri</h6>
						</ul>
					</div>
					<div class="card-body">
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
									<th modul="anasayfa" yetki_islem="tutanakYaz">Durum</th>
									<th>İşlem</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									//SECİLEN AYIN KAÇ GÜN ÇEKTİĞİ
									$gunSayisi = $fn->ikiHaneliVer( $ay ) == date( "m" ) ? date( "d" ) : date( "t",mktime(0,0,0,$ay,01,$yil));
									$sayi = 1; 

									while( $sayi <= $gunSayisi ) { 

										$gun = $fn->gunVer($tarih.'-'.$sayi);

										$giris_cikis_saat_getir = $vt->select( $SQL_giris_cikis_saat, array( $tarih."-".$sayi, $tarih."-".$sayi, '%,'.$gun.',%', '%,'.$tek_personel[ 0 ]["grup_id"].',%' ) ) [ 2 ][ 0 ];

										/*tarifeye ait mesai saatleri */

										$saatler = $vt->select( $SQL_tarife_saati, array( $giris_cikis_saat_getir[ 'id' ] ) )[ 2 ];

										//Mesaiye 10 DK gec Gelme olasıılıgını ekledik 10 dk ya kadaar gec gelebilir 
										$mesai_baslangic 	= date("H:i", strtotime('+10 minutes', strtotime( $saatler[ 0 ]["baslangic"] ) ) );
										//Personel 5 DK  erken çıkabilir
										$mesai_bitis 		= date("H:i", strtotime('-5 minutes',  strtotime( $saatler[ 0 ]["bitis"] ) ) );
										//Eger Tatil Olarak İsaretlenmisse Giriş Zorunluluğu bulunmayıp mesaiye gelmisse mesai yazdıracaktır.
										$tatil = $giris_cikis_saat_getir["tatil"] == 1  ?  'evet' : 'hayir';
										

										
										
										$islemtipi = array();
										$personel_giris_cikis_saatleri 	= $vt->select($SQL_belirli_tarihli_giris_cikis,array($personel_id,$tarih."-".$sayi,$_SESSION[ 'firma_id' ] ))[2];

										$personel_giris_cikis_sayisi   	= count($personel_giris_cikis_saatleri);

										/*Perosnel Giriş Yapmış ise tatilden Satılmayacak Ek mesai oalrak hesaplanacaktır. */
										if($personel_giris_cikis_sayisi > 0){
											$tatil = 'hayir';
										}

										//Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
										$son_cikis_index 	= $personel_giris_cikis_sayisi - 1;
										$ilk_islemtipi 	= $personel_giris_cikis_saatleri[0]['islem_tipi'];
										$son_islemtipi 	= $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

										$ilkGirisSaat 		= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

										$SonCikisSaat 		= $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);
										
										if ($ilkGirisSaat[0] > $mesai_baslangic AND ( $ilk_islemtipi =="" or $ilk_islemtipi == "0" )  ) {
											$islemtipi["gecgelme"] 	 = $ilkGirisSaat[0];
										}
										if ($SonCikisSaat[0] < $mesai_bitis AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
											$islemtipi["erkencikma"] = $SonCikisSaat[0];
										}

								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $sayi.'.'.$fn->ayAdiVer($ay,1).''.$fn->gunVer($tarih.'-'.$sayi); ?></td>
										<?php 
											$i = 1;
											if ($personel_giris_cikis_sayisi == 0) {
												$col = ($tarihSayisi*2);
												$col = $col == 0 ? 2 : $col;
												$i = 1;
												while ($i <= $col) { 
													echo '<td class="text-center" >-</td>';
													$i++;
												}
												$islemtipi[ "gelmedi" ] = "Gelmedi"; 
											}
											$giriscikisFarki = $tarihSayisi - $personel_giris_cikis_sayisi;
										
											//uygulanan işlem tipleri
											foreach($personel_giris_cikis_saatleri AS $giriscikis){
												$giriscikis[ "islemTipi" ] != "" ? $islemtipi[] = $giriscikis[ "islemTipi" ] : '';
											}
											$fark[ "UcretliIzin" ] 		= 0;
											$fark[ "UcretsizIzin" ] 	= 0;
											$fark[ "mesai" ] 			= 0;
											//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
											if ($personel_giris_cikis_sayisi ==$tarihSayisi ) {
												foreach($personel_giris_cikis_saatleri AS $giriscikis){

													$baslangicSaat 	= $fn->saatKarsilastir($giriscikis[ 'baslangic_saat' ], $giriscikis["baslangic_saat_guncellenen"]);
													$bitisSaat 		= $fn->saatKarsilastir($giriscikis[ 'bitis_saat' ], $giriscikis["bitis_saat_guncellenen"]);
													
													echo '
														<td class="text-center">'.$baslangicSaat[1].'</td>
														<td class="text-center">'.$bitisSaat[1].'</td>';
												}
											}else if($personel_giris_cikis_sayisi == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
												$baslangicSaat 	= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0][ 'baslangic_saat_guncellenen' ]);
												$bitisSaat 		= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'bitis_saat' ], $personel_giris_cikis_saatleri[0][ 'bitis_saat_guncellenen' ]);
												echo '<td class="text-center">'.$baslangicSaat[1].'</td>';
												$i = 1;
												while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
													echo '
														<td class="text-center"> - </td>
														<td class="text-center"> - </td>	
													';
													$i++;
												}

												echo '<td class="text-center">'.$bitisSaat[1].'</td>';

											}else{ //Günde birden fazla giriş çıkış var ise 
												$i = 1;
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													
													if($i < $personel_giris_cikis_sayisi){

														$baslangicSaat 	= $fn->saatKarsilastir($giriscikis[ 'baslangic_saat' ], $giriscikis["baslangic_saat_guncellenen"]);
														$bitisSaat 		= $fn->saatKarsilastir($giriscikis[ 'bitis_saat' ], $giriscikis["bitis_saat_guncellenen"]);

														echo '
															<td class="text-center">'.$baslangicSaat[1].'</td>
															<td class="text-center">'.$bitisSaat[1].'</td>';
													}else{
														$baslangicSaat 	= $fn->saatKarsilastir($giriscikis[ 'baslangic_saat' ], $giriscikis["baslangic_saat_guncellenen"]);
														$bitisSaat 		= $fn->saatKarsilastir($giriscikis[ 'bitis_saat' ], $giriscikis["bitis_saat_guncellenen"]);
														echo '<td  class="text-center">'.$baslangicSaat[1].'</td>';
														$j = 1;
														while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
															echo '
																<td class="text-center"> - </td>
																<td class="text-center"> - </td>';
															$j++;
														}
														echo '<td class="text-center">'.$bitisSaat[1].'</td>';
													}
													$i++;
												}
											}
										?>
										
										<td width="270px" modul="anasayfa" yetki_islem="tutanakYaz">
											<?php 
												if($tatil == "evet" ){
													echo '<b class="text-center text-info">'.$giris_cikis_saat_getir[ 'adi' ].'</b>';
												}else{
													//islemTipi Fonksiyonu personelin izin kullanıp kullanmadığını ise gelme durumunu gelmiş ise erken mi veya gecmi çkıkısını kontrol ediyorum
													echo $fn->islemTipi($islemtipi,$personel_id,$tarih.'-'.$sayi);

												}
											?>
										</td>
										<td>
											<?php if($fn->gunVer($tarih.'-'.$sayi) != "Pazar" AND !array_key_exists( "gelmedi", $islemtipi)){  ?>
												<a modul = 'personel' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=giriscikis&personel_id=<?php echo $personel_id; ?>&tarih=<?php echo $tarih; ?>&duzenlenecek_tarih=<?php echo $tarih.'-'.$fn->ikiHaneliVer($sayi); ?>&islem=saatduzenle" id="saat_duzenle">
													Düzenle
												</a>
											<?php }else{ echo '-'; } ?>
										</td>
										
									</tr>
								<?php $sayi++; $tatil = 'hayir';} ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php }else{ ?>
			<div class="col-12" modul = 'giriscikis' yetki_islem="goruntule">
				<div class="card card-secondary" id = "card_giriscikislar">
					<div class="card-header">
						<h3 class="card-title">Tüm Personelin Giriş Çıkış Hareketleri</h3>
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
									<th>Personel Adı</th>
									<?php
										$i = 1;

										echo $tarihSayisi == 0 ? '<th>Giriş Çıkış</th>':'';
										while ($i <= $tarihSayisi) {
											
											$thBaslikilk = $i == 1 ? 'İlk Giriş' : 'Giriş';

											$thBaslikSon = $i == $tarihSayisi ? 'Son Çıkış' : 'Çıkış';

											echo '<th>'.$thBaslikilk.'</th><th>'.$thBaslikSon.'</th>';
											$i++;
										}
									?>
									<th>İşlem</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$gun 	= $fn->gunVer($listelenecekgun1);
									$tarih 	= $listelenecekgun1;
									$sayi 	= 1; 

									foreach( $gunluk_giris_cikislar AS $giriscikis_personel ) { 


										$giris_cikis_saat_getir = $vt->select( $SQL_giris_cikis_saat, array( $tarih, $tarih, '%,'.$gun.',%', '%,'.$giriscikis_personel["grup_id"].',%' ) ) [ 2 ][ 0 ];

										$saatler = $vt->select( $SQL_tarife_saati, array( $giris_cikis_saat_getir[ 'id' ] ) )[ 2 ];

										//Mesaiye 10 DK gec Gelme olasıılıgını ekledik 10 dk ya kadaar gec gelebilir 
										$mesai_baslangic 	= date("H:i", strtotime('+10 minutes', strtotime( $saatler[ 0 ]["baslangic"] ) ) );
										//Personel 5 DK  erken çıkabilir
										$mesai_bitis 		= date("H:i", strtotime('-5 minutes',  strtotime( $saatler[ 0 ]["bitis"] ) ) );
										
										//Eger Tatil Olarak İsaretlenmisse Giriş Zorunluluğu bulunmayıp mesaiye gelmisse mesai yazdıracaktır.
										$tatil = $giris_cikis_saat_getir["tatil"] == 1  ?  'evet' : 'hayir';

								?>
								<tr>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $giriscikis_personel[ 'adSoyad' ]; ?></td>
									<?php 

										$islemtipi = array();

										//Gelen tarihe ait giriş çıkışları aldık 
										$personel_giris_cikis_saatleri 	= $vt->select($SQL_belirli_tarihli_giris_cikis,array( $giriscikis_personel[ 'id' ],$listelenecekgun1,$_SESSION[ 'firma_id' ] ) )[2];

										$personel_giris_cikis_sayisi = count($personel_giris_cikis_saatleri);
										
										if( $personel_giris_cikis_sayisi > 0){
											$tatil = 'hayir';
										}

										//Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
										$son_cikis_index 			= $personel_giris_cikis_sayisi - 1;
										$ilk_islemtipi 			= $personel_giris_cikis_saatleri[0]['islem_tipi'];
										$son_islemtipi 			= $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

										$ilkGirisSaat 			= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

										$SonCikisSaat 			= $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);
										
										if ($ilkGirisSaat[0] > $mesai_baslangic AND ( $ilk_islemtipi =="" or $ilk_islemtipi == "0" )  ) {
											$islemtipi["gecgelme"] 	 = $ilkGirisSaat[0];
										}
										if ($SonCikisSaat[0] < $mesai_bitis AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
											$islemtipi["erkencikma"] = $SonCikisSaat[0];
										}


										$personel_toplam_giriscikis_sayisi = count($personel_giris_cikis_saatleri);
										
										$giriscikisFarki = $tarihSayisi - $personel_toplam_giriscikis_sayisi;
										
										//Personel Girş Veya Çıkış Yapmamış İse
										if ($personel_toplam_giriscikis_sayisi == 0) {
											$colspan = ($tarihSayisi*2);
											$colspan = $colspan == 0 ? 1 : $colspan;
											$i = 1;
											while ($i <= $colspan) { 
												echo '<td class="text-center" >-</td>';
												$i++;
											}
											$islemtipi[ "gelmedi" ] = "Gelmedi"; 
										}

										//uygulanan işlem tipleri
										foreach($personel_giris_cikis_saatleri AS $giriscikis_tip){
											$giriscikis_tip[ "islemTipi" ] != "" ? $islemtipi[] = $giriscikis_tip[ "islemTipi" ] : '';
										}

										//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
										if ($personel_toplam_giriscikis_sayisi ==$tarihSayisi ) {
											foreach($personel_giris_cikis_saatleri AS $giriscikis_personel){
												$baslangicSaat 	= $fn->saatKarsilastir($giriscikis_personel[ 'baslangic_saat' ], $giriscikis_personel["baslangic_saat_guncellenen"]);
												$bitisSaat 		= $fn->saatKarsilastir($giriscikis_personel[ 'bitis_saat' ], $giriscikis_personel["bitis_saat_guncellenen"]);
												echo '
													<td class="text-center">'.$baslangicSaat[1].'</td>
													<td class="text-center">'.$bitisSaat[1].'</td>	
												';
											}
										}else if($personel_toplam_giriscikis_sayisi == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
											$baslangicSaat 	= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0][ 'baslangic_saat_guncellenen' ]);
											$bitisSaat 	= $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'bitis_saat' ], $personel_giris_cikis_saatleri[0][ 'bitis_saat_guncellenen' ]);
											echo '<td class="text-center">'.$baslangicSaat[1].'</td>';
											$i = 1;
											while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
												echo '
													<td class="text-center"> - </td>
													<td class="text-center"> - </td>	
												';
												$i++;
											}
											echo '<td class="text-center">'.$bitisSaat[1].'</td>';
										}else{ //Gündee birden fazla giriş çıkış var ise 
											$i = 1;
											foreach($personel_giris_cikis_saatleri AS $giriscikis_saat){
												
												$baslangicSaat 	= $fn->saatKarsilastir($giriscikis_saat[ 'baslangic_saat' ], $giriscikis_saat["baslangic_saat_guncellenen"]);
												$bitisSaat 		= $fn->saatKarsilastir($giriscikis_saat[ 'bitis_saat' ], $giriscikis_saat["bitis_saat_guncellenen"]);

												if($i < $personel_toplam_giriscikis_sayisi){
													echo '
														<td class="text-center">'.$baslangicSaat[1].'</td>
														<td class="text-center">'.$bitisSaat[1].'</td>';
												}else{
													$j = 1;
													while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
														echo '
															<td class="text-center"> - </td>
															<td class="text-center"> - </td>	
														';
														$j++;
													}
													echo '<td class="text-center">'.$bitisSaat[1].'</td>';
												}
												$i++;
											}
										}
									?>
									
									<td class="text-center" width="280px"> 
										<?php 
											//islemTipi Fonksiyonu personelin izin kullanıp kullanmadığını ise gelme durumunu gelmiş ise erken mi veya gecmi çkıkısını kontrol ediyorum
											if ( $tatil == 'evet' ) {
												echo '<b class="text-info">'.$giris_cikis_saat_getir[ 'adi' ].'</b>';
											}else{
												echo $fn->islemTipi( $islemtipi, $giriscikis_personel["id"], $tarih );
											}
										?>
									</td>
									
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>	
			<?php } ?>
		</div>
	</div>
</section>

<!--Toplu Hareket Ekleme Modalı-->
<div class="modal fade" id="TopluHareketEkle"  aria-modal="true" role="dialog" modul = 'giriscikis' yetki_islem="kaydet">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="_modul/giriscikis/giriscikisSEG.php" method="post" >
				<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
				<input type = "hidden" name = "toplu" value = "toplu">
				<div class="modal-header">
					<h4 class="modal-title">Toplu Hareket Ekle</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Başlangıc Tarihi ve Saati</label>
						<div class="input-group date" id="toplubaslangicDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#toplubaslangicDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="baslangicTarihSaat" class="form-control datetimepicker-input" data-target="#toplubaslangicDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">Bitiş Tarihi ve Saati</label>
						<div class="input-group date" id="toplubitisDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#toplubitisDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="bitisTarihSaat" class="form-control datetimepicker-input" data-target="#toplubitisDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">İşlem Tipi</label>
						<select class="form-control select2" name = "islem_tipi">
							<option value="">Seçiniz</option>
							<?php foreach( $firma_giris_cikis_tipleri as $tip ) { ?>
								<option value="<?php echo $tip[ 'id' ]; ?>"><?php echo $tip[ 'adi' ]; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Açıklama</label>
						<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"></textarea>
					</div>
					
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
					
				</div>
			</form>
		</div>
	</div>
</div>

<!--Personel Hareket Ekleme Modalı-->
<div class="modal fade" id="PersonelHareketEkle"  aria-modal="true" role="dialog" modul = 'giriscikis' yetki_islem="kaydet">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="_modul/giriscikis/giriscikisSEG.php" method="post" >
				<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
				<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
				<div class="modal-header">
					<h4 class="modal-title">Personel Hareket Ekle</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Başlangıc Tarihi ve Saati</label>
						<div class="input-group date" id="baslangicDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#baslangicDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="baslangicTarihSaat" class="form-control datetimepicker-input" data-target="#baslangicDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">Bitiş Tarihi ve Saati</label>
						<div class="input-group date" id="bitisDateTime" data-target-input="nearest">
							<div class="input-group-append" data-target="#bitisDateTime" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="bitisTarihSaat" class="form-control datetimepicker-input" data-target="#bitisDateTime" data-toggle="datetimepicker"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">İşlem Tipi</label>
						<select class="form-control select2" name = "islem_tipi">
							<option value="">Seçiniz</option>
							<?php foreach( $firma_giris_cikis_tipleri as $tip ) { ?>
								<option value="<?php echo $tip[ 'id' ]; ?>"><?php echo $tip[ 'adi' ]; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Açıklama</label>
						<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"></textarea>
					</div>
					
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
					
				</div>
			</form>
		</div>
	</div>
</div>

<!--Firma Tip İşlemleri -->
<div class="modal fade" id="IslemTipi"  aria-modal="true" role="dialog" modul = 'giriscikis' yetki_islem="tipKaydet">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Giriş Çıkış - İzin Tipi Ayarlama</h4>
				</div>
				<div class="modal-body">
					<table id="tbl_firmaGirisCikis" class="table table-bordered table-hover table-sm" width = "100%">
						<thead>
							<tr>
								<th style="width: 15px">#</th>
								<th>Baslık</th>
								<th>Maaş Kesintisi</th>
								<th data-priority="1" style="width: 20px">Sil</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$sayi = 1;
								foreach ($firma_giris_cikis_tipleri as $giris_cikis_tipi) {
									$maas_kesintisi = $giris_cikis_tipi[ "maas_kesintisi" ] ==1 ? 'Kesintili':' Kesintisiz';
									echo '<tr>';
										echo '<td>'.$sayi.'</td>';
										echo '<td>'.$giris_cikis_tipi[ "adi" ].'</td>';
										echo '<td>'.$maas_kesintisi.'</td>';
										echo '<td><button modul= "giriscikis" yetki_islem="giris_cikis_tipi_sil" class="btn btn-xs btn-danger" data-href="_modul/giriscikis/tipSEG.php?islem=sil&tip_id='.$giris_cikis_tipi[ "id" ].'" data-toggle="modal" data-target="#sil_onay">Sil</button></td>';
									echo '</tr>';
									$sayi++;
								} 
							?>
						</tbody>
					</table>
					<button modul="" yetki_islem="giris_cikis_tipi_keydet" class="btn btn-outline-info col-sm-6 offset-sm-3" data-toggle="modal" data-target="#IslemTipleri"> Farklı İzin Kaydet</button>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!--Tüm İşlem Tip Listesi -->
<div class="modal fade" id="IslemTipleri"  aria-modal="true" role="dialog"  modul = 'giriscikis' yetki_islem="tipKaydet">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="_modul/giriscikis/tipSEG.php" method="post">
				<input type="hidden" name="islem" value="ekle">
				<div class="modal-header">
					<h4 class="modal-title">Giriş Çıkış - İzin Tipi Ayarlama</h4>
				</div>
				<div class="modal-body">
					<div style="display: none;" id="FirmaTipSayisi" data-tipSayi="<?php echo count($firma_giris_cikis_tipleri); ?>"> </div>
					<table id="" class="table table-bordered table-hover table-sm" width = "100%">
						<thead>
							<tr>
								<th style="width: 15px">Seçiniz</th>
								<th>Baslık</th>
								<th>Maaş Kesintisi</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$sayi = 1;
								foreach ($tum_giris_cikis_tipleri as $giris_cikis_tipi) {
									if ($giris_cikis_tipi[ "varmi" ] == null) {
										echo '<tr class="TipTr TipTr-'.$giris_cikis_tipi[ "id" ].'">';
											echo '<td class="text-center">
														<div class="form-group">
															<div class="icheck-success d-inline">
																<input type="checkbox" name = "tip_id[]" value="'.$giris_cikis_tipi[ "id" ].'" id="TipId-'.$giris_cikis_tipi[ "id" ].'">
																<label for="TipId-'.$giris_cikis_tipi[ "id" ].'">
																</label>
															</div>
														</div>
													</td>';
											echo '<td>'.$giris_cikis_tipi[ "adi" ].'</td>';
											echo '<td>
													<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-focused bootstrap-switch-animate bootstrap-switch-off" >
														<div class="bootstrap-switch-container" >
															<input type="checkbox" name="maas_kesintisi[]" checked="" value="'.$giris_cikis_tipi[ "id" ].'" data-bootstrap-switch="" data-off-color="danger" data-on-text="Kesintili" data-off-text="Kesintisiz" data-on-color="success">
														</div>
													</div>
												</td>';
										echo '</tr>';
										$sayi++;
									}
								} 
							?>
						</tbody>
					</table>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
					<button type="submit" class="btn btn-success">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>


<?php  if($islem == "saatduzenle" AND count($personel_giris_cikis)>0 AND count($personel)> 0 ){ ?>
	<div class="modal fade" id="saat_duzenle_modal"  aria-modal="true" role="dialog">
		<div class="modal-dialog ">
			<div class="modal-content">
				<form action="_modul/giriscikis/giriscikisSEG.php" method="post" >
					<input type = "hidden" name = "islem" value = "saatguncelle" >
					<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
					<div class="modal-header">
						<h4 class="modal-title">Giriş Çıkış Saati Düzenle</h4>
					</div>
					<div class="modal-body">
						<?php $i = 1; foreach ($personel_giris_cikis as $giris_cikis) { ?>
							<input type="hidden" value="<?php echo $giris_cikis["id"]; ?>" name="giriscikis_id[]">
							<div class="form-group">
								<span  modul= "giriscikis" yetki_islem="sil" data-href="_modul/giriscikis/giriscikisSEG.php?islem=sil&giriscikis_id=<?php echo $giris_cikis["id"]; ?>&personel_id=<?php echo $personel_id; ?>" data-toggle="modal" data-target="#sil_onay"  class="btn btn-xs btn-danger float-right" id="sil">Sil</span>
				                    <label>Başlangıc Saati</label>
				                    <div class="input-group date" id="timepickerBaslangic-<?php echo $i; ?>" data-target-input="nearest">
				                      <input type="text" name="baslangic_saat[]" class="form-control datetimepicker-input" data-target="#timepickerBaslangic-<?php echo $i; ?>" value="<?php echo $giris_cikis["baslangic_saat"]; ?>">
				                        <div class="input-group-append" data-target="#timepickerBaslangic-<?php echo $i; ?>" data-toggle="datetimepicker">
				                          	<div class="input-group-text"><i class="far fa-clock"></i></div>
				                        </div>
				                    </div>
				                    <!-- /.input group -->
				                </div>
	                  		<!-- /.form group -->
	                  		<div class="form-group">
			                    <label>Bitiş Saati</label>
			                    <div class="input-group date" id="timepickerBitis-<?php echo $i; ?>" data-target-input="nearest">
			                      <input type="text" name="bitis_saat[]" class="form-control datetimepicker-input" data-target="#timepickerBitis-<?php echo $i; ?>" value="<?php echo $giris_cikis["bitis_saat"]; ?>"/>
			                        <div class="input-group-append" data-target="#timepickerBitis-<?php echo $i; ?>" data-toggle="datetimepicker">
			                          	<div class="input-group-text"><i class="far fa-clock"></i></div>
			                        </div>
			                    </div>
			                    <!-- /.input group -->
			                </div>
	                  		<!-- /.form group -->
	                  		<hr>
                  		<?php $i++; } ?>
                  		
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
						<button type="submit" class="btn btn-success">Kaydet</button>
						
					</div>
				</form>
			</div>
		</div>
	</div>
<?php }else{echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";}?>
<!--Toplu Hareket Ekleme Modalı-->

<!--Firma Tip İşlemleri -->
<div class="modal fade" id="dosyaOku"  aria-modal="true" role="dialog" modul = 'giriscikis' yetki_islem="tipKaydet">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Girş Çıkışları Dosyadan Çek</h4>
				</div>
				<div class="modal-body">
					<form action="_modul/giriscikis/dosyadanCek.php" method="POST" enctype="multipart/form-data">
						<input type="file" name="file">

						<button type="submit" modul="giriscikis" yetki_islem="dosyadanCek" class="btn btn-outline-info col-sm-6 offset-sm-3">Dosyadan Çek</button>
					</form>
						
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
				</div>
			</form>
		</div>
	</div>
</div>


<div class="modal fade"  id="sil_onay">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lütfen Dikkat</h4>
			</div>
			<div class="modal-body">
				<p>Bu kaydı silmek istediğinize emin misiniz?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>

	<?php if ($islem =="saatduzenle" ) {?> $('#saat_duzenle_modal').modal( "show" ) <?php } 

	//Personel Kaç defa giriş çıkış yapmıs ise o akadar form için timepicker oluşturuyoruz
	$i = 1;
		while ($i <= count($personel_giris_cikis)) {
		echo "
			$('#timepickerBaslangic-".$i."').datetimepicker({
		      	format: 'HH:mm'
		    });

		    $('#timepickerBitis-".$i."').datetimepicker({
		      format: 'HH:mm'
		    });";
		$i++;
	}
	?>

	/* Kayıt silme onay modal açar. */
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	});

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

	$(function () {
		$('#datetimepickerGun').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});
	

	$(function () {
		$('#baslangicDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#bitisDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#toplubaslangicDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(function () {
		$('#toplubitisDateTime').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy-MM-DD HH:mm',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});
	

	$( "body" ).on('click', '#listeleBtn', function() {
		var tarih 		= $( "#tarihSec" ).val();
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
	

	var tbl_personeller = $( "#tbl_personeller" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 15,
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
		}
	} ).buttons().container().appendTo('#tbl_personeller_wrapper .col-md-6:eq(0)');

	var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 30,
		//"buttons": [ "excel", "print" ],

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
				"targets" : [],
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
		}
	} ).buttons().container().appendTo('#tbl_giriscikislar_wrapper .col-md-6:eq(0)');

	$('#card_personeller').on('maximized.lte.cardwidget', function() {
		var tbl_personeller = $( "#tbl_personeller" ).DataTable();
		var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
		column.visible( ! column.visible() );
	});

	$('#card_personeller').on('minimized.lte.cardwidget', function() {
		var tbl_personeller = $( "#tbl_personeller" ).DataTable();
		var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
		column.visible( ! column.visible() );
	} );

	$('#card_giriscikislar').on('maximized.lte.cardwidget', function() {
		var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable();
		var column = tbl_giriscikislar.column(  tbl_giriscikislar.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_giriscikislar.column(  tbl_giriscikislar.column.length - 2 );
		column.visible( ! column.visible() );
	});

	$('#card_giriscikislar').on('minimized.lte.cardwidget', function() {
		var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable();
		var column = tbl_giriscikislar.column(  tbl_giriscikislar.column.length - 1 );
		column.visible( ! column.visible() );
		var column = tbl_giriscikislar.column(  tbl_giriscikislar.column.length - 2 );
		column.visible( ! column.visible() );
	} );
</script>

