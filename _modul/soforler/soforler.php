<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'sofor_id' ] = $_SESSION[ 'sonuclar' ][ 'sofor_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
	s.*
	,f.adi AS firma_adi 
	,f.id AS firma_id
FROM
	tb_soforler AS s 
LEFT JOIN
	tb_firmalar AS f ON s.firma_id = f.id
SQL;

$SQL_sofor_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_soforler
WHERE
	id = ?
SQL;


$SQL_firmalar = <<< SQL
SELECT * FROM tb_firmalar
SQL;

$sofor_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$soforler				= $vt->select( $SQL_oku, array() );
$firmalar				= $vt->select( $SQL_firmalar, array() );
$sofor					= $vt->selectSingle( $SQL_sofor_bilgileri, array( $sofor_id ) );
$sofor_bilgileri		= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
$sofor_bilgileri = array(
	 'id'			=> $sofor[ 2 ][ 'id' ]
	,'adi'			=> $sofor[ 2 ][ 'adi' ]
	,'soyadi'		=> $sofor[ 2 ][ 'soyadi' ]
	,'cep_telefonu'	=> $sofor[ 2 ][ 'cep_telefonu' ]
	,'iban'			=> $sofor[ 2 ][ 'iban' ]
	,'firma_id'		=> $sofor[ 2 ][ 'firma_id' ]
);
?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="soforler_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#soforler_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

        <div class="row">
		<div class="col-md-8">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Şöförler</h3>
                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Ara">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-sm table-bordered table-striped">
                  <thead>
                    <tr>
						<th style="width: 15px">#</th>
						<th>Adı</th>
						<th>Soyadı</th>
						<th>Cep Telefonu</th>
						<?php if( !$fn->mobilCihaz() ) { ?>
						<th>İban</th>
						<th>Firma</th>
						<?php } ?>
						<th style="width: 20px">Düzenle</th>
						<th style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
					<?php $sayi = 1; foreach( $soforler[ 2 ] AS $sofor ) { ?>
					<tr>
						<td><?php echo $sayi++; ?></td>
						<td><?php echo $sofor[ 'adi' ]; ?></td>
						<td><?php echo $sofor[ 'soyadi' ]; ?></td>
						<td><?php echo $sofor[ 'cep_telefonu' ]; ?></td>
						<?php if( !$fn->mobilCihaz() ) { ?>
						<td><?php echo $sofor[ 'iban' ]; ?></td>
						<td><?php echo $sofor[ 'firma_adi' ]; ?></td>
						<?php } ?>
						<td align = "center">
							<a modul= 'soforler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=soforler&islem=guncelle&id=<?php echo $sofor[ 'id' ]; ?>" >
								Düzenle
							</a>
						</td>
						<td align = "center">
							<button modul= 'soforler' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/soforler/soforlerSEG.php?islem=sil&id=<?php echo $sofor[ 'id' ]; ?>" data-toggle="modal" data-target="#soforler_sil_onay" >Sil</button>
						</td>
					</tr>
					<?php } ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                  <li class="page-item"><a class="page-link" href="#">«</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">»</a></li>
                </ul>
              </div>
            </div>
            <!-- /.card -->

          </div>
          <!-- left column -->
          <div class="col-md-4">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Şöför Ekle / Güncelle</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form id = "kayit_formu" action = "_modul/soforler/soforlerSEG.php" method = "POST">
                <div class="card-body">
					<input type = "hidden" name = "id" value = "<?php echo $sofor_bilgileri[ 'id' ]; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">Adı</label>
							<input type="text" class="form-control" name ="sofor_adi" value = "<?php echo $sofor_bilgileri[ 'adi' ]; ?>" required placeholder="Adı">
					</div>
					<div class="form-group">
						<label  class="control-label">Soyadı</label>
							<input type="text" class="form-control" name ="sofor_soyadi" value = "<?php echo $sofor_bilgileri[ 'soyadi' ]; ?>" required placeholder="Soyadı">
					</div>
					<div class="form-group">
						<label  class="control-label">Cep Telefon</label>
							<input type="text" class="form-control" name ="sofor_cep_telefonu" value = "<?php echo $sofor_bilgileri[ 'cep_telefonu' ]; ?>" required placeholder="Cep Telefon">
					</div>
					<div class="form-group">
						<label  class="control-label">Iban</label>
							<input type="text" class="form-control" name ="sofor_iban" value = "<?php echo $sofor_bilgileri[ 'iban' ]; ?>" required placeholder="Iban">
					</div>
					<div class="form-group">
						<label  class="control-label">Firma</label>
							<select  class="form-control" name = "sofor_firma_id" required>
									<option value = "" >Seçiniz</option>
								<?php foreach( $firmalar[ 2 ] AS $firma ) { ?>
									<option value = "<?php echo $firma[ 'id' ]; ?>" <?php if( $firma[ 'id' ] ==  $sofor_bilgileri[ 'firma_id' ] ) echo 'selected'?>><?php echo $firma[ 'adi' ]?></option>
								<?php } ?>
							</select>
					</div>
				</div>
                <!-- /.card-body -->
                <div class="card-footer">
					<button modul= 'soforler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					<a type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=soforler&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
                </div>
              </form>
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->
          <!-- right column -->

        </div>
        <!-- /.row -->


