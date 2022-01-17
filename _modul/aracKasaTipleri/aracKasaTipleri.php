<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
  $mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
  $mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
  $_REQUEST[ 'arac_kasa_tipi_id' ] = $_SESSION[ 'sonuclar' ][ 'arac_kasa_tipi_id' ];
  unset( $_SESSION[ 'sonuclar' ] );
  echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
  *
FROM
  tb_arac_kasa_tipleri
SQL;

$SQL_arac_kasa_tipi_bilgileri = <<< SQL
SELECT
  *
FROM
  tb_arac_kasa_tipleri
WHERE
  id = ?
SQL;

$id     	= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$arac_kasa_tipleri		= $vt->select( $SQL_oku, array() );
$arac_kasa_tipi           = $vt->selectSingle( $SQL_arac_kasa_tipi_bilgileri, array( $id ) );
$arac_kasa_tipi_bilgileri = array();
$islem          = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' ){
	$arac_kasa_tipi_bilgileri = $arac_kasa_tipi[ 2 ];
}
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="arac_kasa_tipleri_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#arac_kasa_tipleri_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>


<script>  
  $(document).ready(function() {
    $('#limit-belirle').change(function() {
		
        $(this).closest('form').submit();
		
    });
});
</script>
  <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Araç Kasa Tipleri</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-sm table-bordered table-striped">
                  <thead>
                    <tr>
                      <th style="width: 15px">#</th>
                      <th>Adı</th>
                      <th style="width: 20px">Düzenle</th>
                      <th style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
				  <?php $sira=1; foreach( $arac_kasa_tipleri[ 2 ] AS $arac_kasa_tipi ) { ?>
                  <tr>
                    <td><?php echo $sira++; ?></td>
                    <td><?php echo $arac_kasa_tipi[ 'adi' ]; ?></td>
                    <td align = "center">
                      <a modul = 'aracKasaTipleri' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=aracKasaTipleri&islem=guncelle&id=<?php echo $arac_kasa_tipi[ 'id' ]; ?>" >
                        Düzenle
                      </a>
                    </td>
                    <td align = "center">
                      <button modul = 'aracKasaTipleri' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/aracKasaTipleri/aracKasaTipleriSEG.php?islem=sil&id=<?php echo $arac_kasa_tipi[ 'id' ]; ?>" data-toggle="modal" data-target="#arac_kasa_tipleri_sil_onay" >Sil</button>
                    </td>
                  </tr>
				  <?php } ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>


     
          <!-- left column -->
          <div class="col-md-4">
            <!-- general form elements -->
            <div class="card card-secondary">
              <div class="card-header">
                <h3 class="card-title">Araç Kasa Tipi Ekle / Güncelle</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form id = "kayit_formu" action = "_modul/aracKasaTipleri/aracKasaTipleriSEG.php" method = "POST">
                <div class="card-body">
                  <input type = "hidden" name = "id" value = "<?php echo $arac_kasa_tipi_bilgileri[ 'id' ]; ?>">
                  <input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
                  <div class="form-group">
                    <label  class="control-label">Adı</label>
                      <input type="text" class="form-control" name ="adi" value = "<?php echo $arac_kasa_tipi_bilgileri[ 'adi' ]; ?>" required placeholder="Adı">
                  </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button modul= 'aracKasaTipleri' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
                    <button onclick="window.location.href = '?modul=aracKasaTipleri&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
                </div>
              </form>
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->
          <!-- right column -->

        </div>
        <!-- /.row -->


