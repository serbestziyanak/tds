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

$islem 				= array_key_exists( 'islem', $_REQUEST ) 			? $_REQUEST[ 'islem' ] 				: 'ekle';
$avansKesinti_id 	= array_key_exists( 'avansKesinti_id', $_REQUEST ) 	? $_REQUEST[ 'avansKesinti_id' ] 	: 0;

$SQL_tum_personel_oku = <<< SQL
SELECT
	 tb_personel.id
	,CONCAT( adi, " ", soyadi ) as adi
FROM
	tb_personel
WHERE
	firma_id 	= ? AND 
	aktif 		= 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	id
	,adi
	,soyadi
FROM
	tb_personel
WHERE
	id 			= ? AND 
	firma_id 	= ?
SQL;

/*Avans Kesinti Tiplerini Çağırıyoruz*/
$SQL_avansKesinti_tipleri = <<< SQL
SELECT
	id
	,adi
FROM
	tb_avans_kesinti_tipi
WHERE
	firma_id 	= ? AND
	aktif 		= 1
SQL;

/*Personele Yapılan Ödeme ve Kesintiler*/
$SQL_tum_avans_kesinti_oku = <<< SQL
SELECT
	a.*
	,t.adi
	,t.maas_kesintisi
FROM
	tb_avans_kesinti As a
INNER JOIN tb_avans_kesinti_tipi AS t ON t.id = a.islem_tipi
WHERE
	a.personel_id 	= ? AND
	a.aktif 		= 1
ORDER BY verilis_tarihi ASC
SQL;

/*Düzenlenen Avans Bilgilerini Çeker*/
$SQL_tek_avans_kesinti_oku = <<< SQL
SELECT
	a.*
FROM
	tb_avans_kesinti As a
WHERE
	a.personel_id 	= ? AND
	a.id 			= ? AND
	a.aktif 		= 1
ORDER BY verilis_tarihi ASC
SQL;

$personeller					= $vt->select( $SQL_tum_personel_oku, array( $_SESSION['firma_id'] ) );
$personel_id					= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$tek_personel					= $vt->select( $SQL_tek_personel_oku, array( $personel_id, $_SESSION['firma_id'] ) )[ 2 ][ 0 ];
$avans_kesinti_tipleri			= $vt->select( $SQL_avansKesinti_tipleri, array( $_SESSION['firma_id'] ) )[ 2 ];
$avansKesintiler				= $vt->select( $SQL_tum_avans_kesinti_oku, array( $personel_id ) )[ 2 ];
$avansGelen						= $vt->select( $SQL_tek_avans_kesinti_oku, array( $personel_id, $avansKesinti_id ) )[ 2 ][ 0 ];

$satir_renk				= $personel_id 	> 0				? 'table-warning' 						: '';
$kaydet_buton_cls		= $islem 		== "guncelle"	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';
$kaydet_buton_yazi		= $islem 		== "guncelle"	? 'Güncelle'							: 'Kaydet';

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				Bu kaydı <b>Silmek</b> istediğinize emin misiniz?
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
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<button modul = 'avansKesinti' yetki_islem="toplu_avans_kesinti_kazanc" class="btn btn-outline-primary btn-lg col-xs-6 col-sm-2" data-toggle="modal" data-target="#PersonelHareketEkle">Toplu İşlem Ekle</button>
				
			</div>
			<div class = "col-md-4">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Personel Seçin</h3>
					</div>
					<div class="card-body">
						<table id="tbl_avansKesinti" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th data-priority=" 1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1;  foreach( $personeller[ 2 ] AS $personel ) { ?>

								<tr  <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'";?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td align = "center">
										<a modul = 'avansKesinti' yetki_islem="odemeler" class = "btn btn-sm btn-success btn-xs" href = "?modul=avansKesinti&personel_id=<?php echo $personel[ 'id' ]; ?>" >
											Ödemeler
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class = "col-md-8">
				<div class="card card-light">
					<div class="card-header">
						<h3 class="card-title">
							<?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> || Avans ve Kesinti Ekle
						</h3>
					</div>
					<div class="card-body">
							<form action = "_modul/avansKesinti/avansKesintiSEG.php" method = "POST" enctype="multipart/form-data">
								<input type = "hidden" name="personel_id" value="<?php echo $personel_id; ?>">
								<input type = "hidden" name="islem" value="<?php echo $islem; ?>">
								<input type = "hidden" name="avansKesinti_id" value="<?php echo $avansKesinti_id; ?>">
								<div class="form-group">
									<label class="control-label">Veriliş Tarihi</label>
									<div class="input-group date" id="verilisTarihi" data-target-input="nearest">
										<div class="input-group-append" data-target="#verilisTarihi" data-toggle="datetimepicker">
											<div class="input-group-text"><i class="fa fa-calendar"></i></div>
										</div>
										<input autocomplete="off" type="text" name="verilis_tarihi" class="form-control datetimepicker-input" data-target="#verilisTarihi" data-toggle="datetimepicker" 
										value="<?php echo $avansGelen[ 'verilis_tarihi' ] != '' ? date('d.m.Y', strtotime( $avansGelen[ 'verilis_tarihi' ] ) ) : ''; ?>">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">İşlem Tipi</label>
									<select class="form-control select2 " name="islem_tipi" data-select2-id="4" required tabindex="-1" aria-hidden="true">
										<?php foreach( $avans_kesinti_tipleri as $tip ) { ?>
											<option value="<?php echo $tip[ 'id' ]; ?>" <?php echo $avansGelen[ "islem_tipi" ] == $tip[ "id" ] ? 'selected' : ''; ?>><?php echo $tip[ 'adi' ]; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="form-group">
									<label class="control-label">Tutar</label>
									<input type="number" step="0.01" class="form-control" name ="tutar"  required value="<?php echo $avansGelen[ 'tutar' ] ?>">
								</div>
								
								<div class="form-group">
									<label class="control-label">Ödeme Şekli</label>
									<select class="form-control select2 " name="verilis_sekli" data-select2-id="4" required tabindex="-1" aria-hidden="true">
										<option value="Banka" <?php echo $avansGelen[ "verilis_sekli" ] == 'Banka' ? 'selected' : ''; ?>>Banka</option>
										<option value="Elden" <?php echo $avansGelen[ "verilis_sekli" ] == 'Elden' ? 'selected' : ''; ?>>Elden</option>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Açıklama</label>
									<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"><?php echo $avansGelen[ "aciklama" ]; ?></textarea>
								</div>
								<div class="card-footer">
									<button modul= 'avansKesinti' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
								</div>
							</form>
					</div>
				</div>
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> || Avans Kesinti Listesi</h3>
					</div>
					<div class="card-body">
						<table class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Veriliş Tarihi</th>
									<th>Tutar</th>
									<th>İşlem Tipi</th>
									<th>Ödeme Sekli</th>
									<th>Düzenle</th>
									<th>Sil</th>
								</tr>
							</thead>
							<tbody>
							<?php 	
									$say = 0;
									if( count( $avansKesintiler ) > 0 ) {
										foreach( $avansKesintiler AS $avans ) { $say++; ?>
											<tr <?php if( $avans[ 'id' ] == $avansKesinti_id ) echo "class = '$satir_renk'";?>>
												<td><?php echo $say; ?></td>
												<td><?php echo $fn->tarihFormatiDuzelt($avans[ "verilis_tarihi" ]); ?></td>
												<td><?php echo $avans[ "tutar" ]; ?></td>
												<td><?php echo $avans[ "adi" ]; ?></td>
												<td><?php echo $avans[ "verilis_sekli" ]; ?></td>
												<td align = "center" width = "5%">
													<a modul = 'avansKesinti' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=avansKesinti&islem=guncelle&personel_id=<?php echo $personel_id; ?>&avansKesinti_id=<?php echo $avans[ 'id' ]; ?>" >
														Düzenle
													</a>
												</td>
												<td align = "center" width = "5%">
													<a href = "" 
														modul = 'avansKesinti' yetki_islem="sil"
														data-href="_modul/avansKesinti/avansKesintiSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&avansKesinti_id=<?php echo $avans[ 'id' ]; ?>"
														data-target="#kayit_sil"
														data-toggle="modal"
														data-toggle="tooltip" 
														data-placement="left" 
														title="Dosyayı Sil"
														class="btn btn-danger btn-xs">
														Sil
													</a>
												</td>
											</tr>
									<?php
										}
									} ?>
							</tbody>	
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="modal fade" id="PersonelHareketEkle"  aria-modal="true" role="dialog" modul = 'avansKesinti' yetki_islem="toplu_avans_kesinti_kazanc">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="_modul/avansKesinti/avansKesintiSEG.php" method="post" enctype="multipart/form-data">
				<input type = "hidden" name = "islem" value = "toplu" >
				<div class="modal-header">
					<h4 class="modal-title">Toplu İşlem Ekleme</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Veriliş Tarihi</label>
						<div class="input-group date" id="topluVerilisTarihi" data-target-input="nearest">
							<div class="input-group-append" data-target="#topluVerilisTarihi" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="verilis_tarihi" class="form-control datetimepicker-input" data-target="#topluVerilisTarihi" data-toggle="datetimepicker" 
							value="<?php echo $avansGelen[ 'verilis_tarihi' ] != '' ? date('d.m.Y', strtotime( $avansGelen[ 'verilis_tarihi' ] ) ) : ''; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">İşlem Tipi</label>
						<select class="form-control select2 " name="islem_tipi" data-select2-id="4" required tabindex="-1" aria-hidden="true">
							<?php foreach( $avans_kesinti_tipleri as $tip ) { ?>
								<option value="<?php echo $tip[ 'id' ]; ?>" <?php echo $avansGelen[ "islem_tipi" ] == $tip[ "id" ] ? 'selected' : ''; ?>><?php echo $tip[ 'adi' ]; ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label class="control-label">Tutar</label>
						<input type="number" step="0.01" class="form-control" name ="tutar"  required value="<?php echo $avansGelen[ 'tutar' ] ?>">
					</div>
					
					<div class="form-group">
						<label class="control-label">Ödeme Şekli</label>
						<select class="form-control select2 " name="verilis_sekli" data-select2-id="4" required tabindex="-1" aria-hidden="true">
							<option value="Banka" <?php echo $avansGelen[ "verilis_sekli" ] == 'Banka' ? 'selected' : ''; ?>>Banka</option>
							<option value="Elden" <?php echo $avansGelen[ "verilis_sekli" ] == 'Elden' ? 'selected' : ''; ?>>Elden</option>
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Açıklama</label>
						<textarea class="form-control" rows="2" name="aciklama" placeholder="Açıklama Yazabilirisniz"><?php echo $avansGelen[ "aciklama" ]; ?></textarea>
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

<script>
	$('#tbl_avansKesinti').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'stateSave'	: true,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
		}
	});

	$('#tbl_avanslar').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'stateSave'	: true,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
		}
	});
	

	$(function () {
		$('#verilisTarihi').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});

		$('#topluVerilisTarihi').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});
	
    

</script>