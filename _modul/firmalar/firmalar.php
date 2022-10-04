<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
  $mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
  $mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
  $_REQUEST[ 'firma_id' ] = $_SESSION[ 'sonuclar' ][ 'firma_id' ];
  unset( $_SESSION[ 'sonuclar' ] );
  echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$firma_id             = array_key_exists( 'firma_id' ,$_REQUEST ) ? $_REQUEST[ 'firma_id' ] : 0;

$satir_renk           = $firma_id > 0  ? 'table-warning'                      : '';
$kaydet_buton_yazi    = $firma_id > 0  ? 'Güncelle'                           : 'Kaydet';
$kaydet_buton_cls     = $firma_id > 0  ? 'btn btn-warning btn-sm pull-right'  :  'btn btn-success btn-sm pull-right';

$SQL_oku = <<< SQL
SELECT
  *
FROM
  tb_firmalar
WHERE
  id = ?
SQL;

$SQL_firma_bilgileri = <<< SQL
SELECT
  *
FROM
  tb_firmalar
WHERE
  id = ?
SQL;


$limit = isset($_SESSION["limit-belirle"]) ? $_SESSION["limit-belirle"] : 5;
  $sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 1;
  $baslangic = ($sayfa - 1) * $limit;

$firmalar        = $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );

$firma           = $vt->selectSingle( $SQL_firma_bilgileri, array( $firma_id ) );
$firma_bilgileri = array();
$islem          = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
	$firma_bilgileri = $firma[ 2 ];

//echo "limit:".$limit;
//echo "sayfa:".$sayfa;
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="firmalar_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#firmalar_sil_onay' ).on( 'show.bs.modal', function( e ) {
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
                <h3 class="card-title">Firmalar</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-sm table-bordered table-hover">
                  <thead>
                    <tr>
                      <th style="width: 15px">#</th>
                      <th>Adı</th>
                      <th>Üst Firma</th>
                      <th>Ünvan</th>
                      <th>Tel</th>
                      <th data-priority="1" style="width: 20px">Düzenle</th>
                      <th data-priority="1" style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php $sayi = ($sayfa-1)*$limit+1;  foreach( $firmalar[ 2 ] AS $firma ) { ?>
                  <tr <?php if( $firma[ 'id' ] == $firma_id ) echo "class = '$satir_renk'"; ?>>
                    <td><?php echo $sayi++; ?></td>
                    <td><b><?php echo $firma[ 'adi' ]; ?></b></td>
                    <td><?php echo $firma[ 'firma' ]; ?></td>
                    <td><?php echo $firma[ 'unvan' ]; ?></td>
                    <td><?php echo $firma[ 'tel' ]; ?></td>
                    <td align = "center">
                      <a modul = 'firmalar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=firmalar&islem=guncelle&firma_id=<?php echo $firma[ 'id' ]; ?>" >
                        Düzenle
                      </a>
                    </td>
                    <td align = "center">
                      <button modul = 'firmalar' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/firmalar/firmalarSEG.php?islem=sil&firma_id=<?php echo $firma[ 'id' ]; ?>" data-toggle="modal" data-target="#firmalar_sil_onay" >Sil</button>
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
        <div class="col-md-4">
          <!-- general form elements -->
          <div class="card card-secondary">
            <div class="card-header">
              <h3 class="card-title">Firma Ekle / Güncelle</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form id = "kayit_formu" action = "_modul/firmalar/firmalarSEG.php" method = "POST">
              <div class="card-body">
                <input type = "hidden" name = "id" value = "<?php echo $firma_bilgileri[ 'id' ]; ?>">
                <input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
                <div class="form-group">
                  <label  class="control-label">Firma Adı</label>
                    <input type="text" class="form-control" name ="firma_adi" value = "<?php echo $firma_bilgileri[ 'adi' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Üst Firma</label>
                    <input type="text" class="form-control" name ="firma" value = "<?php echo $firma_bilgileri[ 'firma' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Ünvan</label>
                    <input type="text" class="form-control" name ="unvan" value = "<?php echo $firma_bilgileri[ 'unvan' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Vergi No</label>
                    <input type="text" class="form-control" name ="vergi_no" value = "<?php echo $firma_bilgileri[ 'vergi_no' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Vergi Dairesi</label>
                    <input type="text" class="form-control" name ="vergi_dairesi" value = "<?php echo $firma_bilgileri[ 'vergi_dairesi' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Ticaret Sicil No</label>
                    <input type="text" class="form-control" name ="ticaret_sicil_no" value = "<?php echo $firma_bilgileri[ 'ticaret_sicil_no' ]; ?>" required placeholder="">
                </div>
                <div class="form-group">
                  <label  class="control-label">Yetki Belgesi No</label>
                    <input type="text" class="form-control" name ="yetki_belgesi_no" value = "<?php echo $firma_bilgileri[ 'yetki_belgesi_no' ]; ?>" required placeholder="">
                </div>
        				<div class="form-group">
        					<label>Telefon</label>
        					<div class="input-group">
        						<div class="input-group-prepend">
        							<span class="input-group-text"><i class="fas fa-phone"></i></span>
        						</div>
        						<input type="text" name ="tel" value = "<?php echo $firma_bilgileri[ 'tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
        					</div>
        					<!-- /.input group -->
        				</div>				  
                <div class="form-group">
                  <label  class="control-label">Adres</label>
                    <textarea class="form-control" name ="adres" value = "" required placeholder=""><?php echo $firma_bilgileri[ 'adres' ]; ?></textarea>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <button modul= 'firmalar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
                  <button onclick="window.location.href = '?modul=firmalar&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
              </div>
            </form>
          </div>
          <!-- /.card -->

        </div>
          <!--/.col (left) -->
          <!-- right column -->

        </div>
        <!-- /.row -->


