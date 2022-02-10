<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem			= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : 0;

$SQL_tum_personel_oku =<<< SQL
SELECT
	 p.*
	,f.adi AS firma_adi
	,f.id AS firma_id
	,b.id AS bolum_id
	,b.adi AS bolum_adi
	,sgk.id AS sgk_kanun_no_id
	,sgk.adi AS sgk_kanun_no_adi
FROM
	tb_personel AS p
LEFT JOIN
	tb_firmalar AS f ON p.firma_id = f.id
LEFT JOIN
	tb_bolumler AS b ON p.bolum_id = b.id
LEFT JOIN
	tb_sgk_kanun_no AS sgk ON p.sgk_kanun_no_id = sgk.id
WHERE
	p.aktif = 1
ORDER By
	p.id DESC
SQL;

$SQL_tek_personel_oku =<<< SQL
SELECT
	*
FROM
	tb_personel
WHERE
	id = ?
SQL;

$SQL_firmalar =<<< SQL
SELECT
	 id
	,adi
FROM
	tb_firmalar
SQL;


$SQL_firmalar = <<< SQL
SELECT
	 id
	,adi
FROM
	tb_firmalar
SQL;

$SQL_bolumler = <<< SQL
SELECT
	*
FROM
	tb_bolumler
WHERE
	aktif = 1
ORDER BY
	adi
SQL;

$SQL_sgk_kanun_no = <<< SQL
SELECT
	*
FROM
	tb_sgk_kanun_no
WHERE
	aktif = 1
SQL;

$bolumler			= $vt->select( $SQL_bolumler, array() );
$sgk_kanun_nolar	= $vt->select( $SQL_sgk_kanun_no, array() );
$firmalar			= $vt->select( $SQL_firmalar, array() );
$personeller		= $vt->select( $SQL_tum_personel_oku, array() );
$tek_personel		= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) );

$personel_bilgileri = array(
	 'id'							=> $personel_id > 0 ? $personel_id												: 0
	,'firma_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_id' ]						: 0
	,'bolum_id'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_id' ]						: 0
	,'firma_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'firma_adi' ]					: ''
	,'bolum_adi'					=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'bolum_adi' ]					: ''
	,'tc_no'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'tc_no' ]						: ''
	,'adi'							=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'adi' ]							: ''
	,'soyadi'						=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'soyadi' ]						: ''
	,'ise_giris_tarihi'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'ise_giris_tarihi' ]				: ''
	,'isten_cikis_tarihi'			=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'isten_cikis_tarihi' ]			: ''
	,'sgk_kanun_no_id'				=> $personel_id > 0 ? $tek_personel[ 2 ][ 0 ][ 'sgk_kanun_no_id' ]				: ''
	,'ucret'						=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'ucret' ] )						: array( '', '' )
	,'calisma_gunu'					=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'calisma_gunu' ] )					: array( '', '' )
	,'hakedis_saati'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'hakedis_saati' ] )				: array( '', '' )
	,'agi'							=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'agi' ] )							: array( '', '' )
	,'normal_calisma_tutari'		=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'normal_calisma_tutari' ] )		: array( '', '' )
	,'yuzde_50_saati'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'yuzde_50_saati' ] )				: array( '', '' )
	,'yuzde_100_saati'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'yuzde_100_saati' ] )				: array( '', '' )
	,'ikinci_fazla_mesai_odemesi'	=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'ikinci_fazla_mesai_odemesi' ] )	: array( '', '' )
	,'mesai_kazanci'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'mesai_kazanci' ] )				: array( '', '' )
	,'toplam_kesinti_saati'			=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'toplam_kesinti_saati' ] )			: array( '', '' )
	,'toplam_gelmeme_kesintisi'		=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'toplam_gelmeme_kesintisi' ] )		: array( '', '' )
	,'hesaplama_hatasi'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'hesaplama_hatasi' ] )				: array( '', '' )
	,'bankaya_odenen'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'bankaya_odenen' ] )				: array( '', '' )
	,'bes'							=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'bes' ] )							: array( '', '' )
	,'avans_toplami'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'avans_toplami' ] )				: array( '', '' )
	,'borc_tutari'					=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'borc_tutari' ] )					: array( '', '' )
	,'odeme_tutari'					=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'odeme_tutari' ] )					: array( '', '' )
	,'iskur_odemesi'				=> $personel_id > 0 ? explode( ".", $tek_personel[ 2 ][ 0 ][ 'iskur_odemesi' ] )				: array( '', '' )
	,'personel_resim'				=> $personel_id > 0 ? 'personel_resimler/' . $tek_personel[ 2 ][ 0 ][ 'personel_resim' ]		: 'personel_resimler/resim_yok.jpg'
);
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kaydı<b>Silmek</b> istediğinize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#kayit_sil' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class = "col-md-5">
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
			<div class = "col-md-7">
				<div class="card card-widget widget-user shadow">
					<form action = "_modul/personel/personelEkleSEG.php" method = "POST" enctype="multipart/form-data">
						<input type="file" id="gizli_input_file" name = "personel_resim" style = "display:none;" name = "resim" accept="image/gif, image/jpeg, image/png"  onchange="resimOnizle(this)"; />
						<input type = "hidden" name = "personel_id" value = "<?php echo $personel_id; ?>">
						<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
						<div class="widget-user-header bg-primary">
							<h3 class="widget-user-username"><b>Personel Ekle - Düzenle</b></h3>
							<small class="widget-user-desc">Resim seçmek için fotoğrafa tıklayınız</small>
						</div>
						<div class="widget-user-image">
							<img class="img-circle elevation-2" src="<?php echo $personel_bilgileri[ 'personel_resim' ] . "?_dc=" . time(); ?>" width = "100px" height = "100px" alt="User Avatar" id = "personel_resim">
						</div>
						<div class="card-footer">
							<div class="row">
								<div class="col-sm-4 border-right">
									<div class="description-block">
										<h5 class="description-header">TC No</h5>
										<input type="text" pattern = "[0-9]{11}" title="TC No 11 haneli olmalıdır." name = "tc_no" class="form-control" value = "<?php echo $personel_bilgileri[ 'tc_no' ]; ?>" required>
									</div>
								</div>
								<div class="col-sm-4 border-right">
									<div class="description-block">
										<h5 class="description-header">Adı</h5>
										<input type="text" name = "adi" class="form-control" value = "<?php echo $personel_bilgileri[ 'adi' ]; ?>" required>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="description-block">
										<h5 class="description-header">Soyadı</h5>
										<input type="text" name = "soyadi"  class="form-control" value = "<?php echo $personel_bilgileri[ 'soyadi' ]; ?>" required >
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4 border-right">
									<div class="description-block">
										<h5 class="description-header">Bölüm</h5>
										<select  class="form-control select2" name = "bolum_id" id = "bolum_id">
											<?php foreach( $bolumler[ 2 ] AS $bolum ) { ?>
												<option value = "<?php echo $bolum[ 'id' ]; ?>" <?php if( $personel_bilgileri[ 'bolum_id' ] == $bolum[ 'id' ] ) echo 'selected'?>><?php echo $bolum[ 'adi' ]; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4 border-right">
									<div class="description-block">
										<h5 class="description-header">Firma</h5>
										<select  class="form-control select2" name = "firma_id" id = "firma_id" width = "100%">
											<?php foreach( $firmalar[ 2 ] AS $firma ) { ?>
												<option value = "<?php echo $firma[ 'id' ]; ?>" <?php if( $personel_bilgileri[ 'firma_id' ] == $firma[ 'id' ] ) echo 'selected'?>><?php echo $firma[ 'adi' ]; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="description-block">
										<h5 class="description-header">SGK Kanun No</h5>
										<select  class="form-control select2" name = "sgk_kanun_no_id" id = "sgk_kanun_no_id" width = "100%">
											<?php foreach( $sgk_kanun_nolar[ 2 ] AS $kanun_no ) { ?>
												<option value = "<?php echo $kanun_no[ 'id' ]; ?>" <?php if( $personel_bilgileri[ 'sgk_kanun_no_id' ] == $kanun_no[ 'id' ] ) echo 'selected'?>><?php echo $kanun_no[ 'adi' ]; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
						</div>

							<div class="card-body">
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend">
											<span class="input-group-text" id="inputGroupPrepend">İşe Giriş Tarihi</span>
											</div>
											<input type="date" name = "ise_giris_tarihi"  class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend" value = "<?php echo $personel_bilgileri[ 'ise_giris_tarihi' ]; ?>" required>
											<div class="invalid-feedback">
											  Please choose a username.
											</div>
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend">
											<span class="input-group-text" id="inputGroupPrepend">İşten Çıkış Tarihi</span>
											</div>
											<input type="date" name = "isten_cikis_tarihi"  class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend" value = "<?php echo $personel_bilgileri[ 'isten_cikis_tarihi' ]; ?>" required>
											<div class="invalid-feedback">
											  Please choose a username.
											</div>
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Ücret</span>
											</div>
											<input type="number" name = "ucret[]" 		class="form-control" placeholder = "TL" 	min = "0" value = "<?php echo $personel_bilgileri[ 'ucret' ][ 0 ]; ?>" >
											<input type="number" name = "ucret[]"  class="form-control" placeholder = "Kuruş" min = "0" max = "99" value = "<?php echo $personel_bilgileri[ 'ucret' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Hakediş Saati</span>
											</div>
											<input type="number" name = "hakedis_saati[]" class="form-control" placeholder = "TL" min = "0" value = "<?php echo $personel_bilgileri[ 'hakedis_saati' ][ 0 ]; ?>">
											<input type="number" name = "hakedis_saati[]" class="form-control" placeholder = "Kuruş" min = "0" max = "99" value = "<?php echo $personel_bilgileri[ 'hakedis_saati' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Agi</span>
											</div>
											<input type="number" name = "agi[]" class="form-control" placeholder = "TL" min = "0" value = "<?php echo $personel_bilgileri[ 'agi' ][ 0 ]; ?>">
											<input type="number" name = "agi[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'agi' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Mesai Kazancı</span>
											</div>
											<input type="number" name = "mesai_kazanci[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'mesai_kazanci' ][ 0 ]; ?>">
											<input type="number" name = "mesai_kazanci[]" class="form-control" placeholder = "Kuruş"  min = "0" value = "<?php echo $personel_bilgileri[ 'mesai_kazanci' ][ 0 ]; ?>">
										</div>
									</div>
								</div>
								
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Yuzde 50 Saati</span>
											</div>
											<input type="number" name = "yuzde_50_saati[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'yuzde_50_saati' ][ 0 ]; ?>">
											<input type="number" name = "yuzde_50_saati[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'yuzde_50_saati' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Yuzde 100 Saati</span>
											</div>
											<input type="number" name = "yuzde_100_saati[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'yuzde_100_saati' ][ 0 ]; ?>">
											<input type="number" name = "yuzde_100_saati[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'yuzde_100_saati' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">B.E.S</span>
											</div>
											<input type="number" name = "bes[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'bes' ][ 0 ]; ?>">
											<input type="number" name = "bes[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'bes' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Avans Toplamı</span>
											</div>
											<input type="number" name = "avans_toplami[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'avans_toplami' ][ 0 ]; ?>">
											<input type="number" name = "avans_toplami[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'avans_toplami' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Borç Tutarı</span>
											</div>
											<input type="number" name = "borc_tutari[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'borc_tutari' ][ 0 ]; ?>">
											<input type="number" name = "borc_tutari[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'borc_tutari' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Ödeme Tutarı</span>
											</div>
											<input type="number" name = "odeme_tutari[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'odeme_tutari' ][ 0 ]; ?>">
											<input type="number" name = "odeme_tutari[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'odeme_tutari' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								
								<div class="form-row">
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">İşkur Ödemesi</span>
											</div>
											<input type="number" name = "iskur_odemesi[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'iskur_odemesi' ][ 0 ]; ?>">
											<input type="number" name = "iskur_odemesi[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'iskur_odemesi' ][ 1 ]; ?>">
										</div>
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Bankaya Ödenen</span>
											</div>
											<input type="number" name = "bankaya_odenen[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'bankaya_odenen' ][ 0 ]; ?>">
											<input type="number" name = "bankaya_odenen[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'bankaya_odenen' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Normal Çalışma Tutarı</span>
											</div>
											<input type="number" name = "normal_calisma_tutari[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'normal_calisma_tutari' ][ 0 ]; ?>">
											<input type="number" name = "normal_calisma_tutari[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'normal_calisma_tutari' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">İkinci Fazla Mesai Ödemesi</span>
											</div>
											<input type="number" name = "ikinci_fazla_mesai_odemesi[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'ikinci_fazla_mesai_odemesi' ][ 0 ]; ?>">
											<input type="number" name = "ikinci_fazla_mesai_odemesi[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'ikinci_fazla_mesai_odemesi' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Toplam Kesinti Saati</span>
											</div>
											<input type="number" name = "toplam_kesinti_saati[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'toplam_kesinti_saati' ][ 0 ]; ?>">
											<input type="number" name = "toplam_kesinti_saati[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'toplam_kesinti_saati' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Toplam Gelmeme Kesintisi</span>
											</div>
											<input type="number" name = "toplam_gelmeme_kesintisi[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'toplam_gelmeme_kesintisi' ][ 0 ]; ?>">
											<input type="number" name = "toplam_gelmeme_kesintisi[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'toplam_gelmeme_kesintisi' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle" id="">Hesaplama Hatası</span>
											</div>
											<input type="number" name = "hesaplama_hatasi[]" class="form-control" placeholder = "TL"  min = "0" value = "<?php echo $personel_bilgileri[ 'hesaplama_hatasi' ][ 0 ]; ?>">
											<input type="number" name = "hesaplama_hatasi[]" class="form-control" placeholder = "Kuruş" max = "99" value = "<?php echo $personel_bilgileri[ 'hesaplama_hatasi' ][ 1 ]; ?>">
										</div>
									</div>
								</div>
								<div class="form-row">
									<div class="col-md-12 mb-3">
										<div class="input-group">
											<div class="input-group-prepend personel-ekle">
												<span class="input-group-text personel-ekle">Çalışma Günü</span>
											</div>
											<input type="number" name = "calisma_gunu" class="form-control" placeholder = "Gün sayısı"  min = "0" value = "<?php echo $personel_bilgileri[ 'calisma_gunu' ][ 0 ]; ?>">
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<div style="display: flex ; justify-content: flex-end">
									<a href = "?modul=personelEkle&islem=ekle" class="btn btn-sm btn-default mr-1"> Temizle/Yeni Kayıt </a>
									<button type = "submit" class="btn btn-sm btn-success"> Kaydet </button>
								</div>
							</div>
						</div>
					</form>
			</div>
		</div>
	</div>
</section>

<script>
	$( '#personel_id' ).on( 'select2:select', function ( e ) {
		window.location = window.location.origin + '/index.php?modul=personelOzlukDosyalari&personel_id=' + e.params.data.id;
	} );
</script>
<script>
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
</script>
