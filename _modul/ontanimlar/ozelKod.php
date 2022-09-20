<?php
$fn = new Fonksiyonlar();

$SQL_oku = <<< SQL
SELECT
	*
FROM
	tb_ozel_kod
WHERE
	firma_id 	= ? AND
	aktif 		= 1
SQL;

$SQL_tek_ozel_kod_oku = <<< SQL
SELECT
	*
FROM
	tb_ozel_kod
WHERE
	id = ?
SQL;


$ozel_kod_id	= array_key_exists( 'ozel_kod_id', $_REQUEST ) ? $_REQUEST[ 'ozel_kod_id' ] : 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$ozel_kodlar	= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tek_ozel_kod	= $vt->select( $SQL_tek_ozel_kod_oku, array( $ozel_kod_id ) );

$ozel_kod_bilgileri = array(
	 'id'		=> $ozel_kod_id > 0 ? $ozel_kod_id : 0
	,'adi'		=> $ozel_kod_id > 0 ? $tek_ozel_kod[ 2 ][ 0 ][ 'adi' ] : ''

);


$satir_renk				= $ozel_kod_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $ozel_kod_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $ozel_kod_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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

<div class="row">
	<div class="col-md-6">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Bölümler</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th>Adı</th>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
					<tbody>
						<?php $sayi = ($sayfa-1)*$limit+1;  foreach( $ozel_kodlar[ 2 ] AS $ozel_kod ) { ?>
						<tr <?php if( $ozel_kod[ 'id' ] == $ozel_kod_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $ozel_kod[ 'adi' ]; ?></td>
							<td align = "center">
							<a modul = 'ozelKod' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=ozelKod&islem=guncelle&ozel_kod_id=<?php echo $ozel_kod[ 'id' ]; ?>" >
								Düzenle
							</a>
							</td>
							<td align = "center">
							<button modul = 'ozelKod' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/ontanimlar/ozelKodSEG.php?islem=sil&ozel_kod_id=<?php echo $ozel_kod[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="card-footer clearfix">
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card card-secondary">
		<div class="card-header">
			<h3 class="card-title">Bölüm Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/ontanimlar/ozelKodSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "ozel_kod_id" value = "<?php echo $ozel_kod_bilgileri[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Bölüm Adı</label>
				<input type="text" class="form-control" name ="ozel_kod_adi" value = "<?php echo $ozel_kod_bilgileri[ 'adi' ]; ?>" required placeholder="">
			</div>
			</div>
			<div class="card-footer">
			<button modul= 'ozel_kodlar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi?></button>
				<button onclick="window.location.href = '?modul=ozelKod&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>


