<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$aktif_tab		= array_key_exists( 'aktif_tab'		,$_REQUEST ) ? $_REQUEST[ 'aktif_tab' ]		: '_genel';
$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;


$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';


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



/* Sabit tablolar */
$SQL_gruplar = <<< SQL
SELECT
	*
FROM
	tb_gruplar
SQL;


$SQL_subeler = <<< SQL
SELECT
	*
FROM
	tb_subeler
SQL;


$SQL_bolumler = <<< SQL
SELECT
	*
FROM
	tb_bolumler
SQL;


$SQL_ozel_kod = <<< SQL
SELECT
	*
FROM
	tb_ozel_kod
SQL;


$SQL_ulkeler = <<< SQL
SELECT
	*
FROM
	tb_ulkeler
SQL;


$SQL_iller = <<< SQL
SELECT
	*
FROM
	tb_iller
SQL;


$SQL_ilceler = <<< SQL
SELECT
	*
FROM
	tb_ilceler
SQL;


$SQL_dinler = <<< SQL
SELECT
	*
FROM
	tb_dinler
SQL;


$SQL_ogrenim_duzeyleri = <<< SQL
SELECT
	*
FROM
	tb_ogrenim_duzeyleri
SQL;


$personeller				= $vt->select( $SQL_tum_personel_oku, array() );
$tek_personel				= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) )[ 2 ][ 0 ];
$personel_ozluk_dosyalari	= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) );


/* Sabit tablolar içerik oku */
$iller				= $vt->select( $SQL_iller				,array() )[ 2 ];
$dinler				= $vt->select( $SQL_dinler				,array() )[ 2 ];
$ilceler			= $vt->select( $SQL_ilceler				,array() )[ 2 ];
$gruplar			= $vt->select( $SQL_gruplar				,array() )[ 2 ];
$subeler			= $vt->select( $SQL_subeler				,array() )[ 2 ];
$ulkeler			= $vt->select( $SQL_ulkeler				,array() )[ 2 ];
$bolumler			= $vt->select( $SQL_bolumler			,array() )[ 2 ];
$ozel_kod			= $vt->select( $SQL_ozel_kod			,array() )[ 2 ];
$ogrenim_duzeyleri	= $vt->select( $SQL_ogrenim_duzeyleri	,array() )[ 2 ];

if( !count( $tek_personel ) ) $tek_personel[ 'resim' ] = 'resim_yok.jpg';

?>

<div class="modal fade" id="sil_onay">
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
	/* Kayıt silme onay modal açar. */
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>


<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="card card-secondary" id = "my-card-12">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
						<div class = "card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%" data-export-title = "Deneme">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th>Kayıt No</th>
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
								<tr <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'"; ?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'tc_no' ]; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $personel[ 'soyadi' ]; ?></td>
									<td><?php echo $personel[ 'kayit_no' ]; ?></td>
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
										<button modul= 'personel' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/personel/personelSEG.php?islem=sil&personel_id=<?php echo $personel[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card <?php if( $personel_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills">
							<?php if( $personel_id > 0 ) { ?>
								<li class="nav-item"><a class="nav-link active" href="#_genel" data-toggle="tab">Genel</a></li>
								<li class="nav-item"><a class="nav-link" href="#_nufus" data-toggle="tab" disabled>Nüfus</a></li>
								<li class="nav-item"><a class="nav-link" href="#_adres" data-toggle="tab">Adres</a></li>
								<li class="nav-item"><a class="nav-link" href="#_diger" data-toggle="tab">Diğer</a></li>
							<?php } else {
								echo "<h5>Yeni personel ekle</h5>";
							} ?>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type="file" id="gizli_input_file" name = "input_personel_resim" style = "display:none;" name = "resim" accept="image/gif, image/jpeg, image/png"  onchange="resimOnizle(this)"; />
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
									<div class="text-center">
										<img class="img-fluid img-circle img-thumbnail mw-100"
										style="width:120px;"
										src="personel_resimler/<?php echo $tek_personel[ 'resim' ] . '?_dc = ' . time(); ?>" id = "personel_resim" 
										alt="User profile picture"
										id = "personel_resim">
										<h6>Fotoğraf değiştirmek için üzerine tıklayınız</h6>
									</div>
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_personel[ "adi" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Soyadı</label>
										<input required type="text" class="form-control" name ="soyadi" value = "<?php echo $tek_personel[ "soyadi" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Kayıt No</label>
										<input required type="text" class="form-control" name ="kayit_no" value = "<?php echo $tek_personel[ "kayit_no" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Grubu</label>
										<select class="form-control" name = "grup_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = "<?php echo $grup[ 'id' ]; ?>" <?php if( $tek_personel[ 'grup_id' ] == $grup[ 'id' ] ) echo 'selected'; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Sicil No</label>
										<input required type="text" class="form-control" name ="sicil_no" value = "<?php echo $tek_personel[ "sicil_no" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">İşe Girişi</label>
										<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-ise_giris_tarihi" value="<?php echo $fn->tarihFormatiDuzelt(  $tek_personel[ "ise_giris_tarihi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">İşten Çıkışı</label>
										<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-isten_cikis_tarihi" value="<?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "isten_cikis_tarihi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker2" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Ücreti</label>
										<input required type="text" class="form-control" name ="ucret" value = "<?php echo $tek_personel[ "ucret" ]; ?>" data-inputmask="'alias': '9999.99'"  placeholder = "0000,00" >
									</div>


									<div class="form-group">
										<label class="control-label">Şube</label>
										<select class="form-control" name = "sube_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $subeler as $sube ) { ?>
												<option value="<?php echo $sube[ 'id' ]; ?>" <?php if( $tek_personel[ 'sube_id' ] == $sube[ 'id' ] ) echo 'selected'; ?>><?php echo $sube['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Bölüm</label>
										<select class="form-control" name = "bolum_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $bolumler as $bolum ) { ?>
												<option value="<?php echo $bolum[ 'id' ]; ?>" <?php if( $tek_personel[ 'bolum_id' ] == $bolum[ 'id' ] ) echo 'selected'; ?>><?php echo $bolum['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Servisi</label>
										<input required type="text" class="form-control" name ="servis" value = "<?php echo $tek_personel[ "servis" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Özel Kod1</label>
										<select class="form-control" name = "ozel_kod1_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ozel_kod as $ok1 ) { ?>
												<option value="<?php echo $ok1[ 'id' ]; ?>" <?php if( $tek_personel[ 'ozel_kod1_id' ] == $ok1[ 'id' ] ) echo 'selected'; ?>><?php echo $ok1['adi']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Özel Kod2</label>
										<select class="form-control" name = "ozel_kod2_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ozel_kod as $ok2 ) { ?>
												<option value="<?php echo $ok2[ 'id' ]; ?>" <?php if( $tek_personel[ 'ozel_kod2_id' ] == $ok2[ 'id' ] ) echo 'selected'; ?>><?php echo $ok2['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
									</div>
								</form>
							</div>

							<!-- NÜFUS BİLGİLERİ -->
							<div class="tab-pane" id="_nufus">
								<form class="form-horizontal" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
									<div class="form-group">
										<label class="control-label">TC No</label>
										<input type="text" class="form-control" name ="tc_no" value = "<?php echo $tek_personel[ "tc_no" ]; ?>" required>
									</div>
									<div class="form-group">
										<label class="control-label">Uyruğu</label>
										<select class="form-control select2" name = "uyruk_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ulkeler as $ulke ) { ?>
												<option value="<?php echo $ulke[ 'id' ]; ?>" <?php if( $tek_personel[ 'uyruk_id' ] == $ulke[ 'id' ] ) echo 'selected'; ?>><?php echo $ulke['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Cinsiyet</label>
										<select class="form-control" name = "cinsiyet" required>
										<option value="">Seçiniz</option>
											<option value = "1" <?php if( $tek_personel[ 'cinsiyet' ] == 1 ) echo 'selected'; ?> >Kadın</option>
											<option value = "2" <?php if( $tek_personel[ 'cinsiyet' ] == 2 ) echo 'selected'; ?> >Erkek</option>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Cüzdan No</label>
										<input required type="text" class="form-control" name ="cuzdan_no" value = "<?php echo $tek_personel[ "cuzdan_no" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Ana Adı</label>
										<input required type="text" class="form-control" name ="ana_adi" value = "<?php echo $tek_personel[ "ana_adi" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Baba Adı</label>
										<input required type="text" class="form-control" name ="baba_adi" value = "<?php echo $tek_personel[ "baba_adi" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Doğum Yeri</label>
										<select class="form-control select2" name = "dogum_yeri_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ilceler as $ilce ) { ?>
												<option value="<?php echo $ilce[ 'id' ]; ?>" <?php if( $tek_personel[ 'dogum_yeri_id' ] == $ilce[ 'id' ] ) echo 'selected'; ?>><?php echo $ilce['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Doğum Tarihi</label>
										<div class="input-group date" id="datetimepicker3" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker3" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-dogum_tarihi" value="<?php echo $fn->tarihFormatiDuzelt(  $tek_personel[ "dogum_tarihi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker3" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Kızlık Soyadı</label>
										<input type="text" class="form-control" name ="kizlik_soyadi" value = "<?php echo $tek_personel[ "kizlik_soyadi" ]; ?>">
									</div>
									<div class="form-group">
										<label class="control-label">Medeni Hali</label>
										<select class="form-control" name = "medeni_hali" required>
										<option value="">Seçiniz</option>
											<option value = "1" <?php if( $tek_personel[ 'medeni_hali' ] == 1 ) echo 'selected'; ?>>Evli</option>
											<option value = "2" <?php if( $tek_personel[ 'medeni_hali' ] == 2 ) echo 'selected'; ?>>Bekar</option>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Dini</label>
										<select class="form-control" name = "din_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $dinler as $din ) { ?>
												<option value="<?php echo $din[ 'id' ]; ?>" <?php if( $tek_personel[ 'din_id' ] == $din[ 'id' ] ) echo 'selected'; ?>><?php echo $din['adi']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Kan Grubu</label>
										<select class="form-control" name = "kan_grubu" required>
										<option value="">Seçiniz</option>
											<option value = "1" <?php if( $tek_personel[ 'kan_grubu' ] == 1 ) echo 'selected'; ?>>0 RH+</option>
											<option value = "2" <?php if( $tek_personel[ 'kan_grubu' ] == 2 ) echo 'selected'; ?>>0 RH-</option>
											<option value = "3" <?php if( $tek_personel[ 'kan_grubu' ] == 3 ) echo 'selected'; ?>>A RH-</option>
											<option value = "4" <?php if( $tek_personel[ 'kan_grubu' ] == 4 ) echo 'selected'; ?>>A RH+</option>
											<option value = "5" <?php if( $tek_personel[ 'kan_grubu' ] == 5 ) echo 'selected'; ?>>B RH-</option>
											<option value = "6" <?php if( $tek_personel[ 'kan_grubu' ] == 6 ) echo 'selected'; ?>>B RH+</option>
											<option value = "7" <?php if( $tek_personel[ 'kan_grubu' ] == 7 ) echo 'selected'; ?>>AB RH-</option>
											<option value = "8" <?php if( $tek_personel[ 'kan_grubu' ] == 8 ) echo 'selected'; ?>>AB RH+</option>
										</select>
									</div>


									<div class="form-group">
										<label class="control-label">Eğitimi</label>
										<select class="form-control" name = "ogrenim_duzeyi_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ogrenim_duzeyleri as $ogrenim_duzeyi ) { ?>
												<option value="<?php echo $ogrenim_duzeyi[ 'id' ]; ?>" <?php if( $tek_personel[ 'ogrenim_duzeyi_id' ] == $ogrenim_duzeyi[ 'id' ] ) echo 'selected'; ?>><?php echo $ogrenim_duzeyi['adi']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">İl</label>
										<select class="form-control" name = "il_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $iller as $il ) { ?>
												<option value="<?php echo $il[ 'id' ]; ?>" <?php if( $tek_personel[ 'il_id' ] == $il[ 'id' ] ) echo 'selected'; ?>><?php echo $il['adi']; ?></option>
											<?php } ?>
										</select>
									</div>


									<div class="form-group">
										<label class="control-label">İlçe</label>
										<select class="form-control" name = "ilce_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $ilceler as $ilce ) { ?>
												<option value="<?php echo $ilce[ 'id' ]; ?>" <?php if( $tek_personel[ 'ilce_id' ] == $ilce[ 'id' ] ) echo 'selected'; ?>><?php echo $ilce['adi']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Mahalle</label>
										<input required type="text" class="form-control" name ="mahalle" value = "<?php echo $tek_personel[ "mahalle" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Cilt</label>
										<input required type="text" class="form-control" name ="cilt" value = "<?php echo $tek_personel[ "cilt" ]; ?>">
									</div>


									<div class="form-group">
										<label class="control-label">Aile</label>
										<input required type="text" class="form-control" name ="aile" value = "<?php echo $tek_personel[ "aile" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Sıra</label>
										<input required type="text" class="form-control" name ="sira" value = "<?php echo $tek_personel[ "sira" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Verildiği Yer</label>
										<input required type="text" class="form-control" name ="verildigi_yer" value = "<?php echo $tek_personel[ "verildigi_yer" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Nedeni</label>
										<input required type="text" class="form-control" name ="verilis_nedeni" value = "<?php echo $tek_personel[ "verilis_nedeni" ]; ?>">
									</div>
									
									<div class="form-group">
										<label class="control-label">Veriliş Tarihi</label>
										<div class="input-group date" id="datetimepicker4" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker4" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete = "off" type="text" name="tarihalani-verilis_tarihi" value="<?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "verilis_tarihi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker4" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
									</div>
								</form>
							</div>

							<!-- ADRES BİLGİLERİ -->
							<div class="tab-pane" id="_adres">
								<form class="form-horizontal" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
									<div class="form-group">
										<label class="control-label">Adres</label>
										<textarea class="form-control" name ="adres" ><?php echo $tek_personel[ "adres" ]; ?></textarea>
									</div>
									<div class="form-group">
										<label class="control-label">Sabit Telefon</label>
										<input required="" type="text" name="sabit_telefon" value="<?php echo $tek_personel[ "sabit_telefon" ]; ?>" class="form-control" data-inputmask="&quot;mask&quot;: &quot;0(999) 999-9999&quot;" data-mask="" inputmode="text">
									</div>
									<div class="form-group">
										<label class="control-label">Gsm</label>
										<input required="" type="text" name="mobil_telefon" value="<?php echo $tek_personel[ "mobil_telefon" ]; ?>" class="form-control" data-inputmask="&quot;mask&quot;: &quot;0(999) 999-9999&quot;" data-mask="" inputmode="text">
									</div>

									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
									</div>
								</form>
							</div>

							<!-- DİĞER BİLGİLER -->
							<div class="tab-pane" id="_diger">
								<form class="form-horizontal" action = "_modul/personel/personelSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
									<div class="form-group">
										<label class="control-label">Sigorta No</label>
										<input required type="text" class="form-control" name ="sigorta_no" value = "<?php echo $tek_personel[ "sigorta_no" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Sigorta Başı</label>
										<div class="input-group date" id="datetimepicker5" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker5" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-sigarta_basi" value="<?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "sigarta_basi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker5" data-toggle="datetimepicker"/>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Sigorta Sonu</label>
										<div class="input-group date" id="datetimepicker6" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker6" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-sigorta_sonu" value="<?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "sigorta_sonu" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker6" data-toggle="datetimepicker"/>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Ek Grub</label>
										<select class="form-control" name = "ek_grup_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value="<?php echo $grup[ 'id' ]; ?>" <?php if( $tek_personel[ 'ek_grup_id' ] == $grup[ 'id' ] ) echo 'selected'; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									
									<div class="form-group">
										<label class="control-label">Diğer Ödeme</label>
										<input required type="text" class="form-control" name ="diger_odeme" data-inputmask="'alias': '9999.99'"  value = "<?php echo $tek_personel[ "diger_odeme" ]; ?>" placeholder = "0000.00">
									</div>
									
									<div class="form-group">
										<label class="control-label">Günlük Ödeme</label>
										<input required type="text" class="form-control" name ="gunluk_odeme" data-inputmask="'alias': '9999.99'"  value = "<?php echo $tek_personel[ "gunluk_odeme" ]; ?>" placeholder = "0000.00">
									</div>
									
									<div class="form-group">
										<label class="control-label">Aylık Ek Ödeme</label>
										<input required type="text" class="form-control" name ="aylik_ek_odeme" data-inputmask="'alias': '9999.99'"  value = "<?php echo $tek_personel[ "aylik_ek_odeme" ]; ?>" placeholder = "0000.00">
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Banka Şube No</label>
												<input type="text" name = "banka_sube" class="form-control" value = "<?php echo $tek_personel[ "banka_sube" ]; ?>" >
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Banka Hesap No</label>
												<input type="text" name = "banka_hesap_no" class="form-control" value = "<?php echo $tek_personel[ "banka_hesap_no" ]; ?>" >
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label">Kart No</label>
										<input autocomplete="off" data-inputmask="&quot;mask&quot;: &quot;(9999) (9999) (9999) (9999)&quot;" required type="text" class="form-control" name ="kart_no" value = "<?php echo $tek_personel[ "kart_no" ]; ?>" >
									</div>
									

									<div class="form-group">
										<label class="control-label">İzin Başlama Tarihi</label>
										<div class="input-group date" id="datetimepicker7" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker7" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-izin_baslama_tarihi" value="<?php echo $fn->tarihFormatiDuzelt( $tek_personel[ "izin_baslama_tarihi" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker7" data-toggle="datetimepicker"/>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label">Kalan İzin</label>
										<input required type="number" class="form-control" name ="kalan_izin" value = "<?php echo $tek_personel[ "kalan_izin" ]; ?>">
									</div>

									<div class="form-group">
										<label class="control-label">Ödenen İzin</label>
										<input required type="number" class="form-control" name ="odenen_izin" value = "<?php echo $tek_personel[ "odenen_izin" ]; ?>">
									</div>

									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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
	$( "#personel_resim" ).click( function() {
		$( "#gizli_input_file" ).trigger( 'click' );
	});
});

/* Seçilen resim önizle */
function resimOnizle( input ) {
	if ( input.files && input.files[ 0 ] ) {
		var reader = new FileReader();
		reader.onload = function ( e ) {
			$( '#personel_resim' ).attr( 'src', e.target.result );
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
			"targets" : [ 1,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50 ],
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