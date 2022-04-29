<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$SQL_oku = <<< SQL
SELECT
	id,adi
FROM
	tb_hizli_veri_girisi_sablonlar
SQL;

$SQL_tablolar_oku = <<< SQL
SELECT
	 tablo_adi
	,adi
FROM
	tb_hizli_veri_girisi_tablolar
SQL;

$SQL_tablo_alanlar_oku = <<< SQL
SELECT
	 COLUMN_NAME as alan_orj
	,COLUMN_COMMENT as alan_tr
FROM
	INFORMATION_SCHEMA.COLUMNS
WHERE
	TABLE_SCHEMA = Database()
AND
	TABLE_NAME = ?
SQL;


$SQL_tek_sablon_oku = <<< SQL
SELECT
	*
FROM
	tb_hizli_veri_girisi_sablonlar
WHERE
	id = ?
SQL;

$sablon_id	= array_key_exists( 'sablon_id', $_REQUEST ) ? $_REQUEST[ 'sablon_id' ] : 0;
$islem		= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';


$sablonlar	= $vt->select( $SQL_oku, array() )[ 2 ];
$tablolar	= $vt->select( $SQL_tablolar_oku, array() )[ 2 ];
$tek_sablon	= $vt->select( $SQL_tek_sablon_oku, array( $sablon_id ) )[ 2 ];

$satir_renk				= $sablon_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $sablon_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $sablon_id > 0	? 'btn btn-warning btn-sm float-right'	: 'btn btn-success btn-sm float-right';


$tablo_alanlar	= $vt->select( $SQL_tablo_alanlar_oku, array( $tablolar[ 0 ][ 'tablo_adi' ] ) )[ 2 ];

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
				<h3 class="card-title">Şablonlar</h3>
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
						<?php $sayi = 1; foreach( $sablonlar AS $sablon ) { ?>
						<tr <?php if( $sablon[ 'id' ] == $sablon_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $sablon[ 'adi' ]; ?></td>
							<td align = "center">
								<a modul = 'sablonlar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=sablonlar&islem=guncelle&sablon_id=<?php echo $sablon[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul = 'sablonlar' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/hizliverigirisi/sablonlarSEG.php?islem=sil&sablon_id=<?php echo $sablon[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
				<form class="form-horizontal" action = "_modul/hizliverigirisi/sablonlarSEG.php" method = "POST">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<input type = "hidden" name = "sablon_id" value = "<?php echo $sablon_id; ?>">
					<input type = "hidden" name = "sablon_tablo_adi_gizli" value = "<?php echo $tek_sablon[ 0 ][ 'tablo_adi' ] ?>">
					<div class = "card-body">
							<div class="form-group">
								<label for="recipient-name" class="col-form-label">Modül</label>
								
								<select <?php if( $sablon_id > 0 ) echo 'disabled'; ?> name = "sablon_tablo_adi" class = "form-control" required id = "sablon_tablo_adi" data-sablon_id = "<?php echo $sablon_id; ?>">
									<?php foreach( $tablolar as $tablo ) { ?>
										<option <?php if( $tablo[ 'tablo_adi' ] == $tek_sablon[ 0 ][ 'tablo_adi' ] ) echo 'selected' ?> value = "<?php echo $tablo[ 'tablo_adi' ]?>"><?php echo $tablo[ 'adi' ]; ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label for="message-text" class="col-form-label">Alanlar</label>
								<select multiple="multiple" name = "sablon_alanlar[]" class = "form-control select2" id = "sablon_alanlar" required>
									<?php
										foreach( $tablo_alanlar as $alan ) {
											$id		= $alan[ 'alan_orj' ];
											if( $id == 'id' or $id == 'aktif' ) continue;
											$adi	= strlen( $alan[ 'alan_tr' ] ) > 0 ? explode( "-", $alan[ 'alan_tr' ] )[ 0 ] : $alan[ 'alan_orj' ];
											echo "<option value = '$id'>$adi</option>";
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label for="recipient-name" class="col-form-label">Şablonun Adı</label>
								<input type = "text" value = "<?php echo $tek_sablon[ 0 ][ 'adi' ]; ?>"name = "sablon_adi" placeholder = "Şablona bir isim verin..."  class = "form-control" required>
							</div>
					</div>
					<div class="card-footer">
							<button modul= 'sablonlar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
							<a href = "?modul=sablonlar"  class="btn btn-sm btn-default"><span class="fa fa-refresh"></span> Yeni kayıt / Temizle</a>
					</div>
				</form>
		</div>
	</div>
</div>

<script>



$( document ).ready( function() {
	let sablon_id = <?php echo $sablon_id; ?>;

	$( "#sablon_tablo_adi" ).change( function () {
		let tablo_adi = this.value;
		$.ajax( {
			 url		: "_modul/hizliverigirisi/tabloAlanVer.php"
			,cache		: false
			,data		: {
				 tablo_adi : tablo_adi
				,sablon_id : sablon_id
			}
			,success	: function( sonuc ) {
				$( "#sablon_alanlar" ).html( sonuc );
			}
		} );
	} );
	if( sablon_id ) $("#sablon_tablo_adi").trigger( 'change' );
} );

</script>

