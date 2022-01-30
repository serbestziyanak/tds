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

$SQL_oku = <<< SQL
SELECT
  *
FROM
  tb_subeler
SQL;

$SQL_sube_bilgileri = <<< SQL
SELECT
  *
FROM
  tb_subeler
WHERE
  id = ?
SQL;

$SQL_ara = <<< SQL
SELECT
  *
FROM 
  tb_subeler
WHERE
  adi
LIKE 
    ?
SQL;

$SQL_toplam_veri = <<< SQL
SELECT 
  count(id)
FROM 
  tb_subeler
SQL;

  session_start();
  if(isset($_POST['limit-belirle'])){
      $_SESSION['limit-belirle'] = $_POST['limit-belirle'];
  }


$limit = isset($_SESSION["limit-belirle"]) ? $_SESSION["limit-belirle"] : 5;
  $sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 1;
  $baslangic = ($sayfa - 1) * $limit;

$SQL_sayfala = <<< SQL
SELECT
 * 
FROM 
  tb_subeler
ORDER BY 
  id 
ASC 
LIMIT
   $baslangic
  ,$limit
SQL;

  
  if($sayfa)
$sube_bilgileri = array(
   'id'       => $sube[ 2 ][ 'id' ]
  ,'adi'      => $sube[ 2 ][ 'adi' ]
);
  $sorgu = $vt->select( $SQL_sayfala, array($baslangic,$limit) );
  $sorgu2 = $vt->select( $SQL_toplam_veri, array() );
 // print_r ($sorgu2[2][0]['count(id)']);
  //echo $sorgu2[2][0]['count(id)'];

  $sayfalar = ceil( $sorgu2[2][0]['count(id)'] / $limit );
  $geri  = $sayfa - 1;
  $ileri = $sayfa + 1;
  //echo $sayfalar;
if($sayfa == $sayfalar)
{
  $subeler = $sorgu;
}else{
  $subeler = $vt->select( $SQL_oku, array() );
}

if(isset($_POST['arama'])){
   $aranan = "%".$_POST['table_search']."%";
   if(strlen($aranan) >= 3){
     $sorgu = $vt->select( $SQL_ara, array($aranan) );
      //print_r($sorgu);
    }else{
       echo "Bulunamadı!";
    }
 }
   
$sube_id     = array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
if(isset($_POST['arama'])){
	$subeler = $sorgu;
}else{
	$subeler    = $vt->select( $SQL_sayfala, array() );
}
$sube           = $vt->selectSingle( $SQL_sube_bilgileri, array( $sube_id ) );
$sube_bilgileri = array();
$islem          = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
	$sube_bilgileri = $sube[ 2 ];

//echo "limit:".$limit;
//echo "sayfa:".$sayfa;
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="subeler_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#subeler_sil_onay' ).on( 'show.bs.modal', function( e ) {
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
                <h3 class="card-title">Şubeler</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-sm table-bordered table-hover">
                  <thead>
                    <tr>
                      <th style="width: 15px">#</th>
                      <th>Adı</th>
                      <th>Firma</th>
                      <th>Ünvan</th>
                      <th>Tel</th>
                      <th data-priority="1" style="width: 20px">Düzenle</th>
                      <th data-priority="1" style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php $sayi = ($sayfa-1)*$limit+1;  foreach( $subeler[ 2 ] AS $sube ) { ?>
                  <tr>
                    <td><?php echo $sayi++; ?></td>
                    <td><b><?php echo $sube[ 'adi' ]; ?></b></td>
                    <td><?php echo $sube[ 'firma' ]; ?></td>
                    <td><?php echo $sube[ 'unvan' ]; ?></td>
                    <td><?php echo $sube[ 'tel' ]; ?></td>
                    <td align = "center">
                      <a modul = 'subeler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=subeler&islem=guncelle&id=<?php echo $sube[ 'id' ]; ?>" >
                        Düzenle
                      </a>
                    </td>
                    <td align = "center">
                      <button modul = 'subeler' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/subeler/subelerSEG.php?islem=sil&id=<?php echo $sube[ 'id' ]; ?>" data-toggle="modal" data-target="#subeler_sil_onay" >Sil</button>
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
                <h3 class="card-title">Şube Ekle / Güncelle</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form id = "kayit_formu" action = "_modul/subeler/subelerSEG.php" method = "POST">
                <div class="card-body">
                  <input type = "hidden" name = "id" value = "<?php echo $sube_bilgileri[ 'id' ]; ?>">
                  <input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
                  <div class="form-group">
                    <label  class="control-label">Şube Adı</label>
                      <input type="text" class="form-control" name ="sube_adi" value = "<?php echo $sube_bilgileri[ 'adi' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Firma</label>
                      <input type="text" class="form-control" name ="firma" value = "<?php echo $sube_bilgileri[ 'firma' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Ünvan</label>
                      <input type="text" class="form-control" name ="unvan" value = "<?php echo $sube_bilgileri[ 'unvan' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Vergi No</label>
                      <input type="text" class="form-control" name ="vergi_no" value = "<?php echo $sube_bilgileri[ 'vergi_no' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Vergi Dairesi</label>
                      <input type="text" class="form-control" name ="vergi_dairesi" value = "<?php echo $sube_bilgileri[ 'vergi_dairesi' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Ticaret Sicil No</label>
                      <input type="text" class="form-control" name ="ticaret_sicil_no" value = "<?php echo $sube_bilgileri[ 'ticaret_sicil_no' ]; ?>" required placeholder="">
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Yetki Belgesi No</label>
                      <input type="text" class="form-control" name ="yetki_belgesi_no" value = "<?php echo $sube_bilgileri[ 'yetki_belgesi_no' ]; ?>" required placeholder="">
                  </div>
				<div class="form-group">
					<label>Telefon</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-phone"></i></span>
						</div>
						<input type="text" name ="tel" value = "<?php echo $sube_bilgileri[ 'tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
					</div>
					<!-- /.input group -->
				</div>				  
                  <div class="form-group">
                    <label  class="control-label">Adres</label>
                      <textarea class="form-control" name ="adres" value = "" required placeholder=""><?php echo $sube_bilgileri[ 'adres' ]; ?></textarea>
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


