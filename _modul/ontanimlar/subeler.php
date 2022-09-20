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
WHERE 
  firma_id = ?
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
WHERE 
  firma_id  = ? AND
  aktif     = 1
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
  $sorgu = $vt->select( $SQL_sayfala, array( $_SESSION[ "firma_id" ], $baslangic,$limit) );
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
  $subeler = $vt->select( $SQL_oku, array(  $_SESSION[ "firma_id" ]) );
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
	$subeler    = $vt->select( $SQL_sayfala, array( $_SESSION[ "firma_id" ] ) );
}
$sube           = $vt->selectSingle( $SQL_sube_bilgileri, array( $sube_id ) );
$sube_bilgileri = array();
$islem          = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
	$sube_bilgileri = $sube[ 2 ];


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

<script>  
  $(document).ready(function() {
    $('#limit-belirle').change(function() {
		
        $(this).closest('form').submit();
		
    });
});
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
                  <?php $sayi = ($sayfa-1)*$limit+1;  foreach( $subeler[ 2 ] AS $sube ) { ?>
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


