<?php
$fn = new Fonksiyonlar();

$SQL_oku = <<< SQL
SELECT
	*
FROM
	tb_bolumler
WHERE
	firma_id = ? AND
	aktif 	 = 1
SQL;

$SQL_tek_bolum_oku = <<< SQL
SELECT
	*
FROM
	tb_bolumler
WHERE
	id = ?
SQL;


$bolum_id	= array_key_exists( 'bolum_id', $_REQUEST ) ? $_REQUEST[ 'bolum_id' ] : 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$bolumler	= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tek_bolum	= $vt->select( $SQL_tek_bolum_oku, array( $bolum_id ) );

$bolum_bilgileri = array(
	 'id'		=> $bolum_id > 0 ? $bolum_id : 0
	,'adi'		=> $bolum_id > 0 ? $tek_bolum[ 2 ][ 0 ][ 'adi' ] : ''

);

$satir_renk				= $bolum_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $bolum_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $bolum_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
						<?php $sayi = ($sayfa-1)*$limit+1;  foreach( $bolumler[ 2 ] AS $bolum ) { ?>
						<tr <?php if( $bolum[ 'id' ] == $bolum_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $bolum[ 'adi' ]; ?></td>
							<td align = "center">
							<a modul = 'bolumler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=bolumler&islem=guncelle&bolum_id=<?php echo $bolum[ 'id' ]; ?>" >
								Düzenle
							</a>
							</td>
							<td align = "center">
							<button modul = 'bolumler' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/ontanimlar/bolumlerSEG.php?islem=sil&bolum_id=<?php echo $bolum[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
		<form id = "kayit_formu" action = "_modul/ontanimlar/bolumlerSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "bolum_id" value = "<?php echo $bolum_bilgileri[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Bölüm Adı</label>
				<input type="text" class="form-control" name ="bolum_adi" value = "<?php echo $bolum_bilgileri[ 'adi' ]; ?>" required placeholder="">
			</div>
			</div>
			<div class="card-footer">
			<button modul= 'bolumler' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi?></button>
				<button onclick="window.location.href = '?modul=bolumler&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>


