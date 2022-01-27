<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]		: 'ekle';
$aktif_tab		= array_key_exists( 'aktif_tab'		,$_REQUEST ) ? $_REQUEST[ 'aktif_tab' ]	: '_genel';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.id = ? AND p.aktif = 1
SQL;

$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 ot.adi
	,od.dosya
FROM
	tb_personel_ozluk_dosyalari AS od
JOIN
	tb_personel_ozluk_dosya_turleri AS ot ON od.dosya_turu_id = ot.id
WHERE
	od.personel_id = ?
SQL;


$SQL_personel_detaylar = <<< SQL
SELECT
	 p.*
	,s.adi AS firma_adi
	,b.adi AS bolum_adi
	,b.id AS bolum_id
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS s ON p.firma_id = s.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.id = ?
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array() );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) );
$personel_ozluk_dosyalari	= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) );
$personel_detaylar			= $vt->select( $SQL_personel_detaylar, array( $personel_id ) );

/*
id
adi
soyadi
kayit_no
grup_id
sicil_no
ise_giris_tarihi
isten_cikis_tarihi
ucret
sube_id
bolum_id
servis
ozel_kod1_id
ozel_kod2_id


tc_no
uyruk_id
cinsiyet
cuzdan_no
baba_adi
ana_adi
dogum_yeri_id
dogum_tarihi
kizlik_soyadi
medeni_hali
dini
egitim_id
il_id
ilce_id
mahalle
cilt
aile
sira
verilis_nedeni
verilis_tarihi
verildigi_yer


adres
sabit_telefon
mobil_telefon
sigorta_no
sigarta_basi
sigorta_sonu
ek_grup_id
diger_odeme
gunluk_odeme
aylik_ek_odeme
banka_sube
banka_hesap_no
kart_no
izin_baslama_tarihi
kalan_izin
odenen_izin


*/
$secilen_personel_bilgileri = array(
	 'id'							=> $personel_id > 0 ? $personel_id												: $personeller[ 2 ][ 0 ][ 'id' ]
	,'firma_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_id' ]						: $personeller[ 2 ][ 0 ][ 'firma_id' ]
	,'firma_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_adi' ]					: $personeller[ 2 ][ 0 ][ 'firma_adi' ]
	,'bolum_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_id' ]						: $personeller[ 2 ][ 0 ][ 'bolum_id' ]
	,'bolum_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_adi' ]					: $personeller[ 2 ][ 0 ][ 'bolum_adi' ]
	,'tc_no'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'tc_no' ]						: $personeller[ 2 ][ 0 ][ 'tc_no' ]
	,'adi'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'adi' ]							: $personeller[ 2 ][ 0 ][ 'adi' ]
	,'soyadi'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'soyadi' ]						: $personeller[ 2 ][ 0 ][ 'soyadi' ]
	,'ise_giris_tarihi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ise_giris_tarihi' ]				: $personeller[ 2 ][ 0 ][ 'ise_giris_tarihi' ]
	,'isten_cikis_tarihi'			=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'isten_cikis_tarihi' ]			: $personeller[ 2 ][ 0 ][ 'isten_cikis_tarihi' ]
	,'sgk_kanun_no_id'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'sgk_kanun_no_id' ]				: $personeller[ 2 ][ 0 ][ 'sgk_kanun_no_id' ]
	,'sgk_kanun_no_adi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'sgk_kanun_no_adi' ]				: $personeller[ 2 ][ 0 ][ 'sgk_kanun_no_adi' ]
	,'ucret'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ucret' ]						: $personeller[ 2 ][ 0 ][ 'ucret' ]
	,'calisma_gunu'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'calisma_gunu' ]					: $personeller[ 2 ][ 0 ][ 'calisma_gunu' ]
	,'hakedis_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'hakedis_saati' ]				: $personeller[ 2 ][ 0 ][ 'hakedis_saati' ]
	,'agi'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'agi' ]							: $personeller[ 2 ][ 0 ][ 'agi' ]
	,'normal_calisma_tutari'		=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'normal_calisma_tutari' ]		: $personeller[ 2 ][ 0 ][ 'normal_calisma_tutari' ]
	,'yuzde_50_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'yuzde_50_saati' ]				: $personeller[ 2 ][ 0 ][ 'yuzde_50_saati' ]
	,'yuzde_100_saati'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'yuzde_100_saati' ]				: $personeller[ 2 ][ 0 ][ 'yuzde_100_saati' ]
	,'ikinci_fazla_mesai_odemesi'	=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ikinci_fazla_mesai_odemesi' ]	: $personeller[ 2 ][ 0 ][ 'ikinci_fazla_mesai_odemesi' ]
	,'mesai_kazanci'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'mesai_kazanci' ]				: $personeller[ 2 ][ 0 ][ 'mesai_kazanci' ]
	,'toplam_kesinti_saati'			=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'toplam_kesinti_saati' ]			: $personeller[ 2 ][ 0 ][ 'toplam_kesinti_saati' ]
	,'toplam_gelmeme_kesintisi'		=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'toplam_gelmeme_kesintisi' ]		: $personeller[ 2 ][ 0 ][ 'toplam_gelmeme_kesintisi' ]
	,'hesaplama_hatasi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'hesaplama_hatasi' ]				: $personeller[ 2 ][ 0 ][ 'hesaplama_hatasi' ]
	,'bankaya_odenen'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bankaya_odenen' ]				: $personeller[ 2 ][ 0 ][ 'bankaya_odenen' ]
	,'bes'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bes' ]							: $personeller[ 2 ][ 0 ][ 'bes' ]
	,'avans_toplami'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'avans_toplami' ]				: $personeller[ 2 ][ 0 ][ 'avans_toplami' ]
	,'borc_tutari'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'borc_tutari' ]					: $personeller[ 2 ][ 0 ][ 'borc_tutari' ]
	,'odeme_tutari'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'odeme_tutari' ]					: $personeller[ 2 ][ 0 ][ 'odeme_tutari' ]
	,'iskur_odemesi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'iskur_odemesi' ]				: $personeller[ 2 ][ 0 ][ 'iskur_odemesi' ]
	,'personel_resim'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'personel_resim' ]				: $personeller[ 2 ][ 0 ][ 'personel_resim' ]
);

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
					<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
					</div>
					<div class="card-body">
						<table id="example2" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th>SGK Nanun No</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $personeller[ 2 ] AS $personel ) { ?>
								<tr>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'tc_no' ]; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $personel[ 'soyadi' ]; ?></td>
									<td><?php echo $personel[ 'sgk_kanun_no_adi' ]; ?></td>
									<td align = "center">
										<a modul = 'personel' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=personelEkle&islem=guncelle&personel_id=<?php echo $personel[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul = 'personel' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/personelEkle/personelEkleSEG.php?islem=sil&personel_id=<?php echo $personel[ 'id' ]; ?>" data-toggle="modal" data-target="#kayit_sil" >Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-header p-2">
						<ul class="nav nav-pills">
						<li class="nav-item"><a class="nav-link <?php if( $aktif_tab == '_genel' ) echo 'active'; ?>" href="#_genel" data-toggle="tab">Genel</a></li>
						<li class="nav-item"><a class="nav-link <?php if( $aktif_tab == '_nufus' ) echo 'active'; ?>" href="#_nufus" data-toggle="tab">Nüfus</a></li>
						<li class="nav-item"><a class="nav-link <?php if( $aktif_tab == '_adres' ) echo 'active'; ?>" href="#_adres" data-toggle="tab">Adres</a></li>
						<li class="nav-item"><a class="nav-link <?php if( $aktif_tab == '_diger' ) echo 'active'; ?>" href="#_diger" data-toggle="tab">Diğer</a></li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane <?php if( $aktif_tab == '_genel' ) echo 'active'; ?>" id="_genel">
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/uyeler/uyelerSEG.php" method = "POST" enctype="multipart/form-data">
									<input type="file" id="gizli_input_file" name = "input_sistem_kullanici_resim" style = "display:none;" name = "resim" accept="image/gif, image/jpeg, image/png"  onchange="resimOnizle(this)"; />
									<input type = "hidden" name = "id" value = "0" >
									<input type = "hidden" name = "islem" value = "ekle" >
									<input type = "hidden" name = "form_turu" value = "genel_bilgiler">
									<input type = "hidden" name = "uye_id" value = "0">
									<div class="text-center">
										<img class="img-fluid img-circle img-thumbnail mw-100"
										style="width:120px;"
										src="resimler/resim_yok.jpg" id = "sistem_kullanici_resim" 
										alt="User profile picture"
										id = "sistem_kullanici_resim">
										<h6>Fotoğraf değiştirmek için üzerine tıklayınız</h6>
									</div>
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Soyadı</label>
										<input required type="text" class="form-control" name ="soyadi" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Kayıt No</label>
										<input required type="text" class="form-control" name ="kayit_no" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Grubu</label>
										<select class="form-control" name = "grup_id" required>
											<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Sicil No</label>
										<input required type="text" class="form-control" name ="sicil_no" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">İşe Girişi</label>
										<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="ise_giris_tarihi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">İşten Çıkışı</label>
										<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="isten_cikis_tarihi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker2" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Ücreti</label>
										<input required type="text" class="form-control" name ="ucret" value = "">
									</div>


									<div class="form-group">
										<label class="control-label">Şube</label>
										<select class="form-control" name = "sube_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Bölüm</label>
										<select class="form-control" name = "bolum_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Servisi</label>
										<input required type="text" class="form-control" name ="servis" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Özel Kod1</label>
										<select class="form-control" name = "ozel_kod1_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Özel Kod2</label>
										<select class="form-control" name = "ozel_kod2_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>
									<div class="card-footer">
										<button modul= 'uyeler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
										<a href = "?modul=personel&islem=ekle" class="btn btn-default btn-sm pull-right"><span class="fa fa-refresh"></span> Temizle/Yeni Kayıt</a>
									</div>
								</form>
							</div>

							<!-- NÜFUS BİLGİLERİ -->
							<div class="tab-pane <?php if( $aktif_tab == '_nufus' ) echo 'active'; ?>" id="_nufus">
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/uyeler/uyelerSEG.php" method = "POST" enctype="multipart/form-data">
									<div class="form-group">
										<label class="control-label">TC No</label>
										<input required type="text" class="form-control" name ="tc_no" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Uyruğu</label>
										<select class="form-control" name = "ogrenim_duzeyi_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Cinsiyet</label>
										<select class="form-control" name = "cinsiyet" required>
										<option value="">Seçiniz</option>
											<option value = "1" >Kadın</option>
											<option value = "2" >Erkek</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Cüzdan No</label>
										<input required type="text" class="form-control" name ="cuzdan_no" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Ana Adı</label>
										<input required type="text" class="form-control" name ="ana_adi" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Baba Adı</label>
										<input required type="text" class="form-control" name ="baba_adi" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Doğum Yeri</label>
										<select class="form-control" name = "dogum_yeri_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >Kadın</option>
											<option value = "2" >Erkek</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Doğum Tarihi</label>
										<div class="input-group date" id="datetimepicker3" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker3" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="dogum_tarihi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker3" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Kızlık Soyadı</label>
										<input required type="text" class="form-control" name ="kizlik_soyadi" value = "">
									</div>
									<div class="form-group">
										<label class="control-label">Medeni Hali</label>
										<select class="form-control" name = "medeni_hali" required>
										<option value="">Seçiniz</option>
											<option value = "1" >Evli</option>
											<option value = "2" >Bekar</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Dini</label>
										<select class="form-control" name = "dini" required>
											<option value="">Seçiniz</option>
											<option value = "1" >İslam</option>
											<option value = "2" >Hristiyan</option>
											<option value = "3" >Musevi</option>
											<option value = "4" >Budist</option>
											<option value = "5" >Ateist</option>
											<option value = "6" >Deist</option>
											<option value = "7" >Yok</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Kan Grubu</label>
										<select class="form-control" name = "ogrenim_duzeyi_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >0 RH+</option>
											<option value = "2" >0 RH-</option>
											<option value = "3" >A RH-</option>
											<option value = "4" >A RH+</option>
											<option value = "5" >B RH-</option>
											<option value = "6" >B RH+</option>
											<option value = "7" >AB RH-</option>
											<option value = "8" >AB RH+</option>
										</select>
									</div>


									<div class="form-group">
										<label class="control-label">Eğitimi</label>
										<select class="form-control" name = "egitim_id" required>
											<option value="">Seçiniz</option>

										</select>
									</div>

									<div class="form-group">
										<label class="control-label">İl</label>
										<select class="form-control" name = "il_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >0 RH+</option>
											<option value = "2" >0 RH-</option>
											<option value = "3" >A RH-</option>
											<option value = "4" >A RH+</option>
											<option value = "5" >B RH-</option>
											<option value = "6" >B RH+</option>
											<option value = "7" >AB RH-</option>
											<option value = "8" >AB RH+</option>
										</select>
									</div>


									<div class="form-group">
										<label class="control-label">İlçe</label>
										<select class="form-control" name = "ilce_id" required>
										<option value="">Seçiniz</option>
											<option value = "1" >0 RH+</option>
											<option value = "2" >0 RH-</option>
											<option value = "3" >A RH-</option>
											<option value = "4" >A RH+</option>
											<option value = "5" >B RH-</option>
											<option value = "6" >B RH+</option>
											<option value = "7" >AB RH-</option>
											<option value = "8" >AB RH+</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Mahalle</label>
										<input required type="text" class="form-control" name ="mahalle" value = "">
									</div>

									<div class="form-group">
										<label class="control-label">Cilt</label>
										<input required type="text" class="form-control" name ="cilt" value = "">
									</div>


									<div class="form-group">
										<label class="control-label">Aile</label>
										<input required type="text" class="form-control" name ="aile" value = "">
									</div>

									<div class="form-group">
										<label class="control-label">Sıra</label>
										<input required type="text" class="form-control" name ="sira" value = "">
									</div>

									<div class="form-group">
										<label class="control-label">Verildiği Yer</label>
										<input required type="text" class="form-control" name ="verildigi_yer" value = "">
									</div>

									<div class="form-group">
										<label class="control-label">Nedeni</label>
										<input required type="text" class="form-control" name ="verilis_nedeni" value = "">
									</div>
									
									<div class="form-group">
										<label class="control-label">Veriliş Tarihi</label>
										<div class="input-group date" id="datetimepicker4" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker4" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input type="text" name="verilis_tarihi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker4" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="card-footer">
										<button modul= 'uyeler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
										<a href = "?modul=personel&islem=ekle" class="btn btn-default btn-sm pull-right"><span class="fa fa-refresh"></span> Temizle/Yeni Kayıt</a>
									</div>
								</form>
							</div>

							<!-- ADRES BİLGİLERİ -->
							<div class="tab-pane <?php if( $aktif_tab == '_adres' ) echo 'active'; ?>" id="_adres">
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/uyeler/uyelerSEG.php" method = "POST" enctype="multipart/form-data">
									<div class="form-group">
										<label class="control-label">Adres</label>
										<textarea class="form-control" name ="adres" value = ""></textarea>
									</div>
									<div class="form-group">
										<label class="control-label">Sabit Telefon</label>
										<input required="" type="text" name="sabit_telefon" value="" class="form-control" data-inputmask="&quot;mask&quot;: &quot;0(999) 999-9999&quot;" data-mask="" inputmode="text">
									</div>
									<div class="form-group">
										<label class="control-label">Gsm</label>
										<input required="" type="text" name="mobil_telefon" value="" class="form-control" data-inputmask="&quot;mask&quot;: &quot;0(999) 999-9999&quot;" data-mask="" inputmode="text">
									</div>

									<div class="card-footer">
										<button modul= 'uyeler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
										<a href = "?modul=personel&islem=ekle" class="btn btn-default btn-sm pull-right"><span class="fa fa-refresh"></span> Temizle/Yeni Kayıt</a>
									</div>
								</form>
							</div>

							<!-- DİĞER BİLGİLER -->
							<div class="tab-pane <?php if( $aktif_tab == '_diger' ) echo 'active'; ?>" id="_diger">
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/uyeler/uyelerSEG.php" method = "POST" enctype="multipart/form-data">
									
									<div class="form-group">
										<label class="control-label">Sigorta No</label>
										<input required type="text" class="form-control" name ="sigorta_no" value = "">
									</div>

									<div class="form-group">
										<label class="control-label">Sigorta Başı</label>
										<div class="input-group date" id="datetimepicker5" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker5" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="sigarta_basi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker5" data-toggle="datetimepicker"/>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Sigorta Sonu</label>
										<div class="input-group date" id="datetimepicker6" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker6" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="sigorta_sonu" value="" class="form-control datetimepicker-input" data-target="#datetimepicker6" data-toggle="datetimepicker"/>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Ek Grub</label>
										<select class="form-control" name = "ek_grup_id" required>
											<option value="">Seçiniz</option>
											<option value = "1" >İlkokul</option>
											<option value = "2" >Ortaokul</option>
											<option value = "3" >Lise</option>
											<option value = "4" >Ön Lisans</option>
											<option value = "5" >Lisans</option>
											<option value = "6" >Yüksek Lisans</option>
											<option value = "7" >Doktora</option>
										</select>
									</div>
									
									<div class="form-group">
										<label class="control-label">Diğer Ödeme</label>
										<input required type="number" class="form-control" name ="diger_odeme" value = "" placeholder = "000,00">
									</div>
									
									<div class="form-group">
										<label class="control-label">Günlük Ödeme</label>
										<input required type="number" class="form-control" name ="gunluk_odeme" value = "" placeholder = "000,00">
									</div>
									
									<div class="form-group">
										<label class="control-label">Aylık Ek Ödeme</label>
										<input required type="number" class="form-control" name ="aylik_ek_odeme" value = "" placeholder = "000,00">
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Banka Şube No</label>
												<input type="text" name = "banka_sube" class="form-control" >
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Banka Hesap No</label>
												<input type="text" name = "banka_hesap_no" class="form-control" >
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Kart No</label>
										<input autocomplete="off" data-inputmask="&quot;mask&quot;: &quot;(9999) (9999) (9999) (9999)&quot;" required type="text" class="form-control" name ="kart_no" value = "" >
									</div>
									

									<div class="form-group">
										<label class="control-label">İzin Başlama Tarihi</label>
										<div class="input-group date" id="datetimepicker7" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker7" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="izin_baslama_tarihi" value="" class="form-control datetimepicker-input" data-target="#datetimepicker7" data-toggle="datetimepicker"/>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label">Kalan İzin</label>
										<input required type="number" class="form-control" name ="kalan_izin" value = "0">
									</div>

									<div class="form-group">
										<label class="control-label">Ödenen İzin</label>
										<input required type="number" class="form-control" name ="odenen_izin" value = "0">
									</div>

									<div class="card-footer">
										<button modul= 'uyeler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
										<a href = "?modul=personel&islem=ekle" class="btn btn-default btn-sm pull-right"><span class="fa fa-refresh"></span> Temizle/Yeni Kayıt</a>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">

/* Kullanıcı resmine tıklayınca file nesnesini tetikle*/
$( function() {
	$( "#sistem_kullanici_resim" ).click( function() {
		$( "#gizli_input_file" ).trigger( 'click' );
	});
});

/* Seçilen resim önizle */
function resimOnizle( input ) {
	if ( input.files && input.files[ 0 ] ) {
		var reader = new FileReader();
		reader.onload = function ( e ) {
			$( '#sistem_kullanici_resim' ).attr( 'src', e.target.result );
		};
		reader.readAsDataURL( input.files[ 0 ] );
	}
}

var simdi = new Date(); 
//var simdi="11/25/2015 15:58";
$(function () {
	$('#datetimepicker1').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker2').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker3').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker4').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker5').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker6').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#datetimepicker7').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

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

	/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
	$( window ).on( 'resize', function() {
		$('.form-group').each(function() {
			var formGroup = $( this ),
				formgroupWidth = formGroup.outerWidth();
			formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
		});
	} );
	
	/* Slect2 nesnesinin sayfanın genişliğine göre otomatik uzayıp kısalmasını sağlar*/
	$( window ).on( 'resize', function() {
		$('.description-block').each(function() {
			var formGroup = $( this ),
				formgroupWidth = formGroup.outerWidth();
			formGroup.find( '.select2-container' ).css( 'width', formgroupWidth );
		});
	} );
	
	
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	});

</script>