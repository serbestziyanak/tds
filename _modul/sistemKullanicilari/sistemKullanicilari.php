<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'sistem_kullanici_id' ] = $_SESSION[ 'sonuclar' ][ 'sistem_kullanici_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_roller = <<< SQL
SELECT
	 id
	,adi
FROM
	tb_roller
-- WHERE id IN( SELECT gorulecek_rol_id FROM tb_gorulecek_roller WHERE rol_id = ? )
SQL;

$SQL_universiteler = <<< SQL
SELECT
	*
FROM
	tb_universiteler
WHERE 
	aktif = 1
SQL;

$SQL_sistem_kullanicilari = <<< SQL
SELECT
	 ku.*
	,r.adi AS rol_adi
FROM
	tb_sistem_kullanici AS ku
JOIN
	tb_roller AS r ON ku.rol_id = r.id
WHERE
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE ku.id = ?
	END
ORDER BY adi 
SQL;

$SQL_sistem_kullanici = <<< SQL
SELECT
	 ku.*
	,r.adi AS rol_adi
FROM
	tb_sistem_kullanici AS ku
JOIN
	tb_roller AS r ON ku.rol_id = r.id
WHERE
	ku.id = ?
SQL;

$sistem_kullanici_id	= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$roller								= $vt->select( $SQL_roller, array( $_SESSION[ 'rol_id' ] ) );
$universiteler				= $vt->select( $SQL_universiteler, array(  ) );
$sistem_kullanici			= $vt->selectSingle( $SQL_sistem_kullanici, array( $sistem_kullanici_id ) );
$sistem_kullanicilari	= $vt->select( $SQL_sistem_kullanicilari, array( $_SESSION[ 'super' ] , $_SESSION[ 'kullanici_id' ]) );
$kullaniciBilgileri		= array( "resim"=>'resimler/resim_yok.jpg', "ad_soyad" => '<h6 align = "center">Resim eklemek için fotoğrafa tıklayınız</h6>' );
$islem								= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'guncelle' )
$kullaniciBilgileri = array(
	 'id'						=> $sistem_kullanici[ 2 ][ 'id' ]
	,'universite_id'=> $sistem_kullanici[ 2 ][ 'universite_id' ]
	,'universiteler'=> $sistem_kullanici[ 2 ][ 'universiteler' ]
	,'adi'					=> $sistem_kullanici[ 2 ][ 'adi' ]
	,'soyadi'				=> $sistem_kullanici[ 2 ][ 'soyadi' ]
	,'email'				=> $sistem_kullanici[ 2 ][ 'email' ]
	,'sifre'				=> $sistem_kullanici[ 2 ][ 'sifre' ]
	,'telefon'			=> $sistem_kullanici[ 2 ][ 'telefon' ]
	,'tc_no'				=> $sistem_kullanici[ 2 ][ 'tc_no' ]
	,'dogum_tarihi'	=> explode( ' ', $sistem_kullanici[ 2 ][ 'dogum_tarihi' ] )[ 0 ]
	,'rol_id'				=> $sistem_kullanici[ 2 ][ 'rol_id' ]
	,'rol_adi'			=> $sistem_kullanici[ 2 ][ 'rol_adi' ]
	,'super'				=> $sistem_kullanici[ 2 ][ 'super' ]
	,'resim'				=> 'resimler/' . $sistem_kullanici[ 2 ][ 'resim' ]
	,'ad_soyad'			=> $sistem_kullanici[ 2 ][ 'adi' ] . " " . $sistem_kullanici[ 2 ][ 'soyadi' ]
);



$satir_renk				= $sistem_kullanici_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $sistem_kullanici_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $sistem_kullanici_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';
?>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kullanici_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kullanıcıyı <b>Silmek</b> istediğinize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#kullanici_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

        <div class="row">
          <div class="col-md-8">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Sistem Kullanıcıları</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr class="">
							<th style="width: 15px">#</th>
							<th>Adı</th>
							<th>Soyadı</th>
							<?php if( !$fn->mobilCihaz() ) { ?>
								<th>Kullanıcı Adı</th>
								<th>Rol</th>
							<?php } ?>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
							<th data-priority="1" style="width: 10px">Süper</th>
						</tr>
					</thead>
					<tbody>
						<?php $sayi = 1; foreach( $sistem_kullanicilari[ 2 ] AS $kul ) { ?>
						<tr <?php if( $kul[ 'id' ] == $sistem_kullanici_id ) echo "class = '$satir_renk'"; ?>>
							<td><?php echo $sayi++; ?></td>
							<td><?php echo $kul[ 'adi' ]; ?></td>
							<td><?php echo $kul[ 'soyadi' ]; ?></td>
							<?php if( !$fn->mobilCihaz() ) { ?>
							<td><?php echo $kul[ 'email' ]; ?></td>
							<td><?php echo $kul[ 'rol_adi' ]; ?></td>
							<?php } ?>
							<td align = "center">
								<a modul= 'sistemKullanicilari' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs " href = "?modul=sistemKullanicilari&islem=guncelle&id=<?php echo $kul[ 'id' ]; ?>" >
									Düzenle
								</a>
							</td>
							<td align = "center">
								<button modul= 'sistemKullanicilari' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/sistemKullanicilari/sistemKullanicilariSEG.php?super=<?php echo $kul[ 'super' ]?>&islem=sil&id=<?php echo $kul[ 'id' ]; ?>" data-toggle="modal" data-target="#kullanici_sil_onay" >Sil</button>
							</td>
							<td align = "center" valign = "center">
								<?php if( $kul[ 'super' ] * 1 > 0 ) { ?><i class='fa fa-check-circle text-green'></i><?php } ?>
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
                <h3 class="card-title">Sistem Kullanıcıları Ekle / Güncelle</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

				<form class="form-horizontal" id = "kayit_formu" action = "_modul/sistemKullanicilari/sistemKullanicilariSEG.php" method = "POST" enctype="multipart/form-data">
					<div class="card-body">
					<div class="text-center">
					  <img class="img-fluid img-circle img-thumbnail mw-100"
						   style="width:120px;"
						   src="<?php echo $kullaniciBilgileri[ 'resim' ]; ?>" id = "sistem_kullanici_resim" 
						   alt="User profile picture"
						   id = "sistem_kullanici_resim">
					</div>

					<h3 class="profile-username text-center"><?php echo $kullaniciBilgileri[ 'ad_soyad' ]; ?></h3>

					<p class="text-muted text-center"><?php if( $kullaniciBilgileri[ 'super' ] != 1 ) echo $kullaniciBilgileri[ 'rol_adi' ]; else echo "Süper Yetkili Kullanıcı"; ?></p>
				
					<input type="file" id="gizli_input_file" name = "input_sistem_kullanici_resim" style = "display:none;" name = "resim" accept="image/gif, image/jpeg, image/png"  onchange="resimOnizle(this)"; />
					<input type = "hidden" name = "id" value = "<?php echo $kullaniciBilgileri[ 'id' ]; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">TC No</label>
						<input required type="text" class="form-control" name ="sistem_kullanici_tc_no" value = "<?php echo $kullaniciBilgileri[ 'tc_no' ]; ?>">
					</div>
					<div class="form-group">
						<label  class="control-label">Adı</label>
						<input required type="text" class="form-control" name ="sistem_kullanici_adi" value = "<?php echo $kullaniciBilgileri[ 'adi' ]; ?>">
					</div>
					<div class="form-group">
						<label  class="control-label">Soyadı</label>
						<input required type="text" class="form-control" name ="sistem_kullanici_soyadi" value = "<?php echo $kullaniciBilgileri[ 'soyadi' ]; ?>">
					</div>
					<div class="form-group">
					  <label>Cep Telefonu:</label>

					  <div class="input-group">
						<div class="input-group-prepend">
						  <span class="input-group-text"><i class="fas fa-phone"></i></span>
						</div>
						<input required type="text" name ="sistem_kullanici_telefon" value = "<?php echo $kullaniciBilgileri[ 'telefon' ]; ?>" class="form-control" data-inputmask='"mask": "0(999) 999-9999"' data-mask>
					  </div>
					  <!-- /.input group -->
					</div>
					<div class="form-group">
					  <label class="control-label">Doğum Tarihi</label>
						<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input required type="text" name="sistem_kullanici_dogum_tarihi" value="<?php if( $kullaniciBilgileri[ 'dogum_tarihi' ] !='' ){echo date('d.m.Y',strtotime($kullaniciBilgileri[ 'dogum_tarihi' ] ));}//else{ echo date('d.m.Y'); } ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1"/>
						</div>
					</div>

					<div class="form-group">
						<label  class="control-label">Email</label>
							<input required type="email" class="form-control" name ="sistem_kullanici_email" value = "<?php echo $kullaniciBilgileri[ 'email' ]; ?>" placeholder="Eposta">
					</div>
					<div class="form-group">
						<label  class="control-label">Şifre</label>
							<input required type="password" class="form-control" name ="sistem_kullanici_sifre" value = "<?php echo $kullaniciBilgileri[ 'sifre' ]; ?>">
					</div>
					<div class="form-group">
						<label  class="control-label">Rolü</label>
							<select  class="form-control select2" name = "sistem_kullanici_rol_id" required>
								<?php foreach( $roller[ 2 ] AS $rol ) { ?>
									<option value = "<?php echo $rol[ 'id' ]; ?>" <?php if( $rol[ 'id' ] ==  $kullaniciBilgileri[ 'rol_id' ] ) echo 'selected'?>><?php echo $rol[ 'adi' ]?></option>
								<?php } ?>
							</select>
					</div>
					<div class="form-group">
						<label  class="control-label">Üniversite</label>
							<select   class="form-control select2"  multiple="multiple" name = "universite_id[]" required>
									<option>Seçiniz</option>
								<?php foreach( $universiteler[ 2 ] AS $universite ) { 
										$universiteler2 = explode(",", $kullaniciBilgileri[ 'universiteler' ]);
								?>
									<option value = "<?php echo $universite[ 'id' ]; ?>" <?php if( in_array($universite[ 'id' ], $universiteler2) ) echo 'selected'?>><?php echo $universite[ 'adi' ]?></option>
								<?php } ?>
							</select>
					</div>

					<div modul= 'sistemKullanicilari' yetki_islem="super" class="form-group">
						<div class='material-switch pull-right' style ="padding-top:10px">
							<label class="control-label">Süper Kullanıcı</label>
							<input id='sistem_kullanici_super' name='sistem_kullanici_super' type="checkbox" <?php if ( $kullaniciBilgileri[ 'super' ] * 1 > 0 ) echo 'checked'; ?> data-bootstrap-switch data-off-color="danger" data-on-color="success">
						</div>
					</div>
				</div>
				<div class="card-footer">
						<button modul= 'sistemKullanicilari' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
						<a modul= 'sistemKullanicilari' yetki_islem="ekle" type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=sistemKullanicilari&islem=ekle" ><span class="fa fa-plus"></span> Temizle / Yeni Kullanıcı</a>
				</div>
				</form>
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->
          <!-- right column -->

        </div>
        <!-- /.row -->

<script>


	
/* Kullanıcı resmine tıklayınca file nesnesini tetikle*/
$( function() {
	$( "#sistem_kullanici_resim" ).click( function() {
		$( "#gizli_input_file" ).trigger( 'click' );
	});
});

/* Seçilen resim önizle */
function resimOnizle( input ) {
	if ( input.files && input.files[ 0 ] ) {
		var reader = new FileReader();
		reader.onload = function ( e ) {
			$( '#sistem_kullanici_resim' ).attr( 'src', e.target.result );
		};
		reader.readAsDataURL( input.files[ 0 ] );
	}
}
</script>
<script type="text/javascript">
	var simdi = new Date(); 
	//var simdi="11/25/2015 15:58";
	$(function () {
		$('#datetimepicker1').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy',
			icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
			}
		});
	});
	
</script>
