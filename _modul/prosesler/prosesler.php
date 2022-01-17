<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu		= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'arac_id' ] = $_SESSION[ 'sonuclar' ][ 'arac_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
SELECT
	*
FROM
	tb_araclar 
WHERE 
	aktif = 1
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE sube_id in ($yetkili_subeler)
	END
ORDER BY arac_no DESC
SQL;

$SQL_arac_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_araclar
WHERE
	id = ?
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE sube_id in ($yetkili_subeler)
	END
SQL;

$SQL_arac_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_tipleri 
SQL;

$SQL_arac_kasa_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_kasa_tipleri 
SQL;

$SQL_arac_vites_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_vites_tipleri 
SQL;

$SQL_arac_vites_sayilari = <<< SQL
SELECT
	*
FROM
	tb_arac_vites_sayilari 
SQL;

$SQL_arac_yakit_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_yakit_tipleri 
SQL;

$SQL_arac_cekis_tipleri = <<< SQL
SELECT
	*
FROM
	tb_arac_cekis_tipleri 
SQL;

$SQL_arac_renkleri = <<< SQL
SELECT
	*
FROM
	tb_arac_renkleri
SQL;

$SQL_arac_markalari = <<< SQL
SELECT
	*
FROM
	tb_arac_markalari
SQL;

$SQL_arac_lastik_tipi = <<< SQL
SELECT
	*
FROM
	tb_arac_lastik_tipleri
SQL;

$SQL_arac_yayin_yerleri = <<< SQL
SELECT
	*
FROM
	tb_arac_yayin_yerleri
SQL;

$SQL_arac_medya = <<< SQL
SELECT
	*
FROM
	tb_arac_medya
WHERE
	arac_id = ?
ORDER BY kapak_foto desc,id
SQL;

$SQL_arac_yayinlari = <<< SQL
SELECT 
	ay.*
	,ayy.adi AS yayin_yeri_adi
	,ayy.logo
FROM
	tb_arac_yayinlari AS ay
LEFT JOIN tb_arac_yayin_yerleri AS ayy ON ayy.id = ay.yayin_yeri_id
WHERE
	arac_id = ?
SQL;

$SQL_arac_expertiz = <<< SQL
SELECT
	*
FROM
	tb_arac_expertiz
WHERE
	arac_id = ?
SQL;

$SQL_yayin_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_arac_yayinlari
WHERE
	id = ?
SQL;

$SQL_arac_expertiz_boya = <<< SQL
SELECT
	*
FROM
	tb_arac_expertiz_boya_degisen
SQL;

$SQL_arac_expertiz_kaporta = <<< SQL
SELECT
	*
FROM
	tb_arac_expertiz_kaporta
SQL;

$SQL_prosesler_eksik_alan_sayisi_guncelle = <<< SQL
UPDATE
	tb_araclar
SET
	prosesler_eksik_alan_sayisi = ?
WHERE
	id = ?
SQL;

$SQL_firmalar = <<< SQL
SELECT * FROM tb_firmalar
SQL;

$arac_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$arac					= $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id, $_SESSION[ 'super' ]  ) );
$arac_id				= $arac[ 2 ]['id'];
$araclar				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$arac_tipleri			= $vt->select( $SQL_arac_tipleri, array() );
$arac_kasa_tipleri		= $vt->select( $SQL_arac_kasa_tipleri, array() );
$arac_vites_tipleri		= $vt->select( $SQL_arac_vites_tipleri, array() );
$arac_vites_sayilari	= $vt->select( $SQL_arac_vites_sayilari, array() );
$arac_yakit_tipleri		= $vt->select( $SQL_arac_yakit_tipleri, array() );
$arac_cekis_tipleri		= $vt->select( $SQL_arac_cekis_tipleri, array() );
$arac_renkleri			= $vt->select( $SQL_arac_renkleri, array() );
$arac_markalari			= $vt->select( $SQL_arac_markalari, array() );
$arac_lastik_tipleri	= $vt->select( $SQL_arac_lastik_tipi, array() );
$arac_expertiz_boya		= $vt->select( $SQL_arac_expertiz_boya, array() );
$arac_expertiz_kaporta	= $vt->select( $SQL_arac_expertiz_kaporta, array() );
$arac_yayin_yerleri		= $vt->select( $SQL_arac_yayin_yerleri, array() );
$arac_yayinlari			= $vt->select( $SQL_arac_yayinlari, array( $arac_id ) );
$arac_yayini			= $vt->selectSingle( $SQL_yayin_bilgileri, array( $_REQUEST[ 'yayin_id' ] ) );
$arac_expertiz			= $vt->selectSingle( $SQL_arac_expertiz, array( $arac_id ) );
$arac_medya				= $vt->select( $SQL_arac_medya, array( $arac_id ) );
$arac_bilgileri			= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';
$modul					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'modul' ] : 'prosesler';

if( $modul == 'prosesler' ){
	//if( $arac[ 2 ]['id'] == '' ){
	//	echo "<h1>Yetkiniz Bulunmamaktdır</h1>";
	//	exit;
	//}
	$arac_bilgileri				= $arac[ 2 ];
	$arac_expertiz_bilgileri	= $arac_expertiz[ 2 ];
	$arac_medya_bilgileri		= $arac_medya[ 2 ];
	$arac_medya_sayisi			= $arac_medya[ 3 ];
	$arac_yayin_bilgileri		= $arac_yayini[ 2 ];
}

include "kontrol.php";
$prosesler_eksik_alan_sayisi = prosesler_genel_kontrol($arac_bilgileri,$arac_expertiz_bilgileri,$arac_medya_sayisi,$arac_yayinlari);
$sorgu_sonuc = $vt->update( $SQL_prosesler_eksik_alan_sayisi_guncelle, array(
	 $prosesler_eksik_alan_sayisi
	,$arac_id
) );

?>
<div class="modal fade" id="arac_sec_modal">
	<div class="modal-dialog">
	  <div class="modal-content bg-secondary">
		<div class="modal-header">
		  <h4 class="modal-title">Detaylarını görmek istediğiniz aracı seçiniz</h4>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<form id = "arac_sec_form" action = "index.php" method = "GET">
			<div class="modal-body">
				<input type = "hidden" name = "modul" value = "prosesler">
				<input type = "hidden" name = "islem" value = "prosesler">
				<input type = "hidden" name = "tab_no" value = "1">
				<div class="form-group">
					<label  class="control-label">Araçlar</label>
						<select  class="form-control select2" name = "id" required>
								<option value="">Seçiniz</option>
							<?php foreach( $araclar[ 2 ] AS $arac_sec ) { ?>
								<option value = "<?php echo $arac_sec[ 'id' ]; ?>" <?php if( $arac_sec[ 'id' ] ==  $arac_bilgileri[ 'id' ] ) echo 'selected'?>><?php echo $arac_sec[ 'arac_no' ]." (".$arac_sec[ 'plaka' ].")"?></option>
							<?php } ?>
						</select>
				</div>
			</div>
			<div class="modal-footer justify-content-between">
			  <button type="button" class="btn btn-outline-light" data-dismiss="modal">Kapat</button>
			  <button  modul= 'prosesler' yetki_islem="arac_sec" type="submit" class="btn btn-outline-light">Araç Seç</button>
			</div>
		</form>
	  </div>
	  <!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="aracListesi_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#aracListesi_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<div class="modal fade" id="prosesler_onayla_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kaydı <b>Onaylamak</b> istediğinize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#prosesler_onayla_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
        <div class="row">
          <div class="col-12">
			<h3><i class="fas fa-file-signature"></i> Hizmet Prosesleri
			<div class="float-md-right">
				<a class = "btn btn-sm btn-secondary" style="width:200px;" href = "?modul=araclar&islem=detaylar&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Araç Detayları Göster
				</a>
				<button disabled class = "btn btn-sm btn-outline-secondary" style="width:200px;" href = "?modul=prosesler&islem=prosesler&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Prosesleri Göster
				</button>
				<a class = "btn btn-sm btn-secondary" style="width:200px;" href = "?modul=aracSatis&islem=satis&tab_no=7&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Araç Satış İşlemleri
				</a>
			</div>
			</h3>
            <div class="card card-secondary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
				<?php
					if( $arac[ 2 ]['id'] == '' or $arac[ 2 ]['id'] == null ){
				?>
                  <li class="pt-2 px-3"><a href="#" data-toggle="modal" data-target="#arac_sec_modal"><h3 class="card-title"><?php if( $arac_bilgileri[ 'arac_no' ] !="" ){ ?><b><?php echo $arac_bilgileri[ 'arac_no' ]; ?></b> (<?php echo $arac_bilgileri[ 'plaka' ]; ?>)<?php }else{?>Araç Seç<?php } ?></h3></a></li>
                  <li class="nav-item">
                    <a class="nav-link">
						&nbsp;
					</a>					
				  </li>
				<?php
					}else{
				?>
                  <li class="pt-2 px-3"><a href="#" data-toggle="modal" data-target="#arac_sec_modal"><h3 class="card-title"><?php if( $arac_bilgileri[ 'arac_no' ] !="" ){ ?><b><?php echo $arac_bilgileri[ 'arac_no' ]; ?></b> (<?php echo $arac_bilgileri[ 'plaka' ]; ?>)<?php }else{?>Araç Seç<?php } ?></h3></a></li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "active"; ?> " id="arac_bilgi1_tab" data-toggle="pill" href="#arac_bilgi1" role="tab" aria-controls="arac_bilgi1" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "true"; else echo "false"; ?>">
						Eşya
						<?php if( prosesler_esya_teslim($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "active"; ?> " id="arac_bilgi2_tab" data-toggle="pill" href="#arac_bilgi2" role="tab" aria-controls="arac_bilgi2" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "true"; else echo "false"; ?>">
						Temizlik
						<?php if( prosesler_arac_temizlik($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "active"; ?> " id="arac_bilgi3_tab" data-toggle="pill" href="#arac_bilgi3" role="tab" aria-controls="arac_bilgi3" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "true"; else echo "false"; ?>">
						Tramer
						<?php if( prosesler_tramer($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "active"; ?> " id="arac_bilgi4_tab" data-toggle="pill" href="#arac_bilgi4" role="tab" aria-controls="arac_bilgi4" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "true"; else echo "false"; ?>">
						Expertiz
						<?php if( prosesler_expertiz($arac_expertiz_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "active"; ?> " id="arac_bilgi5_tab" data-toggle="pill" href="#arac_bilgi5" role="tab" aria-controls="arac_bilgi5" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "true"; else echo "false"; ?>">
						Gözle Kontrol
						<?php if( prosesler_gozle_kontrol($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "active"; ?> " id="arac_bilgi6_tab" data-toggle="pill" href="#arac_bilgi6" role="tab" aria-controls="arac_bilgi6" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "true"; else echo "false"; ?>">
						Lastik
						<?php if( prosesler_lastik($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "active"; ?> " id="arac_bilgi7_tab" data-toggle="pill" href="#arac_bilgi7" role="tab" aria-controls="arac_bilgi7" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "true"; else echo "false"; ?>">
						Akü
						<?php if( prosesler_aku($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "active"; ?> " id="arac_bilgi8_tab" data-toggle="pill" href="#arac_bilgi8" role="tab" aria-controls="arac_bilgi8" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "true"; else echo "false"; ?>">
						Medya
						<?php if( prosesler_medya($arac_medya_sayisi) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "active"; ?> " id="arac_bilgi10_tab" data-toggle="pill" href="#arac_bilgi10" role="tab" aria-controls="arac_bilgi10" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "true"; else echo "false"; ?>">
						Onay
						<?php if( prosesler_onay($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
				  <?php if( $arac_bilgileri[ 'onaya_gonderildi' ] == 1 and $arac_bilgileri[ 'onaylandi' ] == 1 ){ ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 11 ) echo "active"; ?> " id="arac_bilgi11_tab" data-toggle="pill" href="#arac_bilgi11" role="tab" aria-controls="arac_bilgi11" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 11 ) echo "true"; else echo "false"; ?>">
						İlan
						<?php if( prosesler_yayin($arac_yayinlari) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "active"; ?> " id="arac_bilgi9_tab" data-toggle="pill" href="#arac_bilgi9" role="tab" aria-controls="arac_bilgi9" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "true"; else echo "false"; ?>">
						Yayın
						<?php if( prosesler_yayin($arac_yayinlari) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
				  <?php } ?>
				<?php 
					}
				?>
                </ul>
              </div>
			<?php
				if( $arac[ 2 ]['id'] == '' or $arac[ 2 ]['id'] == null ){
					echo "<h5>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class='fas fa-level-up-alt text-red'></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Araç Seçiniz</h5>";
					//exit;
				}else{
			?>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 1 ) echo "show active"; ?>" id="arac_bilgi1" role="tabpanel" aria-labelledby="arac_bilgi1_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "1">
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Özel Eşya Teslim</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses1_esya_teslim1" name="proses1_esya_teslim" value="1" <?php if( $arac_bilgileri[ 'proses1_esya_teslim' ] == "1" ) echo 'checked'?> >
									<label for="proses1_esya_teslim1">
										Teslim Edildi
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses1_esya_teslim2" name="proses1_esya_teslim" value="2" <?php if( $arac_bilgileri[ 'proses1_esya_teslim' ] == "2" ) echo 'checked'?> >
									<label for="proses1_esya_teslim2">
										Özel Eşya Yok
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
                  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "show active"; ?>" id="arac_bilgi2" role="tabpanel" aria-labelledby="arac_bilgi2_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu2" action = "_modul/prosesler/proseslerSEG.php" method = "POST" class="" >
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "2">
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Araç Temizlik</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses2_arac_temizlik1" name="proses2_arac_temizlik" value="1" <?php if( $arac_bilgileri[ 'proses2_arac_temizlik' ] == "1" ) echo 'checked'?> >
									<label for="proses2_arac_temizlik1">
										Araç Temiz
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses2_arac_temizlik2" name="proses2_arac_temizlik" value="2" <?php if( $arac_bilgileri[ 'proses2_arac_temizlik' ] == "2" ) echo 'checked'?> >
									<label for="proses2_arac_temizlik2">
										Yıkamaya Gönder
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "show active"; ?>" id="arac_bilgi3" role="tabpanel" aria-labelledby="arac_bilgi3_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu3" action = "_modul/prosesler/proseslerSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "3">
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Tramer Sorgusu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses3_tramer_kontrolu_yapildi1" name="proses3_tramer_kontrolu_yapildi" value="1" <?php if( $arac_bilgileri[ 'proses3_tramer_kontrolu_yapildi' ] == "1" ) echo 'checked'?> >
									<label for="proses3_tramer_kontrolu_yapildi1">
										Yapıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="proses3_tramer_kontrolu_yapildi2" name="proses3_tramer_kontrolu_yapildi" value="0" <?php if( $arac_bilgileri[ 'proses3_tramer_kontrolu_yapildi' ] == "0" ) echo 'checked'?> >
									<label for="proses3_tramer_kontrolu_yapildi2">
										Yapılmadı
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Ağır Hasar Sorgusu</label><br>
								  <div class="icheck-success d-inline">
									<input  type="radio" id="proses3_agir_hasar_sorgusu1" name="proses3_agir_hasar_sorgusu" value="1" <?php if( $arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == "1" ) echo 'checked'?> >
									<label for="proses3_agir_hasar_sorgusu1">
										Ağır Hasarlı
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input  type="radio" id="proses3_agir_hasar_sorgusu2" name="proses3_agir_hasar_sorgusu" value="2" <?php if( $arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == "2" ) echo 'checked'?> >
									<label for="proses3_agir_hasar_sorgusu2">
										Ağır Hasarlı Değil
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Kilometre Kontrolü</label>
								<input type="text" class="form-control" name ="proses3_km_kontrolu" value = "<?php echo $arac_bilgileri[ 'proses3_km_kontrolu' ]; ?>" placeholder="Km" >
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Tramer Kaydı</label><br>
								  <div class="icheck-success d-inline">
									<input  type="radio" id="proses3_tramer_kontrolu1" name="proses3_tramer_kontrolu" value="1" <?php if( $arac_bilgileri[ 'proses3_tramer_kontrolu' ] == "1" ) echo 'checked'?> >
									<label for="proses3_tramer_kontrolu1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input  type="radio" id="proses3_tramer_kontrolu2" name="proses3_tramer_kontrolu" value="2" <?php if( $arac_bilgileri[ 'proses3_tramer_kontrolu' ] == "2" ) echo 'checked'?> >
									<label for="proses3_tramer_kontrolu2">
										YOK
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Hasar Bilgileri</label>
								<textarea class="form-control" name ="proses3_hasar_bilgileri"  placeholder="Hasar Bilgileri" ><?php echo $arac_bilgileri[ 'proses3_hasar_bilgileri' ]; ?></textarea>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "show active"; ?>" id="arac_bilgi4" role="tabpanel" aria-labelledby="arac_bilgi4_tab">
					
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <input type="button" class="btn btn-secondary" onclick="printDiv('expertiz_div')" value="Yazdır" />
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST"  enctype="multipart/form-data" >
						<div class="card-body" id ="expertiz_div">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<input type = "hidden" name = "expertiz_id" value = "<?php echo $arac_expertiz_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "4">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-4">
											<img src="_modul/prosesler/expertiz.png" width="100%">
										</div>
										<div class="col-md-8">
											<h4>Ekspertiz Sorumlulukarı	</h4>	
											<p style="font-size:10pt;">														
												Mekanik kontroller fiziki kontrollerle gerçekleşmiştir. 														
												<br>Yukarıda belirtilen bilgiler değerlendirme amacıyla bağımsız bir değerlendirme kuruluşnda ücret karşılıığı yaptırılmıştır.														
												<br>Sadece ön bilgilendirme olarak değerlendirilmektedir.														
												<br>Raporun orijinal dokümanları belgeninin üstünde yazan "Test No" ile etiketlenmiştir.														
												<br>Bu rapor rapor tarihi itibariyle geçerli olup, araç satışı sırasında isteğe bağlı olarak yeni bir rapor düzenlenebilir. ALICI, ekspertiz işlemini bağımsız başka kuruluşlarda yaptırmayı da talep edebilir. İsteğe bağlı olarak yapılacak ekspertiz işlemlerinin ücreti ALICI tarafından ödenencektir. 														
												<br>														
												<br><b>OTOWOW</b> tüm kontrolleri <b>KENDİNE ALIR GİBİ</b> yapmaktadır.														
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Expertiz</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="expertiz_yapildi1" name="expertiz_yapildi" value="1" <?php if( $arac_expertiz_bilgileri[ 'expertiz_yapildi' ] == "1" ) echo 'checked'?> >
									<label for="expertiz_yapildi1">
										Yapıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="expertiz_yapildi2" name="expertiz_yapildi" value="0" <?php if( $arac_expertiz_bilgileri[ 'expertiz_yapildi' ] == "0" ) echo 'checked'?> >
									<label for="expertiz_yapildi2">
										Yapılmadı
									</label>
								  </div>
								</div>
							</div>
							
							<div class="card card-warning">
							  <!--div class="card-header">
								<h3 class="card-title text-white">Expertiz Raporu</h3>
							  </div-->
								<div class="card-body">

									<div class="row">
										<div class="form-group col-md-4">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text btn-primary">Araç No</span>
												</div>
												<input type="text" class="form-control" name ="" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>"  placeholder="" disabled>
											</div>
										</div>
										<div class="form-group col-md-4">
											<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
												<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
													<div class="input-group-text"><i class="fa fa-calendar"></i></div>
												</div>
												<input type="text" name="expertiz_tarihi" data-target="#datetimepicker1" data-toggle="datetimepicker" value="<?php if( $arac_expertiz_bilgileri['expertiz_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_expertiz_bilgileri['expertiz_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control  datetimepicker-input" data-target="#datetimepicker1"/>
											</div>
										</div>
										<div class="form-group col-md-4">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text primary">Test No</span>
												</div>
												<input type="text" class="form-control" name ="test_no" value = "<?php echo $arac_expertiz_bilgileri[ 'test_no' ]; ?>"  placeholder="">
											</div>
										</div>
										<div class="col-md-8">
											<table align="center">
												<tr>
													<th>
														
													</th>
													<th>
														Boya / Değişen
													</th>
													<th>
														Kaporta									
													</th>
												</tr>
												<tr>
													<td>
													<b>1. Sol Ön Çamurluk&nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sol_on_camurluk_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_on_camurluk_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sol_on_camurluk_kaporta_id_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_on_camurluk_kaporta_id_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>2. Sol Ön Kapı &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sol_on_kapi_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_on_kapi_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sol_on_kapi_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_on_kapi_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>3. Sol Arka Kapı &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sol_arka_kapi_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_arka_kapi_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sol_arka_kapi_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_arka_kapi_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>4. Sol Arka Çamurluk &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sol_arka_camurluk_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_arka_camurluk_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sol_arka_camurluk_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_arka_camurluk_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>5. Arka Tampon &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="arka_tampon_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'arka_tampon_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="arka_tampon_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'arka_tampon_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>6. Arka Bagaj Kapısı &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="arka_bagaj_kapisi_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'arka_bagaj_kapisi_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="arka_bagaj_kapisi_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'arka_bagaj_kapisi_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>7. Sağ Arka Çamurluk &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sag_arka_camurluk_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_arka_camurluk_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sag_arka_camurluk_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_arka_camurluk_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>8. Sağ Arka Kapı &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sag_arka_kapi_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_arka_kapi_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sag_arka_kapi_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_arka_kapi_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>9. Sağ Ön Kapı &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sag_on_kapi_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_on_kapi_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sag_on_kapi_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_on_kapi_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>10. Sağ Ön Çamurluk &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sag_on_camurluk_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_on_camurluk_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sag_on_camurluk_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_on_camurluk_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>11. Ön Tampon &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="on_tampon_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'on_tampon_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="on_tampon_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'on_tampon_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>12. Ön Kaput &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="on_kaput_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'on_kaput_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="on_kaput_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'on_kaput_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>13. Tavan &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="tavan_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'tavan_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="tavan_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'tavan_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>14. Sol Marşpiyel &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sol_marspiyel_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_marspiyel_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sol_marspiyel_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sol_marspiyel_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
												<tr>
													<td>
													<b>15. Sağ Marşpiyel &nbsp;&nbsp;&nbsp;</b>
													</td>
													<td>
														<select onchange="expertiz_arac_renklendir(this)" name="sag_marspiyel_boya_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_boya[ 2 ] AS $boya_degisen ) { ?>
															<option value = "<?php echo $boya_degisen[ 'id' ]; ?>" <?php if( $boya_degisen[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_marspiyel_boya_id' ] ) echo 'selected'?>><?php echo $boya_degisen[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
													<td>
														<select name="sag_marspiyel_kaporta_id" class="form-control" style="width: 100%;">
														<?php foreach( $arac_expertiz_kaporta[ 2 ] AS $kaporta ) { ?>
															<option value = "<?php echo $kaporta[ 'id' ]; ?>" <?php if( $kaporta[ 'id' ] ==  $arac_expertiz_bilgileri[ 'sag_marspiyel_kaporta_id' ] ) echo 'selected'?>><?php echo $kaporta[ 'adi' ]?></option>
														<?php } ?>
														</select>
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<?php
											include "expertiz_arac_gorunumu.php";
											?>
											<!--img src="img/arac_expertiz.png" width="100%"-->
										</div>
									</div>
								</div>
							</div>
									
							<div class="row" style="page-break-before: always;">
							  <!-- left column -->
							  <div class="col-md-6">
								<!-- general form elements -->
								<div class="card card-secondary" style="height:95%">
								  <div class="card-header">
									<h3 class="card-title">Güç Testi</h3>
								  </div>
								  <!-- /.card-header -->
								  <!-- form start -->
								  
									<div class="card-body">
										<div class="row">
											<div class="form-group col-md-6">
												<label  class="control-label">Motor (HP)</label>
												<input type="text" class="form-control" name ="motor_hp" value = "<?php echo $arac_expertiz_bilgileri[ 'motor_hp' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Motor Tork</label>
												<input type="text" class="form-control" name ="motor_tork" value = "<?php echo $arac_expertiz_bilgileri[ 'motor_tork' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Teker (HP)</label>
												<input type="text" class="form-control" name ="teker_hp" value = "<?php echo $arac_expertiz_bilgileri[ 'teker_hp' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Teker Tork</label>
												<input type="text" class="form-control" name ="teker_tork" value = "<?php echo $arac_expertiz_bilgileri[ 'teker_tork' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Kayıp Güç</label>
												<input type="text" class="form-control" name ="kayip_guc_hp" value = "<?php echo $arac_expertiz_bilgileri[ 'kayip_guc_hp' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Motor Performans (%)</label>
												<input type="text" class="form-control" name ="motor_performans" value = "<?php echo $arac_expertiz_bilgileri[ 'motor_performans' ]; ?>"  placeholder="">
											</div>
										</div>
									</div>
									<!-- /.card-body -->
								  
								</div>
								<!-- /.card -->

							  </div>
							  <div class="col-md-6">
								<!-- general form elements -->
								<div class="card card-secondary" style="height:95%">
								  <div class="card-header">
									<h3 class="card-title">Yanal Kayma Testi</h3>
								  </div>
								  <!-- /.card-header -->
								  <!-- form start -->
								  
									<div class="card-body">
										<div class="row">
											<div class="form-group col-md-4">
												<label  class="control-label">Ön (m/km)</label>
												<input type="text" class="form-control" name ="yanal_kayma_on" value = "<?php echo $arac_expertiz_bilgileri[ 'yanal_kayma_on' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Arka (m/km)</label>
												<input type="text" class="form-control" name ="yanal_kayma_arka" value = "<?php echo $arac_expertiz_bilgileri[ 'yanal_kayma_arka' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Kayma (c)</label>
												<input type="text" class="form-control" name ="yanal_kayma" value = "<?php echo $arac_expertiz_bilgileri[ 'yanal_kayma' ]; ?>"  placeholder="">
											</div>
										</div>
									</div>
									<!-- /.card-body -->
								  
								</div>
								<!-- /.card -->

							  </div>
							  <div class="col-md-6">
								<!-- general form elements -->
								<div class="card card-secondary" style="height:95%">
								  <div class="card-header">
									<h3 class="card-title">Süspansiyon Testi</h3>
								  </div>
								  <!-- /.card-header -->
								  <!-- form start -->
								  
									<div class="card-body">
										<div class="row">
											<div class="form-group col-md-4">
												<label  class="control-label">Ön Sol (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_on_sol" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_on_sol' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Ön Sağ (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_on_sag" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_on_sag' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Ön Fark (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_on_fark" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_on_fark' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Arka Sol (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_arka_sol" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_arka_sol' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Arka Sağ (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_arka_sag" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_arka_sag' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Arka Fark (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_arka_fark" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_arka_fark' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Sol Fark (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_sol_fark" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_sol_fark' ]; ?>"  placeholder="">
											</div>
											<div class="form-group col-md-4">
												<label  class="control-label">Sağ Fark (%)</label>
												<input type="text" class="form-control" name ="suspansiyon_sag_fark" value = "<?php echo $arac_expertiz_bilgileri[ 'suspansiyon_sag_fark' ]; ?>"  placeholder="">
											</div>
										</div>
									</div>
									<!-- /.card-body -->
								  
								</div>
								<!-- /.card -->

							  </div>
							  <div class="col-md-6">
								<!-- general form elements -->
								<div class="card card-secondary" style="height:95%">
								  <div class="card-header">
									<h3 class="card-title">Fren Testi</h3>
								  </div>
								  <!-- /.card-header -->
								  <!-- form start -->
								  
									<div class="card-body">
										<div class="row">
											<div class="form-group col-md-6">
												<label  class="control-label">Ön Sol</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">kN</span>
													</div>
													<input type="text" class="form-control" name ="fren_on_sol_kn" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_on_sol_kn' ]; ?>"  placeholder="">
												</div>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">%&nbsp;</span>
													</div>
													<input type="text" class="form-control" name ="fren_on_sol_yuzde" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_on_sol_yuzde' ]; ?>"  placeholder="">
												</div>
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Ön Sağ</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">kN</span>
													</div>
													<input type="text" class="form-control" name ="fren_on_sag_kn" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_on_sag_kn' ]; ?>"  placeholder="">
												</div>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">%&nbsp;</span>
													</div>
													<input type="text" class="form-control" name ="fren_on_sag_yuzde" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_on_sag_yuzde' ]; ?>"  placeholder="">
												</div>
											</div>
											<div class="form-group col-md-12">
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"> Ön Sapma (Max %30)</span>
													</div>
													<input type="text" class="form-control" name ="fren_on_sapma" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_on_sapma' ]; ?>"  placeholder="">
												</div>
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Arka Sol</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">kN</span>
													</div>
													<input type="text" class="form-control" name ="fren_arka_sol_kn" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_arka_sol_kn' ]; ?>"  placeholder="">
												</div>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">%&nbsp;</span>
													</div>
													<input type="text" class="form-control" name ="fren_arka_sol_yuzde" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_arka_sol_yuzde' ]; ?>"  placeholder="">
												</div>
											</div>
											<div class="form-group col-md-6">
												<label  class="control-label">Arka Sağ</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">kN</span>
													</div>
													<input type="text" class="form-control" name ="fren_arka_sag_kn" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_arka_sag_kn' ]; ?>"  placeholder="">
												</div>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text">%&nbsp;</span>
													</div>
													<input type="text" class="form-control" name ="fren_arka_sag_yuzde" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_arka_sag_yuzde' ]; ?>"  placeholder="">
												</div>
											</div>
											<div class="form-group col-md-12">
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"> Arka Sapma (Max %30)</span>
													</div>
													<input type="text" class="form-control" name ="fren_arka_sapma" value = "<?php echo $arac_expertiz_bilgileri[ 'fren_arka_sapma' ]; ?>"  placeholder="">
												</div>
											</div>
										</div>
									</div>
									<!-- /.card-body -->
								  
								</div>
								<!-- /.card -->

							  </div>
							  <div class="col-md-12">
								<!-- general form elements -->
								<div class="card card-secondary" style="height:95%">
								  <div class="card-header">
									<h3 class="card-title">Bağımsız Expertiz Notları</h3>
								  </div>
								  <!-- /.card-header -->
								  <!-- form start -->
								  
									<div class="card-body">
										<div class="form-group">
											<textarea class="form-control" name ="bagimsiz_expertiz_notlari" placeholder="" ><?php echo $arac_expertiz_bilgileri[ 'bagimsiz_expertiz_notlari' ]; ?></textarea>
										</div>
										<div class="form-group">
											<label  class="control-label">Expertiz Raporu</label>
											<div class="custom-file">
											  <input type="file" class="custom-file-input" name="expertiz_raporu" id="customFile">
											  <label class="custom-file-label" for="customFile">Dosya Seçiniz</label>
											</div>	
										</div>
										<?php if( $arac_expertiz_bilgileri[ 'expertiz_raporu' ] != null or $arac_expertiz_bilgileri[ 'expertiz_raporu' ] != '' ){ ?>
										<div class="form-group">
											<label  class="control-label">
												<p class="text-green">Expertiz Raporu Yüklenmiştir. Değiştirmek için yeni dosya yükleyiniz.</p>
												<a href="<?php echo 'arac_resimler/'.$arac_bilgileri[ 'arac_no' ].'/'.$arac_expertiz_bilgileri[ 'expertiz_raporu' ] ?>" target="_blank">Kayıtlı Expertiz Raporunu Görüntülemek için Tıklayınız</a>
											</label>
										</div>
										<?php } ?>
									</div>									
									<!-- /.card-body -->
								  
								</div>
								<!-- /.card -->

							  </div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
							<input type="button" class="btn btn-sm btn-secondary" onclick="printDiv('expertiz_div')" value="Yazdır" />						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>  
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "show active"; ?>" id="arac_bilgi5" role="tabpanel" aria-labelledby="arac_bilgi5_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "5">
							<div class="form-group">
								<label class="control-label">Dış Makyaj Gözle Kontrolü</label>
								<textarea class="form-control" name ="proses5_dis_makyaj_gozle_kontrol" placeholder="" ><?php echo $arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ]; ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">İç Makyaj Gözle Kontrolü</label>
								<textarea class="form-control" name ="proses5_ic_makyaj_gozle_kontrol" placeholder="" ><?php echo $arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ]; ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Elektronik Aksam Gözle Kontrolü</label>
								<textarea class="form-control" name ="proses5_elektronik_aksam_gozle_kontrol" placeholder="" ><?php echo $arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ]; ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Mekanik Aksam Gözle Kontrolü</label>
								<textarea class="form-control" name ="proses5_mekanik_aksam_gozle_kontrol" placeholder="" ><?php echo $arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ]; ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Genel Gözle Kontrol</label>
								<textarea class="form-control" name ="proses5_genel_gozle_kontrol" placeholder="" ><?php echo $arac_bilgileri[ 'proses5_genel_gozle_kontrol' ]; ?></textarea>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "show active"; ?>" id="arac_bilgi6" role="tabpanel" aria-labelledby="arac_bilgi6_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <input type="button" class="btn btn-secondary" onclick="printDiv('lastik_div')" value="Yazdır" />
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST">
						<div class="card-body" id="lastik_div">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "6">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3">
											<img src="_modul/prosesler/lastik.png" width="100%">
										</div>
										<div class="col-md-9">
											<h4>Diş Aşınma Göstergeleri (TWI'ler) Nelerdir?</h4>	
											<p style="font-size:10pt;">	
											<br>Diş Aşınma Göstergeleri, uzunlamasına ana diş olukları içine lastiğin çevresi boyunca eşit olarak dağıtılmıştır. Bunlar diş derinliği yaklaşık 1,6 mm'ye düştüğünde diş yüzeyiyle aynı seviyeye gelmiş olurlar. 1,6 mm (2/32") en yaygın kabul görmüş minimum diş derinliği standardı olarak kabul edilmektedir.											
											<br>Bu standart, dünyada birçok ulusal taşımacılık kurumu tarafından benimsenen yasal bir düzenlemedir.						
											<br>Continental lastik değişimi için aşağıdaki diş derinliklerini önermektedir:						
											<br><b>Yaz Lastikleri : 3 mm (Minimum)</b>						
											<br><b>Kış Lastikleri  : 4 mm (Minimum)</b>				
											<br><b>Tarım Maki̇naları / Traktör / İş Maki̇naları Lastikleri  : %30 (Minimum)</b>	
											</p>
											<h4><b>Araç No : <?php echo $arac_bilgileri[ 'arac_no' ]; ?></b></h4>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Lastik Ölçümü</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="proses6_lastik_olcumu1" name="proses6_lastik_olcumu" value="1" <?php if( $arac_bilgileri[ 'proses6_lastik_olcumu' ] == "1" ) echo 'checked'?> >
									<label for="proses6_lastik_olcumu1">
										Yapıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="proses6_lastik_olcumu2" name="proses6_lastik_olcumu" value="2" <?php if( $arac_bilgileri[ 'proses6_lastik_olcumu' ] == "2" ) echo 'checked'?> >
									<label for="proses6_lastik_olcumu2">
										Yapılmadı
									</label>
								  </div>
								</div>
							</div>
							<div class="row justify-content-center">
								<div class="col-md-6 col-offset-6 centered">
									<table align="center" border="0" width="100%">
										<tr>
											<td>
												<div class="form-group">
													<label class="control-label">Ön Sol</label>
													<input type="number" step="0.1" oninput="lastik_aku_renklendir(this);"  class="form-control" name ="proses6_on_sol_deger" value = "<?php echo $arac_bilgileri[ 'proses6_on_sol_deger' ]; ?>" placeholder="" >
													<select  name="proses6_on_sol_lastik_tipi_id" class="form-control btn btn-warning" style="width: 100%;">
														<option value="">Seçiniz</option>
													<?php foreach( $arac_lastik_tipleri[ 2 ] AS $arac_lastik_tipi ) { ?>
														<option value = "<?php echo $arac_lastik_tipi[ 'id' ]; ?>" <?php if( $arac_lastik_tipi[ 'id' ] ==  $arac_bilgileri[ 'proses6_on_sol_lastik_tipi_id' ] ) echo 'selected'?>><?php echo $arac_lastik_tipi[ 'adi' ]?></option>
													<?php } ?>
													</select>
												</div>
											</td>
											<td rowspan="3">
												<img src="_modul/prosesler/lastik_olcum.png" width="100%">
											</td>
											<td>
												<div class="form-group">
													<label class="control-label">Ön Sağ</label>
													<input type="number" step="0.1"   class="form-control" name ="proses6_on_sag_deger" value = "<?php echo $arac_bilgileri[ 'proses6_on_sag_deger' ]; ?>" placeholder="" >
													<select  name="proses6_on_sag_lastik_tipi_id" class="form-control btn btn-warning" style="width: 100%;">
													<option value="">Seçiniz</option>
													<?php foreach( $arac_lastik_tipleri[ 2 ] AS $arac_lastik_tipi ) { ?>
														<option value = "<?php echo $arac_lastik_tipi[ 'id' ]; ?>" <?php if( $arac_lastik_tipi[ 'id' ] ==  $arac_bilgileri[ 'proses6_on_sag_lastik_tipi_id' ] ) echo 'selected'?>><?php echo $arac_lastik_tipi[ 'adi' ]?></option>
													<?php } ?>
													</select>
												</div>
											</td>
										</tr>
										<tr>
											<td>
											</td>
											<td>
											</td>
										</tr>
										<tr>
											<td>
												<div class="form-group">
													<label class="control-label">Arka Sol</label>
													<input type="number" step="0.1"  class="form-control" name ="proses6_arka_sol_deger" value = "<?php echo $arac_bilgileri[ 'proses6_arka_sol_deger' ]; ?>" placeholder="" >
													<select  name="proses6_arka_sol_lastik_tipi_id" class="form-control btn btn-warning" style="width: 100%;">
													<option value="">Seçiniz</option>
													<?php foreach( $arac_lastik_tipleri[ 2 ] AS $arac_lastik_tipi ) { ?>
														<option value = "<?php echo $arac_lastik_tipi[ 'id' ]; ?>" <?php if( $arac_lastik_tipi[ 'id' ] ==  $arac_bilgileri[ 'proses6_arka_sol_lastik_tipi_id' ] ) echo 'selected'?>><?php echo $arac_lastik_tipi[ 'adi' ]?></option>
													<?php } ?>
													</select>
												</div>
											</td>
											<td>
												<div class="form-group">
													<label class="control-label">Arka Sağ</label>
													<input type="number" step="0.1" class="form-control" name ="proses6_arka_sag_deger" value = "<?php echo $arac_bilgileri[ 'proses6_arka_sag_deger' ]; ?>" placeholder="" >
													<select  name="proses6_arka_sag_lastik_tipi_id" class="form-control btn btn-warning" style="width: 100%;">
													<option value="">Seçiniz</option>
													<?php foreach( $arac_lastik_tipleri[ 2 ] AS $arac_lastik_tipi ) { ?>
														<option value = "<?php echo $arac_lastik_tipi[ 'id' ]; ?>" <?php if( $arac_lastik_tipi[ 'id' ] ==  $arac_bilgileri[ 'proses6_arka_sag_lastik_tipi_id' ] ) echo 'selected'?>><?php echo $arac_lastik_tipi[ 'adi' ]?></option>
													<?php } ?>
													</select>
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="3">
												<div class="form-group">
													<label class="control-label">Stepne</label>
													<select  name="proses6_stepne_lastik_tipi_id" class="form-control btn btn-warning" style="width: 100%;">
													<option value="">Seçiniz</option>
													<?php foreach( $arac_lastik_tipleri[ 2 ] AS $arac_lastik_tipi ) { ?>
														<option value = "<?php echo $arac_lastik_tipi[ 'id' ]; ?>" <?php if( $arac_lastik_tipi[ 'id' ] ==  $arac_bilgileri[ 'proses6_stepne_lastik_tipi_id' ] ) echo 'selected'?>><?php echo $arac_lastik_tipi[ 'adi' ]?></option>
													<?php } ?>
													</select>
												</div>									
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "show active"; ?>" id="arac_bilgi7" role="tabpanel" aria-labelledby="arac_bilgi7_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <input type="button" class="btn btn-secondary" onclick="printDiv('aku_div')" value="Yazdır" />
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST" class="">
						<div class="card-body" id="aku_div">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "7">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3">
											<img src="_modul/prosesler/aku.png" width="100%">
										</div>
										<div class="col-md-9">
											<h4>Akü Ölçüm Prosesi</h4>	
											<p style="font-size:10pt;">	
												"Otomotiv aküsü motorlu araca elektrik akımı sağlayan yeniden doldurulabilir bir aküdür. Esas amacı motoru çalıştıran marş motorunu beslemektir. Motor çalışır çalışmaz, arabanın elektrik sistemleri için gerekli güç talepler artarken ya da azalırken hala şarj eden alternatör ile birlikte akü tarafından sağlanır.

												<br><br>Tipik olarak, marş yapma akü kapasitesinin % 3'ünden daha azını kullanır. Bu nedenle, oto aküleri  kısa bir zaman süresinde maksimum akım verecek şekilde tasarlanırlar . Bu nedenle bazen ""SLI aküler"" olarak anılırlar Starting=marş yapma, Lighting=Aydınlatma ve Ignition=Ateşleme anlamındadır. SLI aküler derin deşarj için tasarlanmamıştır ve tam deşarj akünün ömrünü azaltabilir.

												Motoru marş yaptırmanın yanı sıra SLI akünün görevi aracın elektriksel şartları şarj sisteminden gelen arzı aştığında gerekli ek gücü sağlamaktır. Ayrıca muhtemel gerilim kıvılcımlarını yok eden bir dengeleyicidir.

												Motor çalışırken gücün çoğunluğu, 13.5 ile 14.5 V arasında çıkışı sürdüren voltaj regülatörüne sahip bir alternatör tarafından sağlanır.

												Akünün görevi marş motorunu, ateşleme sistemini, doğru akımla çalışan tüm devreleri, ışık ve alıcıları beslemektir. Akünün içinde Sülfürik asit saf su karışımı olan elektrolit konulur. Karışımda %39 asit, %61 su vardır. Elemanlar arası seri köprülerle bağlanmıştır."											
											</p>	
											<h4><b>Araç No : <?php echo $arac_bilgileri[ 'arac_no' ]; ?></b></h4>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group clearfix card" id="proses7_alternator_durumu_">
								<div class="card-body">
								  <label>Alternatör Durumu</label> (Kontakt Açık Ölçüm)<br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);" id="proses7_alternator_durumu1" name="proses7_alternator_durumu" value="1" <?php if( $arac_bilgileri[ 'proses7_alternator_durumu' ] == "1" ) echo 'checked';?> >
									<label for="proses7_alternator_durumu1">
										İyi (Akü İyi Durumda)
									</label>
								  </div>
								  <br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_alternator_durumu2" name="proses7_alternator_durumu" value="2" <?php if( $arac_bilgileri[ 'proses7_alternator_durumu' ] == "2" ) echo 'checked'?> >
									<label for="proses7_alternator_durumu2">
										Kötü (Akü Arızalı yada Yetersiz)
									</label>
								  </div>
								  <br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_alternator_durumu0" name="proses7_alternator_durumu" value="0" <?php if( $arac_bilgileri[ 'proses7_alternator_durumu' ] == "0" ) echo 'checked'?> >
									<label for="proses7_alternator_durumu0">
										Ölçülmedi
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card" id="proses7_aku_durumu_">
								<div class="card-body">
								  <label>Akü Durumu</label> (Kontakt Kapalı Ölçüm)<br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_aku_durumu1" name="proses7_aku_durumu" value="1" <?php if( $arac_bilgileri[ 'proses7_aku_durumu' ] == "1" ) echo 'checked'?> >
									<label for="proses7_aku_durumu1">
										İyi (Yüksek Seviyede)
									</label>
								  </div>
								  <br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_aku_durumu3" name="proses7_aku_durumu" value="3" <?php if( $arac_bilgileri[ 'proses7_aku_durumu' ] == "3" ) echo 'checked'?> >
									<label for="proses7_aku_durumu3">
										Orta (Orta Seviyede)
									</label>
								  </div>
								  <br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_aku_durumu2" name="proses7_aku_durumu" value="2" <?php if( $arac_bilgileri[ 'proses7_aku_durumu' ] == "2" ) echo 'checked'?> >
									<label for="proses7_aku_durumu2">
										Kötü (Kötü Seviyede)
									</label>
								  </div>
								  <br>
								  <div class="icheck-default d-inline">
									<input required type="radio" onclick="aku_renklendir(this);"  id="proses7_aku_durumu0" name="proses7_aku_durumu" value="0" <?php if( $arac_bilgileri[ 'proses7_aku_durumu' ] == "0" ) echo 'checked'?> >
									<label for="proses7_aku_durumu0">
										Ölçülmedi
									</label>
								  </div>
								</div>
							</div>
						</div>

						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "show active"; ?>" id="arac_bilgi8" role="tabpanel" aria-labelledby="arac_bilgi8_tab">
					<div class="card card-default">
					  <div class="card-header">
						<h3 class="card-title">Araç Fotoğraf Ekle <small><em>Sadece JPG ve PNG formtında dosya yükleyebilirsiniz.</em> </small></h3>
					  </div>
					  <div class="card-body">
						<div id="actions" class="row">
						  <div class="col-lg-6">
							<div class="btn-group w-100">
							  <span class="btn btn-success col fileinput-button">
								<i class="fas fa-plus"></i>
								<span>Dosya Ekle</span>
							  </span>
							  <button type="submit" class="btn btn-primary col start">
								<i class="fas fa-upload"></i>
								<span>Yüklemeye Başla</span>
							  </button>
							  <button type="reset" class="btn btn-warning col cancel">
								<i class="fas fa-times-circle"></i>
								<span>İptal Et</span>
							  </button>
							</div>
						  </div>
						  <div class="col-lg-6 d-flex align-items-center">
							<div class="fileupload-process w-100">
							  <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
								<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
							  </div>
							</div>
						  </div>
						</div>
						<div class="table table-striped files" id="previews">
						  <div id="template" class="row mt-2">
							<div class="col-auto">
								<span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
							</div>
							<div class="col d-flex align-items-center">
								<p class="mb-0">
								  <span class="lead" data-dz-name></span>
								  (<span data-dz-size></span>)
								</p>
								<strong class="error text-danger" data-dz-errormessage></strong>
							</div>
							<div class="col-4 d-flex align-items-center">
								<div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
								  <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
								</div>
							</div>
							<div class="col-auto d-flex align-items-center">
							  <div class="btn-group">
								<button class="btn btn-primary start">
								  <i class="fas fa-upload"></i>
								  <span>Başla</span>
								</button>
								<button data-dz-remove class="btn btn-warning cancel">
								  <i class="fas fa-times-circle"></i>
								  <span>İptal Et</span>
								</button>
								<button data-dz-remove class="btn btn-danger delete">
								  <i class="fas fa-trash"></i>
								  <span>Sil</span>
								</button>
							  </div>
							</div>
						  </div>
						</div>
					  </div>
					  <!-- /.card-body -->
					  <div class="card-footer">
						Dosya eklerken birden fazla dosya seçebilirsiniz. Sürükle - Bırak yöntemiylede dosya ekleyebilirsiniz.
					  </div>
					</div>
					<div class="card card-default">
					  <div class="card-header">
						<h4 class="card-title">Kayıtlı Fotoğraflar</h4>							
						<a type="button" class="btn btn-sm bg-success float-right" href = "_modul/prosesler/zip.php?islem=medya_indir&id=<?php echo $arac_bilgileri['id'];?>&arac_no=<?php echo $arac_bilgileri['arac_no'];?>&tab_no=8" ><span class="fa fa-delete"></span>Tümünü İndir</a>					
					  </div>
					  <div class="card-body">
						<div class="row">
						<?php foreach( $arac_medya_bilgileri AS $arac_medya ) { ?>
						  <div class="col-sm-2">
						  <div class="card card-default bg-default" style="height:90%" >
						  <div class="card-body">
							<a href="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_medya['dosya_adi'];?>" data-toggle="lightbox" data-title="<?php echo $arac_bilgileri['arac_no'].' ('.$arac_bilgileri['plaka'].')';?>" data-gallery="gallery">
							  <img src="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_medya['dosya_adi'];?>" class="img-fluid mb-2" alt="white sample"/>
							</a>
						  </div>
						  <div class="card-footer">
							<a modul= 'prosesler' yetki_islem="medya_sil" type="button" class="btn btn-sm pull-right bg-danger float-right" href = "_modul/prosesler/proseslerSEG.php?islem=medya_sil&id=<?php echo $arac_bilgileri['id'];?>&medya_id=<?php echo $arac_medya[ 'id' ]; ?>&arac_no=<?php echo $arac_bilgileri['arac_no'];?>&dosya_adi=<?php echo $arac_medya[ 'dosya_adi' ]; ?>&tab_no=8" ><span class="fas fa-trash-alt"></span></a>
							<?php if( $arac_medya['kapak_foto'] == 0 ){?>
							<a type="button" class="btn btn-sm pull-right bg-primary float-right" href = "_modul/prosesler/proseslerSEG.php?islem=medya_kapak&id=<?php echo $arac_bilgileri['id'];?>&medya_id=<?php echo $arac_medya[ 'id' ]; ?>&arac_no=<?php echo $arac_bilgileri['arac_no'];?>&tab_no=8" ><span class="fa fa-delete"></span>Kapak yap</a>
							<?php } ?>
						  </div>
						  </div>
						  </div>
						<?}?>
						</div>
					  </div>
					</div>
				  </div>
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "show active"; ?>" id="arac_bilgi9" role="tabpanel" aria-labelledby="arac_bilgi9_tab">
					<div class="row">
					  <!-- left column -->
					  <div class="col-md-3">
						<!-- general form elements -->
						<div class="card card-primary">
						  <div class="card-header">
							<h3 class="card-title">Araç Yayın Ekle / Güncelle</h3>
						  </div>
						  <!-- /.card-header -->
						  <!-- form start -->
						  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST">
							<div class="card-body">
								<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
								<input type = "hidden" name = "yayin_id" value = "<?php echo $arac_yayin_bilgileri[ 'id' ]; ?>">
								<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
								<input type = "hidden" name = "tab_no" value = "9">
								<div class="form-group">
									<label  class="control-label">Yayın Yeri</label>
									<select name="yayin_yeri_id" class="form-control" style="width: 100%;">
									<?php foreach( $arac_yayin_yerleri[ 2 ] AS $arac_yayin_yeri ) { ?>
										<option value = "<?php echo $arac_yayin_yeri[ 'id' ]; ?>" <?php if( $arac_yayin_yeri[ 'id' ] ==  $arac_yayin_bilgileri[ 'yayin_yeri_id' ] ) echo 'selected'?>><?php echo $arac_yayin_yeri[ 'adi' ]?></option>
									<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label  class="control-label">Yayın Linki</label>
									<input type="text" class="form-control" name ="yayin_linki" value = "<?php echo $arac_yayin_bilgileri[ 'yayin_linki' ]; ?>" required placeholder="">
								</div>
							</div>
							<!-- /.card-body -->
							<div class="card-footer">
								<button modul= 'prosesler' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
								<a type="reset" class="btn btn-primary btn-sm pull-right" href = "?modul=prosesler&islem=ekle&id=<?php echo $arac_bilgileri[ 'id' ]; ?>&tab_no=9" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</a>
							</div>
						  </form>
						</div>
						<!-- /.card -->

					  </div>
					  <!--/.col (left) -->

					<div class="col-md-9">
						<div class="card card-primary">
						  <div class="card-header">
							<h3 class="card-title">Araç Yayınları</h3>
							<!--div class="card-tools">
							  <div class="input-group input-group-sm" style="width: 150px;">
								<input type="text" name="table_search" class="form-control float-right" placeholder="Ara">
								<div class="input-group-append">
								  <button type="submit" class="btn btn-default">
									<i class="fas fa-search"></i>
								  </button>
								</div>
							  </div>
							</div-->
						  </div>
						  <!-- /.card-header -->
						  <div class="card-body">
							<table id="example2" class="table table-sm table-bordered  table-hover">
							  <thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Araç No</th>
									<th>Logo</th>
									<th>Yayın Yeri</th>
									<th>Yayın Tarihi</th>
									<th style="width: 20px">Yayın</th>
									<th style="width: 20px">İlan</th>
									<th style="width: 20px">Düzenle</th>
									<th style="width: 120px">Yayından Kaldır</th>
								</tr>
							  </thead>
							  <tbody>
								<?php $sayi = 1; foreach( $arac_yayinlari[ 2 ] AS $arac_yayin ) { ?>
								<tr class="<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ echo 'table-danger'; } ?>">
									<td><?php echo $sayi++; ?></td>
									<td style ="font-weight:bold;"><?php echo $arac_bilgileri[ 'arac_no' ]; ?></td>
									<td><img src="img/<?php echo $arac_yayin[ 'logo' ]; ?>" width="60" ></td>
									<td><?php echo $arac_yayin[ 'yayin_yeri_adi' ]; ?></td>
									<td><span style="display:none;"><?php echo $arac_yayin[ 'yayinlanma_tarihi' ]; ?></span><?php echo date('d.m.Y H:i',strtotime($arac_yayin['yayinlanma_tarihi'])); ?></td>
									<td>
										<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
											<span class="right badge badge-danger">Yayında Değil</span>
										<?php }else{ ?>
											<span class="right badge badge-success">Yayında</span>
										<? } ?>
									</td>
									<td align = "center">
										<a modul= 'prosesler' yetki_islem="ilan" class = "btn btn-sm btn-primary btn-xs" href = "<?php echo $arac_yayin[ 'yayin_linki' ]; ?>" target="_blank">
											İlan
										</a>
									</td>
									<td align = "center">
										<a modul= 'prosesler' yetki_islem="yayin_duzenle" class = "btn btn-sm btn-success btn-xs" href = "?modul=prosesler&islem=yayin_guncelle&yayin_id=<?php echo $arac_yayin[ 'id' ]; ?>&id=<?php echo $arac_bilgileri[ 'id' ]; ?>&tab_no=9" >
											Düzenle
										</a>
									</td>
									<td align = "center" valign="middle">
										<?php if( $arac_yayin[ 'yayindan_alindi' ] == 1 ){ ?>
										<a modul= 'prosesler' yetki_islem="yayindan_kaldir" class = "btn btn-sm btn-success btn-xs" href = "_modul/prosesler/proseslerSEG.php?islem=yayindan_kaldir&yayin_id=<?php echo $arac_yayin[ 'id' ]; ?>&tab_no=9&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" >
											Yayına Al
										</a>
										<?php }else{ ?>
										<a modul= 'prosesler' yetki_islem="yayindan_kaldir" class = "btn btn-sm btn-danger btn-xs" href = "_modul/prosesler/proseslerSEG.php?islem=yayindan_kaldir&yayin_id=<?php echo $arac_yayin[ 'id' ]; ?>&tab_no=9&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" >
											Yayından Kaldır
										</a>
										<? } ?>
									</td>
								</tr>
								<?php } ?>
							  </tbody>
							</table>
						  </div>
						  <!-- /.card-body -->
						  <div class="card-footer clearfix">
							<!--ul class="pagination pagination-sm m-0 float-right">
							  <li class="page-item"><a class="page-link" href="#">«</a></li>
							  <li class="page-item"><a class="page-link" href="#">1</a></li>
							  <li class="page-item"><a class="page-link" href="#">2</a></li>
							  <li class="page-item"><a class="page-link" href="#">3</a></li>
							  <li class="page-item"><a class="page-link" href="#">»</a></li>
							</ul-->
						  </div>
						</div>
						<!-- /.card -->

					  </div>
					  <!-- right column -->

					</div>
					<!-- /.row -->
                  </div>
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 10 ) echo "show active"; ?>" id="arac_bilgi10" role="tabpanel" aria-labelledby="arac_bilgi10_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/prosesler/proseslerSEG.php" method = "POST">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "10">
							<?php if( $arac_bilgileri[ 'onaya_gonderildi' ] == 1 and $arac_bilgileri[ 'onaylandi' ] == 0 ){ ?>
							<h3><span class="badge badge-warning">Onay Bekleniyor</span></h3>
							<button modul= 'prosesler' yetki_islem="onayla" type="button" class="btn btn-sm  bg-indigo color-palette" data-href="_modul/prosesler/proseslerSEG.php?islem=onayla&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" data-toggle="modal" data-target="#prosesler_onayla_onay" >Onayla</button>
							<?php } ?>
							<?php if( $arac_bilgileri[ 'onaya_gonderildi' ] == 1 and $arac_bilgileri[ 'onaylandi' ] == 1 ){ ?>
							<h3><span class="badge badge-success">Onaylandı</span></h3>
							<button modul= 'prosesler' yetki_islem="onay_kaldir" type="button" class="btn btn-sm  btn-success" data-href="_modul/prosesler/proseslerSEG.php?islem=onayla&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" data-toggle="modal" data-target="#prosesler_onayla_onay" >Onayı Kaldır</button>							
							<?php } ?>
							<?php if( $arac_bilgileri[ 'onaya_gonderildi' ] == 0 and $arac_bilgileri[ 'onaylandi' ] == 0 ){ ?>
							<button modul= 'prosesler' yetki_islem="onaya_gonder" type="submit" class="btn btn-primary pull-right"> Onaya Gönder</button>
							<?php } ?>
						</div>
						<!-- /.card-body -->
					  </form>
					</div>
					<!-- /.card -->
                  </div>
                  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 11 ) echo "show active"; ?>" id="arac_bilgi11" role="tabpanel" aria-labelledby="arac_bilgi11_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "11">
							<button  type="button" class="btn btn-primary pull-right"  onclick="CopyToClipboard('ilan_metni')">İlan Metnini Kopyala</button>
							<?php 
								include "ilan_metni.php";
							?>
							<br>
							<button  type="button" class="btn btn-primary pull-right"  onclick="CopyToClipboard('ilan_metni')">İlan Metnini Kopyala</button>
						</div>
						<!-- /.card-body -->
					</div>
					<!-- /.card -->
                  </div>
                </div>
              </div>
              <!-- /.card -->
			<?php
				}
			?>
            </div>
          </div>		
		</div>
<script type="text/javascript">
	var simdi = new Date(); 
	//var simdi="11/25/2015 15:58";
	$(function () {
		$('#datetimepicker1').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy HH:mm',
			icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
			}
		});
	});
	
</script>
<script>
$(function () {
  bsCustomFileInput.init();
});
</script>
<script> 
	function aku_renklendir(a){
		if( a.value == "0" && a.checked == true )
			document.getElementById(a.name+"_").className = "form-group clearfix card bg-default";
		if( a.value == "1" && a.checked == true )
			document.getElementById(a.name+"_").className = "form-group clearfix card bg-success";
		if( a.value == "2" && a.checked == true  )
			document.getElementById(a.name+"_").className = "form-group clearfix card bg-danger";
		if( a.value == "3" && a.checked == true  )
			document.getElementById(a.name+"_").className = "form-group clearfix card bg-warning";
	}
	
	aku_renklendir(document.getElementById('proses7_alternator_durumu1'));
	aku_renklendir(document.getElementById('proses7_alternator_durumu2'));
	aku_renklendir(document.getElementById('proses7_aku_durumu1'));
	aku_renklendir(document.getElementById('proses7_aku_durumu2'));
	aku_renklendir(document.getElementById('proses7_aku_durumu3'));
</script>
<script> 
	function expertiz_arac_renklendir(a){
		//alert(a.value);
		if( a.value == "1" )
			document.getElementById(a.name).style = "fill: rgb(153, 255, 129)";
		if( a.value == "2" )
			document.getElementById(a.name).style = "fill: url(#Gradient_local)";
		if( a.value == "3" )
			document.getElementById(a.name).style = "fill: rgb(255, 219, 77)";
		if( a.value == "4" )
			document.getElementById(a.name).style = "fill: rgb(228, 0, 48)";
		if( a.value == "5" )
			document.getElementById(a.name).style = "fill: rgb(233, 233, 233)";
	}
	expertiz_arac_renklendir(document.getElementsByName('sol_on_camurluk_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sol_on_kapi_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sol_arka_camurluk_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sol_arka_kapi_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sag_on_camurluk_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sag_on_kapi_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sag_arka_camurluk_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('sag_arka_kapi_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('tavan_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('on_tampon_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('on_kaput_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('arka_tampon_boya_id')[0]);
	expertiz_arac_renklendir(document.getElementsByName('arka_bagaj_kapisi_boya_id')[0]);
</script>
<script>		
  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false;

  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template");
  previewNode.id = "";
  var previewTemplate = previewNode.parentNode.innerHTML;
  previewNode.parentNode.removeChild(previewNode);

  var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
    url: "_modul/prosesler/proseslerSEG.php?id=<?php echo $arac_bilgileri[ 'id' ]; ?>&arac_no=<?php echo $arac_bilgileri[ 'arac_no' ]; ?>&islem=guncelle&tab_no=8", // Set the url
	acceptedFiles: "image/jpeg,image/png",
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
	timeout: 180000,
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  });

  myDropzone.on("addedfile", function(file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
  });

  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function(progress) {
    document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
	document.querySelector("#total-progress div").innerHTML = "%" + Math.floor(progress);
  });

  myDropzone.on("sending", function(file) {
    // Show the total progress bar when upload starts
    document.querySelector("#total-progress").style.opacity = "1";
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
  });

  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function(progress) {
    document.querySelector("#total-progress").style.opacity = "1";
    document.querySelector("#total-progress div").innerHTML = "%100";
  });

  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function() {
    myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
  };
  document.querySelector("#actions .cancel").onclick = function() {
    myDropzone.removeAllFiles(true);
  };
  // DropzoneJS Demo Code End
</script>
<script>
  $(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });

    $('.filter-container').filterizr({gutterPixels: 3});
    $('.btn[data-filter]').on('click', function() {
      $('.btn[data-filter]').removeClass('active');
      $(this).addClass('active');
    });
  })
</script>
<script>
function printDiv(divName) {
	if(divName == 'expertiz_div')
	 var baslik = "<div class='card'><div class='card-body'><div class='row'><div class='col-md-4'><img src='img/wowlogo.jfif' width='100%'></div><div class='col-md-8'><table border='0' height='100%' width='100%'><tr><td valign='middle' align='center'><h1 >Ekspertiz Raporu</h1></td></tr></table></div></div></div></div>";
	if(divName == 'lastik_div')
	 var baslik = "<div class='card'><div class='card-body'><div class='row'><div class='col-md-4'><img src='img/wowlogo.jfif' width='100%'></div><div class='col-md-8'><table border='0' height='100%' width='100%'><tr><td valign='middle' align='center'><h1 >Lastik Ölçüm Raporu</h1></td></tr></table></div></div></div></div>";
	if(divName == 'aku_div')
	 var baslik = "<div class='card'><div class='card-body'><div class='row'><div class='col-md-4'><img src='img/wowlogo.jfif' width='100%'></div><div class='col-md-8'><table border='0' height='100%' width='100%'><tr><td valign='middle' align='center'><h1 >Akü Ölçüm Raporu</h1></td></tr></table></div></div></div></div>";
     var printContents = baslik + document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}</script>
<script>
function CopyToClipboard(containerid) {
  if (document.selection) {
    var range = document.body.createTextRange();
    range.moveToElementText(document.getElementById(containerid));
    range.select().createTextRange();
    document.execCommand("copy");
  } else if (window.getSelection) {
    var range = document.createRange();
    range.selectNode(document.getElementById(containerid));
    window.getSelection().addRange(range);
    document.execCommand("copy");
	window.getSelection().removeAllRanges();
	mesajVer('İlan Metni Kopyalandı', 'yesil');
    //alert("Text has been copied, now paste in the text-area")
  }
}
</script>
