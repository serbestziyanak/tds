<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$aktif_tab		= array_key_exists( 'aktif_tab'		,$_REQUEST ) ? $_REQUEST[ 'aktif_tab' ]		: '_genel';
$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
	,ok1.adi AS ozel_kod1
	,ok2.adi AS ozel_kod2
	,ek_g.adi AS ek_grup_adi
	,d.adi AS din_adi
	,u.adi AS uyruk_adi
	,dy.adi AS dogum_yeri_adi
	,od.adi AS ogrenim_duzeyi_adi
	,il.adi AS il_adi
	,ilc.adi AS ilce_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN tb_ozel_kod AS ok1 ON p.ozel_kod1_id = ok1.id
LEFT JOIN tb_ozel_kod AS ok2 ON p.ozel_kod2_id = ok2.id
LEFT JOIN tb_gruplar AS ek_g ON p.ek_grup_id = ek_g.id
LEFT JOIN tb_dinler AS d ON p.din_id = d.id
LEFT JOIN tb_ulkeler AS u ON p.uyruk_id = u.id
LEFT JOIN tb_ilceler AS dy ON p.dogum_yeri_id = dy.id
LEFT JOIN tb_ogrenim_duzeyleri AS od ON p.ogrenim_duzeyi_id = od.id
LEFT JOIN tb_iller AS il ON p.il_id = il.id
LEFT JOIN tb_ilceler AS ilc ON p.ilce_id = ilc.id
WHERE
	p.aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
	,g.adi AS grup_adi
	,s.adi AS sube_adi
	,b.adi AS bolum_adi
	,ok1.adi AS ozel_kod1
	,ok2.adi AS ozel_kod2
	,ek_g.adi AS ek_grup_adi
	,d.adi AS din_adi
	,u.adi AS uyruk_adi
	,dy.adi AS dogum_yeri_adi
	,od.adi AS ogrenim_duzeyi_adi
	,il.adi AS il_adi
	,ilc.adi AS ilce_adi
FROM
	tb_personel AS p
LEFT JOIN tb_gruplar AS g ON p.grup_id = g.id
LEFT JOIN tb_subeler AS s ON p.sube_id = s.id
LEFT JOIN tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN tb_ozel_kod AS ok1 ON p.ozel_kod1_id = ok1.id
LEFT JOIN tb_ozel_kod AS ok2 ON p.ozel_kod2_id = ok2.id
LEFT JOIN tb_gruplar AS ek_g ON p.ek_grup_id = ek_g.id
LEFT JOIN tb_dinler AS d ON p.din_id = d.id
LEFT JOIN tb_ulkeler AS u ON p.uyruk_id = u.id
LEFT JOIN tb_ilceler AS dy ON p.dogum_yeri_id = dy.id
LEFT JOIN tb_ogrenim_duzeyleri AS od ON p.ogrenim_duzeyi_id = od.id
LEFT JOIN tb_iller AS il ON p.il_id = il.id
LEFT JOIN tb_ilceler AS ilc ON p.ilce_id = ilc.id
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
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) );
$personel_ozluk_dosyalari	= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) );

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="card card-secondary" id = "my-card-12">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
						<div class = "card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-striped" width = "100%" data-export-title = "Deneme">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th>Grubu</th>
									<th>Sicil No</th>
									<th>İşe Giriş Trh</th>
									<th>İşten Çıkış Trh</th>
									<th>Ücret</th>
									<th>Şubesi</th>
									<th>Bölümü</th>
									<th>Servis</th>
									<th>Özel Kod1</th>
									<th>Özel Kod2</th>
									<th>Uyruğu</th>
									<th>Cinsiyeti</th>
									<th>Cüzdan No</th>
									<th>Baba Adı</th>
									<th>Ana Adı</th>
									<th>Doğum Yeri</th>
									<th>Doğum Tarihi</th>
									<th>Kızlık Soyadı</th>
									<th>Medeni Durumu</th>
									<th>Dini</th>
									<th>Öğrenim Düzeyi</th>
									<th>İl</th>
									<th>İlçe</th>
									<th>Mahalle</th>
									<th>Cilt</th>
									<th>Aile</th>
									<th>Sıra</th>
									<th>Veriliş Nedeni</th>
									<th>Veriliş Tarihi</th>
									<th>Verildiği Yer</th>
									<th>Adres</th>
									<th>Telefon</th>
									<th>Gsm</th>
									<th>Sigorta No</th>
									<th>Sigorta Başı</th>
									<th>Sigorta Sonu</th>
									<th>Ek Grubu</th>
									<th>Diğer Ödeme</th>
									<th>Günlük Ödeme</th>
									<th>Aylık Ek Ödeme</th>
									<th>Banka Şube</th>
									<th>Banka Hesap</th>
									<th>Kart No</th>
									<th>İzin Başlama Tarihi</th>
									<th>Kalan İzin</th>
									<th>Ödenen İzin</th>
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
									<td><?php echo $personel[ 'grup_adi' ]; ?></td>
									<td><?php echo $personel[ 'sicil_no' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'ise_giris_tarihi' ] ); ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'isten_cikis_tarihi' ] ); ?></td>
									<td><?php echo $personel[ 'ucret' ]; ?></td>
									<td><?php echo $personel[ 'sube_adi' ]; ?></td>
									<td><?php echo $personel[ 'bolum_adi' ]; ?></td>
									<td><?php echo $personel[ 'servis' ]; ?></td>
									<td><?php echo $personel[ 'ozel_kod1' ]; ?></td>
									<td><?php echo $personel[ 'ozel_kod2' ]; ?></td>
									<td><?php echo $personel[ 'uyruk_adi' ]; ?></td>
									<td><?php echo $personel[ 'cinsiyet' ]; ?></td>
									<td><?php echo $personel[ 'cuzdan_no' ]; ?></td>
									<td><?php echo $personel[ 'baba_adi' ]; ?></td>
									<td><?php echo $personel[ 'ana_adi' ]; ?></td>
									<td><?php echo $personel[ 'dogum_yeri_adi' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'dogum_tarihi' ] ); ?></td>
									<td><?php echo $personel[ 'kizlik_soyadi' ]; ?></td>
									<td><?php echo $personel[ 'medeni_hali' ]; ?></td>
									<td><?php echo $personel[ 'din_adi' ]; ?></td>
									<td><?php echo $personel[ 'ogrenim_duzeyi_adi' ]; ?></td>
									<td><?php echo $personel[ 'il_adi' ]; ?></td>
									<td><?php echo $personel[ 'ilce_adi' ]; ?></td>
									<td><?php echo $personel[ 'mahalle' ]; ?></td>
									<td><?php echo $personel[ 'cilt' ]; ?></td>
									<td><?php echo $personel[ 'aile' ]; ?></td>
									<td><?php echo $personel[ 'sira' ]; ?></td>
									<td><?php echo $personel[ 'verilis_nedeni' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'verilis_tarihi' ] ); ?></td>
									<td><?php echo $personel[ 'verildigi_yer' ]; ?></td>
									<td><?php echo $personel[ 'adres' ]; ?></td>
									<td><?php echo $personel[ 'sabit_telefon' ]; ?></td>
									<td><?php echo $personel[ 'mobil_telefon' ]; ?></td>
									<td><?php echo $personel[ 'sigorta_no' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'sigarta_basi' ] ); ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'sigorta_sonu' ] ); ?></td>
									<td><?php echo $personel[ 'ek_grup_adi' ]; ?></td>
									<td><?php echo $personel[ 'diger_odeme' ]; ?></td>
									<td><?php echo $personel[ 'gunluk_odeme' ]; ?></td>
									<td><?php echo $personel[ 'aylik_ek_odeme' ]; ?></td>
									<td><?php echo $personel[ 'banka_sube' ]; ?></td>
									<td><?php echo $personel[ 'banka_hesap_no' ]; ?></td>
									<td><?php echo $personel[ 'kart_no' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $personel[ 'izin_baslama_tarihi' ] ); ?></td>
									<td><?php echo $personel[ 'kalan_izin' ]; ?></td>
									<td><?php echo $personel[ 'odenen_izin' ]; ?></td>
									<td align = "center">
										<a modul = 'personel' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=personel&islem=guncelle&personel_id=<?php echo $personel[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul = 'personel' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/personel/personelSEG.php?islem=sil&personel_id=<?php echo $personel[ 'id' ]; ?>" data-toggle="modal" data-target="#kayit_sil" >Sil</button>
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
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type="file" id="gizli_input_file" name = "input_sistem_kullanici_resim" style = "display:none;" name = "resim" accept="image/gif, image/jpeg, image/png"  onchange="resimOnizle(this)"; />
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
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
										<input required type="text" class="form-control" name ="ucret" value = "" data-inputmask="'alias': '9999,99'"  placeholder = "₺0000,00" >
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
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
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
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
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
								<form class="form-horizontal" id = "kayit_formu" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
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


$( "#tbl_personeller" ).DataTable( {
	"responsive": true, "lengthChange": false, "autoWidth": false,
	//"buttons": ["excel", "pdf", "print","colvis"],

	buttons : [
		{
			extend	: 'colvis',
			text	: "Seçiniz"
			
		},
		{
			extend	: 'excel',
			text 	: 'Excel',
			exportOptions: {
				columns: ':visible'
			},
			title: function(){
				return "Personel Listesi";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "Personel Listesi";
			}
		}
	],
	"columnDefs": [
		{
			"targets" : [ 1,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49 ],
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
} ).buttons().container().appendTo('#tbl_personeller_wrapper .col-md-6:eq(0)');

</script>