<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
  $mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
  $mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
  $_REQUEST[ 'sube_id' ] = $_SESSION[ 'sonuclar' ][ 'sube_id' ];
  unset( $_SESSION[ 'sonuclar' ] );
  echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$islem      = array_key_exists( 'islem', $_REQUEST )    ? $_REQUEST[ 'islem' ]    : 'ekle';
$id         = array_key_exists( 'id', $_REQUEST )       ? $_REQUEST[ 'id' ]       : 0;



$SQL_oku = <<< SQL
SELECT
  *
FROM
  tb_subeler
WHERE 
  firma_id  = ? AND
  aktif     = 1
SQL;

$SQL_sube_bilgileri = <<< SQL
SELECT
  *
FROM
  tb_subeler
WHERE
  firma_id  = ? AND
  id        = ? AND
  aktif     = 1
SQL;

$subeler          = $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$sube_bilgileri   = $vt->select( $SQL_sube_bilgileri, array( $_SESSION[ "firma_id" ], $id ) )[2][0];

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
                <h3 class="card-title">Şubeler</h3>
              </div>
              <!-- /.card-header -->
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
                  <?php $sayi = 1; foreach( $subeler[ 2 ] AS $sube ) { ?>
                  <tr>
                    <td><?php echo $sayi++; ?></td>
                    <td><?php echo $sube[ 'adi' ]; ?></td>
                    <td align = "center">
                      <a modul = 'subeler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=subeler&islem=guncelle&id=<?php echo $sube[ 'id' ]; ?>" >
                        Düzenle
                      </a>
                    </td>
                    <td align = "center">
                      <button modul = 'subeler' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/ontanimlar/subelerSEG.php?islem=sil&id=<?php echo $sube[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
                    </td>
                  </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
                <div class="card-footer clearfix">
                </div>
            </div>
            <!-- /.card -->
        </div>
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-secondary">
              <div class="card-header">
                <h3 class="card-title">Şube Ekle / Güncelle</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form id = "kayit_formu" action = "_modul/ontanimlar/subelerSEG.php" method = "POST">
                <div class="card-body">
                  <input type = "hidden" name = "id" value = "<?php echo $sube_bilgileri[ 'id' ]; ?>">
                  <input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
                  <div class="form-group">
                    <label  class="control-label">Şube Adı</label>
                      <input type="text" class="form-control" name ="sube_adi" value = "<?php echo $sube_bilgileri[ 'adi' ]; ?>" required placeholder="">
                  </div>	
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button modul= 'subeler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
                    <button onclick="window.location.href = '?modul=subeler&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
          <!--/.col (left) -->
          <!-- right column -->

        </div>
        <!-- /.row -->


