<?php
$fn = new Fonksiyonlar();

$SQL_oku = <<< SQL
SELECT
	*
FROM
	tb_sgk_kanun_no
WHERE
	aktif = 1
SQL;

$SQL_tek_sgk_kanun_oku = <<< SQL
SELECT
	*
FROM
	tb_sgk_kanun_no
WHERE
	id = ?
SQL;


$id			= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ]		: 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ]	: 'ekle';

$sgk_nolar	= $vt->select( $SQL_oku, array() );
$tek_sgk	= $vt->select( $SQL_tek_sgk_kanun_oku, array( $id ) );

$sgk_bilgileri = array(
	 'id'		=> $id > 0 ? $id : 0
	,'adi'		=> $id > 0 ? $tek_sgk[ 2 ][ 0 ][ 'adi' ] : ''
);

$satir_renk				= $id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
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
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<div class="row">
	<div class="col-md-6">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">SGK Kanun No Listesi</h3>
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
						<?php $sayi = ( $sayfa-1 ) * $limit + 1;  foreach( $sgk_nolar[ 2 ] AS $sgk_no ) { ?>
						<tr <?php if( $sgk_no[ 'id' ] == $id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $sgk_no[ 'adi' ]; ?></td>
							<td align = "center">
								<a modul = 'sgkKanunNo' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=sgkKanunNo&islem=guncelle&id=<?php echo $sgk_no[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul = 'sgkKanunNo' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/sgkKanunNo/sgkKanunNoSEG.php?islem=sil&id=<?php echo $sgk_no[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
			<h3 class="card-title">SGK Kanun No Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/ontanimlar/sgkKanunNoSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "id" value = "<?php echo $sgk_bilgileri[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			<div class="form-group">
				<label  class="control-label">Bölüm Adı</label>
				<input type="text" class="form-control" name ="sgk_kanun_adi" value = "<?php echo $sgk_bilgileri[ 'adi' ]; ?>" required placeholder="">
			</div>
			</div>
			<div class="card-footer">
			<button modul= 'bolumler' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls;?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi;?></button>
				<button onclick="window.location.href = '?modul=sgkKanunNo&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>


