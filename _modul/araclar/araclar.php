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
	 id
	,arac_no
	,plaka
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
	a.*
	,at.adi as arac_tipi_adi
	,ar.adi as arac_renk_adi
	,am.adi as arac_marka_adi
	,akt.adi as arac_kasa_tipi_adi
	,act.adi as arac_cekis_tipi_adi
	,avt.adi as arac_vites_tipi_adi
	,ayt.adi as arac_yakit_tipi_adi
	,avs.adi as arac_vites_sayisi_adi
	,CONCAT(sk.adi," ",sk.soyadi) as personel
FROM
	tb_araclar as a
LEFT JOIN tb_arac_tipleri as at on a.arac_tipi_id = at.id
LEFT JOIN tb_arac_renkleri as ar on a.renk_id = ar.id
LEFT JOIN tb_arac_markalari as am on a.arac_marka_id = am.id
LEFT JOIN tb_arac_kasa_tipleri as akt on a.arac_kasa_tipi_id = akt.id
LEFT JOIN tb_arac_cekis_tipleri as act on a.arac_cekis_tipi_id = act.id
Left JOIN tb_arac_vites_tipleri as avt on a. arac_vites_tipi_id = avt.id
LEft JOIN tb_arac_yakit_tipleri as ayt on a.arac_yakit_tipi_id = ayt.id
LEFT JOIN tb_arac_vites_sayilari as avs on a.arac_vites_sayisi_id = avs.id
LEFT JOIN tb_sistem_kullanici as sk on a.personel_id = sk.id
WHERE
	a.id = ?
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE a.sube_id in ($yetkili_subeler)
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

$SQL_arac_fiyatlar = <<< SQL
SELECT
	*
FROM
	tb_arac_fiyatlar
WHERE
	arac_id = ?
ORDER BY id
SQL;

$SQL_subeler = <<< SQL
SELECT
	*
FROM
	tb_subeler
WHERE 
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE id in ($yetkili_subeler)
	END
SQL;


$SQL_arac_detaylari_eksik_alan_sayisi_guncelle = <<< SQL
UPDATE
	tb_araclar
SET
	arac_detaylari_eksik_alan_sayisi = ?
WHERE
	id = ?
SQL;

$arac_id				= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$arac					= $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id, $_SESSION[ 'super' ]  ) );
$arac_id				= $arac[ 2 ]['id'];
$araclar				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );
$arac_fiyatlar			= $vt->select( $SQL_arac_fiyatlar, array( $arac_id  ) );
$arac_tipleri			= $vt->select( $SQL_arac_tipleri, array() );
$arac_kasa_tipleri		= $vt->select( $SQL_arac_kasa_tipleri, array() );
$arac_vites_tipleri		= $vt->select( $SQL_arac_vites_tipleri, array() );
$arac_vites_sayilari	= $vt->select( $SQL_arac_vites_sayilari, array() );
$arac_yakit_tipleri		= $vt->select( $SQL_arac_yakit_tipleri, array() );
$arac_cekis_tipleri		= $vt->select( $SQL_arac_cekis_tipleri, array() );
$arac_renkleri			= $vt->select( $SQL_arac_renkleri, array() );
$arac_markalari			= $vt->select( $SQL_arac_markalari, array() );
$subeler				= $vt->select( $SQL_subeler, array( $_SESSION[ 'super' ]  ) );
$arac_bilgileri			= array();
$islem					= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

if( $islem == 'detaylar' ){
	//if( $arac[ 2 ]['id'] == '' ){
	//	echo "<h1>Yetkiniz Bulunmamaktdır</h1>";
	//	exit;
	//}
	$arac_bilgileri = $arac[ 2 ];
}

include "kontrol.php";
$arac_detaylari_eksik_alan_sayisi = arac_detaylari_genel_kontrol($arac_bilgileri);
$sorgu_sonuc = $vt->update( $SQL_arac_detaylari_eksik_alan_sayisi_guncelle, array(
	 $arac_detaylari_eksik_alan_sayisi
	,$arac_id
) );

?>
  <script src="https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js"></script>
  <script src="https://unpkg.com/chartjs-gauge@0.2.0/dist/chartjs-gauge.js"></script>
  <style>
canvas {
  -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
}
  
  </style>

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
			<input type = "hidden" name = "modul" value = "araclar">
			<input type = "hidden" name = "islem" value = "detaylar">
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
		  <button  modul= 'araclar' yetki_islem="arac_sec" type="submit" class="btn btn-outline-light">Araç Seç</button>
		</div>
	</form>
  </div>
  <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>

<div id="ehb_modal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content ">
      <div class="modal-header bg-secondary">
        <h5 id="modalTitle" class="modal-title">Yeni Fiyat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
					
					  <!--form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class=""-->
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "yeni_fiyat_ekle">
							<input type = "hidden" name = "tab_no" value = "6">
							<div class="form-group">
								<label class="control-label">Piyasa Değeri</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input type="number"  class="form-control " name ="piyasa_degeri" id ="piyasa_degeri" value = "<?php echo $arac_bilgileri[ 'piyasa_degeri' ]; ?>" placeholder="Örn : 122000" required>
								</div>
								<a href="https://www.arabam.com/arabam-kac-para" class="" target="_blank">Piyasa Değerini Öğrenmek İçin Tıklayın</a>
							</div>
							<div class="form-group">
								<label class="control-label">Kasko Değeri</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input type="number" class="form-control " name ="kasko_degeri" value = "<?php echo $arac_bilgileri[ 'kasko_degeri' ]; ?>" placeholder="Örn : 122000" required>
								</div>
								<a href="https://www.hangikredi.com/sigorta/arac-kasko-deger-listesi" class="" target="_blank">Kasko Değerini Öğrenmek İçin Tıklayın</a>
							</div>
							<div class="form-group">
								<label class="control-label">Talep Edilen Fiyat</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input type="number" class="form-control " name ="talep_fiyat" id="talep_fiyat" value = "<?php echo $arac_bilgileri[ 'talep_fiyat' ]; ?>" placeholder="Örn : 122000" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Ekstra İstenen Hizmet Bedeli</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input type="number" class="form-control " name ="ekstra_istenen_hizmet_bedeli" id="ekstra_istenen_hizmet_bedeli" value = "<?php echo $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli' ]+0; ?>" placeholder="Örn : 1500" required>
								</div>
							</div>
							<!--div class="form-group">
								<label class="control-label">Hizmet Bedeli</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="hizmet_bedeli" value = "<?php echo $arac_bilgileri[ 'hizmet_bedeli' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Pazarlık Payı</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="pazarlik_payi" value = "<?php echo $arac_bilgileri[ 'pazarlik_payi' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">İlan Fiyatı</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="ilan_fiyati" value = "<?php echo $arac_bilgileri[ 'ilan_fiyati' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div-->
						</div>
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					
    </div>
  </div>
</div>

<div class="modal fade" id="araclar_ekstra_hizmet_bedeli_onayla_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content bg-yellow">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu işlem için emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
				<a class="btn btn-secondary btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#araclar_ekstra_hizmet_bedeli_onayla_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<!-- /.modal -->
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="araclar_sil_onay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
	$( '#araclar_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<script>
function form_disabled( form_id ){
	var form = document.getElementById(form_id);
	var elements = form.elements;
	for (var i = 0, len = elements.length; i < len; ++i) {
		elements[i].readOnly = true;
	}
}
//form_disabled( "vekil_formu" );
</script>

<style>
	.ruhsat_baslik{
		color : #1267a0;
		font: 8pt "Tohama";
		padding : 0px;
		margin : 0px;
		font-weight : none;
	}
	.ruhsat_deger{
		text-align : center;
		font: 10pt "Calibri";
		font-weight:bold;
		padding : 0px;
		margin : 0px;
	}
	#ruhsat td{
		vertical-align: text-top;
		padding : 0px;
		margin : 0px;
	}
	.ruhsat_bg{
		background-color : #D9D9D9;
	}
</style>
        <div class="row">
          <div class="col-12">
			<h3><i class="fas fa-car-crash"></i> Araç Detayları
			<div class="float-md-right">
				<button disabled class = "btn btn-sm btn-outline-secondary" style="width:200px;" href = "?modul=araclar&islem=detaylar&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Araç Detayları Göster
				</button>
				<a class = "btn btn-sm btn-secondary" style="width:200px;" href = "?modul=prosesler&islem=prosesler&tab_no=1&id=<?php echo $arac_bilgileri[ 'id' ]; ?>">
					Prosesleri Göster
				</a>
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
						Temel
						<?php if( araclar_temel_bilgiler($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>					
				  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "active"; ?> " id="arac_bilgi2_tab" data-toggle="pill" href="#arac_bilgi2" role="tab" aria-controls="arac_bilgi2" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "true"; else echo "false"; ?>">
						Araç Sahibi
						<?php if( araclar_arac_sahibi($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
				  <?php if( $arac_bilgileri[ 'vekil' ] == 1 ){ ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "active"; ?> " id="arac_bilgi3_tab" data-toggle="pill" href="#arac_bilgi3" role="tab" aria-controls="arac_bilgi3" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "true"; else echo "false"; ?>">
						Vekil
						<?php if( araclar_vekalet_bilgileri($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
				  <?php } ?>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "active"; ?> " id="arac_bilgi4_tab" data-toggle="pill" href="#arac_bilgi4" role="tab" aria-controls="arac_bilgi4" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "true"; else echo "false"; ?>">
						Araç Bilgileri
						<?php if( araclar_arac_bilgileri($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "active"; ?> " id="arac_bilgi5_tab" data-toggle="pill" href="#arac_bilgi5" role="tab" aria-controls="arac_bilgi5" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "true"; else echo "false"; ?>">
						Ruhsat
						<?php if( araclar_ruhsat_bilgileri($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "active"; ?> " id="arac_bilgi6_tab" data-toggle="pill" href="#arac_bilgi6" role="tab" aria-controls="arac_bilgi6" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "true"; else echo "false"; ?>">
						Fiyatlama
						<?php if( araclar_fiyatlama($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "active"; ?> " id="arac_bilgi7_tab" data-toggle="pill" href="#arac_bilgi7" role="tab" aria-controls="arac_bilgi7" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "true"; else echo "false"; ?>">
						Kayıt Kontrol
						<?php if( araclar_kayit_kontrol($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "active"; ?> " id="arac_bilgi8_tab" data-toggle="pill" href="#arac_bilgi8" role="tab" aria-controls="arac_bilgi8" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "true"; else echo "false"; ?>">
						Evraklar
						<?php if( araclar_fotokopiler($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "active"; ?> " id="arac_bilgi9_tab" data-toggle="pill" href="#arac_bilgi9" role="tab" aria-controls="arac_bilgi9" aria-selected="<?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "true"; else echo "false"; ?>">
						Çıktılar
						<?php if( araclar_sozlesme($arac_bilgileri) > 0 ){ ?>
							<i class="fas fa-exclamation-triangle text-yellow"></i>
						<?php }else{ ?>
							<i class="fas fa-check-circle text-green"></i>
						<?php } ?>
					</a>
                  </li>
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
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Temel Bilgiler</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "1">
							<div class="form-group">
								<label  class="control-label">Şube</label>
									<select  class="form-control select2" name = "sube_id" required>
											<option value="">Seçiniz</option>
										<?php foreach( $subeler[ 2 ] AS $sube ) { ?>
											<option value = "<?php echo $sube[ 'id' ]; ?>" <?php if( $sube[ 'id' ] ==  $arac_bilgileri[ 'sube_id' ] ) echo 'selected'?>><?php echo $sube[ 'adi' ]?></option>
										<?php } ?>
									</select>
							</div>
							<div class="form-group">
								<label class="control-label">Araç No</label>
								<input disabled type="text" class="form-control " name ="" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>" placeholder="" >
							</div>
							<div class="form-group">
							  <label class="control-label">Kayıt Tarihi:</label>
								<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
									<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
										<div class="input-group-text"><i class="fa fa-calendar"></i></div>
									</div>
									<input disabled type="text" name="kayit_tarihi" value="<?php if( $arac_bilgileri['kayit_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_bilgileri['kayit_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control  datetimepicker-input" data-target="#datetimepicker1"/>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
                  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 2 ) echo "show active"; ?>" id="arac_bilgi2" role="tabpanel" aria-labelledby="arac_bilgi2_tab">
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Araç Sahibi Bilgileri</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu2" action = "_modul/araclar/araclarSEG.php" method = "POST" class="" >
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "2">
							<div class="form-group">
								<label class="control-label">T.C.Kimilk No</label>
								<input type="text" pattern="^[1-9]{1}[0-9]{9}[02468]{1}$" class="form-control " minlength="11" maxlength="11" name ="sahip_tc_no" value = "<?php echo $arac_bilgileri[ 'sahip_tc_no' ]; ?>" placeholder="T.C.Kimilk No"  required>
								<div class="invalid-feedback">Lütfen  uygun formatta giriş yapınız.</div>
							</div>
							<div class="valid-feedback">Lütfen  uygun formatta giriş yapınız.</div>
							<div class="form-group">
								<label class="control-label">Adı</label>
								<input type="text" class="form-control " name ="sahip_adi" value = "<?php echo $arac_bilgileri[ 'sahip_adi' ]; ?>" placeholder="Adı" required>
							</div>
							<div class="form-group">
								<label class="control-label">Soyadı</label>
								<input type="text" class="form-control " name ="sahip_soyadi" value = "<?php echo $arac_bilgileri[ 'sahip_soyadi' ]; ?>" placeholder="Soyadı" required>
							</div>
							<div class="form-group">
								<label>Cep Telefonu:</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fas fa-phone"></i></span>
									</div>
									<input type="text" name ="sahip_cep_tel" value = "<?php echo $arac_bilgileri[ 'sahip_cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label class="control-label">E Mail</label>
								<input type="email" class="form-control " name ="sahip_email" value = "<?php echo $arac_bilgileri[ 'sahip_email' ]; ?>" placeholder="E Mail Adresi" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adres</label>
								<input type="text" class="form-control " name ="sahip_adres" value = "<?php echo $arac_bilgileri[ 'sahip_adres' ]; ?>" placeholder="Adres" required>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Vekil</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="vekil1" name="vekil" value="0" <?php if( $arac_bilgileri[ 'vekil' ] == "0" ) echo 'checked'?> >
									<label for="vekil1">
										YOK
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="vekil2" name="vekil" value="1" <?php if( $arac_bilgileri[ 'vekil' ] == "1" ) echo 'checked'?> >
									<label for="vekil2">
										VAR
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 3 ) echo "show active"; ?>" id="arac_bilgi3" role="tabpanel" aria-labelledby="arac_bilgi3_tab">
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Vekil Bilgileri</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "vekil_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "3">
							<div class="form-group">
								<label class="control-label">T.C.Kimilk No</label>
								<input type="text" class="form-control " name ="vekil_tc_no" value = "<?php echo $arac_bilgileri[ 'vekil_tc_no' ]; ?>" placeholder="T.C.Kimilk No" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adı</label>
								<input type="text" class="form-control " name ="vekil_adi" value = "<?php echo $arac_bilgileri[ 'vekil_adi' ]; ?>" placeholder="Adı" required>
							</div>
							<div class="form-group">
								<label class="control-label">Soyadı</label>
								<input type="text" class="form-control " name ="vekil_soyadi" value = "<?php echo $arac_bilgileri[ 'vekil_soyadi' ]; ?>" placeholder="Soyadı" required>
							</div>
							<div class="form-group">
								<label>Cep Telefonu:</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fas fa-phone"></i></span>
									</div>
									<input type="text" name ="vekil_cep_tel" value = "<?php echo $arac_bilgileri[ 'vekil_cep_tel' ]; ?>" class="form-control " data-inputmask='"mask": "0(999) 999-9999"' data-mask required>
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label class="control-label">E Mail</label>
								<input type="email" class="form-control " name ="vekil_email" value = "<?php echo $arac_bilgileri[ 'vekil_email' ]; ?>" placeholder="E Mail Adresi" required>
							</div>
							<div class="form-group">
								<label class="control-label">Adres</label>
								<input type="text" class="form-control " name ="vekil_adres" value = "<?php echo $arac_bilgileri[ 'vekil_adres' ]; ?>" placeholder="Adres" required>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
					<?php if( $arac_bilgileri[ 'vekil' ] == 0 ) echo "<script>form_disabled('vekil_formu');</script>"; ?>
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 4 ) echo "show active"; ?>" id="arac_bilgi4" role="tabpanel" aria-labelledby="arac_bilgi4_tab">
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Araç Bilgileri</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "4">
							<div class="form-group">
								<label class="control-label">Plaka</label>
								<input type="text" class="form-control " name ="plaka" value = "<?php echo $arac_bilgileri[ 'plaka' ]; ?>" placeholder="Örn : 34ABC123" required>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								  <label>Araç Durumu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="arac_durumu1" name="arac_durumu" value="1" <?php if( $arac_bilgileri[ 'arac_durumu' ] == "1" ) echo 'checked'?> >
									<label for="arac_durumu1">
										Sıfır
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="arac_durumu2" name="arac_durumu" value="2" <?php if( $arac_bilgileri[ 'arac_durumu' ] == "2" ) echo 'checked'?> >
									<label for="arac_durumu2">
										2. El
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Plaka Durumu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="plaka_durumu1" name="plaka_durumu" value="1" <?php if( $arac_bilgileri[ 'plaka_durumu' ] == "1" ) echo 'checked'?> >
									<label for="plaka_durumu1">
										TR Plaka
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="plaka_durumu2" name="plaka_durumu" value="2" <?php if( $arac_bilgileri[ 'plaka_durumu' ] == "2" ) echo 'checked'?> >
									<label for="plaka_durumu2">
										Yabancıdan Yabancıya
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="plaka_durumu3" name="plaka_durumu" value="3" <?php if( $arac_bilgileri[ 'plaka_durumu' ] == "3" ) echo 'checked'?> >
									<label for="plaka_durumu3">
										Misafir Plaka
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group">
								<label>Araç Tipi</label>
								<select name="arac_tipi_id" class="form-control  select2" style="width: 100%;" required>
									<option value="">Seçiniz</option>
								<?php foreach( $arac_tipleri[ 2 ] AS $arac_tipi ) { ?>
									<option value = "<?php echo $arac_tipi[ 'id' ]; ?>" <?php if( $arac_tipi[ 'id' ] ==  $arac_bilgileri[ 'arac_tipi_id' ] ) echo 'selected'?>><?php echo $arac_tipi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label>Kasa Tipi</label>
								<select name="arac_kasa_tipi_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_kasa_tipleri[ 2 ] AS $arac_kasa_tipi ) { ?>
									<option value = "<?php echo $arac_kasa_tipi[ 'id' ]; ?>" <?php if( $arac_kasa_tipi[ 'id' ] ==  $arac_bilgileri[ 'arac_kasa_tipi_id' ] ) echo 'selected'?>><?php echo $arac_kasa_tipi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label>Marka (D1)</label>
								<select name="arac_marka_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_markalari[ 2 ] AS $arac_marka ) { ?>
									<option value = "<?php echo $arac_marka[ 'id' ]; ?>" <?php if( $arac_marka[ 'id' ] ==  $arac_bilgileri[ 'arac_marka_id' ] ) echo 'selected'?>><?php echo $arac_marka[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Ticari Adı (D3)</label>
								<input type="text" class="form-control " name ="ticari_adi" value = "<?php echo $arac_bilgileri[ 'ticari_adi' ]; ?>" placeholder="Örn : Passat" required>
							</div>
							<div class="form-group">
								<label class="control-label">Tip / Seri</label>
								<input type="text" class="form-control " name ="model_tipi" value = "<?php echo $arac_bilgileri[ 'model_tipi' ]; ?>" placeholder="Örn : 1.6 TDI" required>
							</div>
							<div class="form-group">
								<label class="control-label">Donanım Paketi</label>
								<input type="text" class="form-control " name ="donanim_paketi" value = "<?php echo $arac_bilgileri[ 'donanim_paketi' ]; ?>" placeholder="Örn : Elegance" required>
							</div>
							<div class="form-group">
								<label>Vites Tipi</label>
								<select name="arac_vites_tipi_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_vites_tipleri[ 2 ] AS $arac_vites_tipi ) { ?>
									<option value = "<?php echo $arac_vites_tipi[ 'id' ]; ?>" <?php if( $arac_vites_tipi[ 'id' ] ==  $arac_bilgileri[ 'arac_vites_tipi_id' ] ) echo 'selected'?>><?php echo $arac_vites_tipi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label>Vites Sayilari</label>
								<select name="arac_vites_sayisi_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_vites_sayilari[ 2 ] AS $arac_vites_sayisi ) { ?>
									<option value = "<?php echo $arac_vites_sayisi[ 'id' ]; ?>" <?php if( $arac_vites_sayisi[ 'id' ] ==  $arac_bilgileri[ 'arac_vites_sayisi_id' ] ) echo 'selected'?>><?php echo $arac_vites_sayisi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Kilometre/Saat</label>
								<input type="text" class="form-control " name ="km" value = "<?php echo $arac_bilgileri[ 'km' ]; ?>" placeholder="Örn : 57532" required>
							</div>
							<div class="form-group">
								<label>Çekiş Tipi</label>
								<select name="arac_cekis_tipi_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_cekis_tipleri[ 2 ] AS $arac_cekis_tipi ) { ?>
									<option value = "<?php echo $arac_cekis_tipi[ 'id' ]; ?>" <?php if( $arac_cekis_tipi[ 'id' ] ==  $arac_bilgileri[ 'arac_cekis_tipi_id' ] ) echo 'selected'?>><?php echo $arac_cekis_tipi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Silindir Hacmi (P1)</label>
								<input type="number" step="0.01" class="form-control " name ="silindir_hacmi" value = "<?php echo $arac_bilgileri[ 'silindir_hacmi' ]; ?>" placeholder="Örn : 2995" required>
							</div>
							<div class="form-group">
								<label>Model Yılı (D4)</label>
								<select name="model_yili" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php for( $i = date( 'Y' );$i>1900;$i-- ) { ?>
									<option value = "<?php echo $i; ?>" <?php if( $i ==  $arac_bilgileri[ 'model_yili' ] ) echo 'selected'?>><?php echo $i;?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label>Yakıt Cinsi (P3)</label>
								<select name="arac_yakit_tipi_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_yakit_tipleri[ 2 ] AS $arac_yakit_tipi ) { ?>
									<option value = "<?php echo $arac_yakit_tipi[ 'id' ]; ?>" <?php if( $arac_yakit_tipi[ 'id' ] ==  $arac_bilgileri[ 'arac_yakit_tipi_id' ] ) echo 'selected'?>><?php echo $arac_yakit_tipi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label>Rengi (R)</label>
								<select name="renk_id" class="form-control  select2" style="width: 100%;">
									<option value="">Seçiniz</option>
								<?php foreach( $arac_renkleri[ 2 ] AS $arac_rengi ) { ?>
									<option value = "<?php echo $arac_rengi[ 'id' ]; ?>" <?php if( $arac_rengi[ 'id' ] ==  $arac_bilgileri[ 'renk_id' ] ) echo 'selected'?>><?php echo $arac_rengi[ 'adi' ]?></option>
								<?php } ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Tipi (D2)</label>
								<input type="text" class="form-control " name ="tipi" value = "<?php echo $arac_bilgileri[ 'tipi' ]; ?>" placeholder="Tipi" required>
							</div>
							<div class="row">
							<div class="form-group col-md-6">
								<label class="control-label">Motor Gücü (P2 - kW)</label>
								<input type="number" step="0.01" oninput="motor_gucu_hp_hesapla(this);" class="form-control " name ="motor_gucu" value = "<?php echo $arac_bilgileri[ 'motor_gucu' ]; ?>" placeholder="Motor Gücü" required>
							</div>
							<div class="form-group col-md-6">
								<label class="control-label">Motor Gücü (HP)</label>
								<input disabled type="number" step="0.01" class="form-control " name ="motor_gucu_hp" id ="motor_gucu_hp" value = "<?php echo round( $arac_bilgileri[ 'motor_gucu' ] * 1.34102 ); ?>" placeholder="Motor Gücü" required>
							</div>
							</div>
							<div class="form-group">
								<label class="control-label">Özellikler ve Ekstralar</label>
								<textarea  id="summernote" class="form-control" name ="arac_ekstra"  placeholder="" ><?php echo $arac_bilgileri[ 'arac_ekstra' ]; ?></textarea>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Garanti Durumu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="garanti_durumu1" name="garanti_durumu" value="1" <?php if( $arac_bilgileri[ 'garanti_durumu' ] == "1" ) echo 'checked'?>>
									<label for="garanti_durumu1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="garanti_durumu2" name="garanti_durumu" value="2" <?php if( $arac_bilgileri[ 'garanti_durumu' ] == "2" ) echo 'checked'?>>
									<label for="garanti_durumu2">
										YOK
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Yedek Anahtar</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="yedek_anahtar1" name="yedek_anahtar" value="1" <?php if( $arac_bilgileri[ 'yedek_anahtar' ] == "1" ) echo 'checked'?>>
									<label for="yedek_anahtar1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="yedek_anahtar2" name="yedek_anahtar" value="2" <?php if( $arac_bilgileri[ 'yedek_anahtar' ] == "2" ) echo 'checked'?>>
									<label for="yedek_anahtar2">
										YOK
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Yetkili Servis Bakımı</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="duzenli_servis_bakimi1" name="duzenli_servis_bakimi" value="1" <?php if( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == "1" ) echo 'checked'?> >
									<label for="duzenli_servis_bakimi1">
										EVET
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="duzenli_servis_bakimi2" name="duzenli_servis_bakimi" value="2" <?php if( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == "2" ) echo 'checked'?> >
									<label for="duzenli_servis_bakimi2">
										HAYIR
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 5 ) echo "show active"; ?>" id="arac_bilgi5" role="tabpanel" aria-labelledby="arac_bilgi5_tab">
					<div class="row">
						<div class="col-md-5">
							<div class="card card-secondary">
							  <div class="card-header">
								<h3 class="card-title">Ruhsat Bilgileri</h3>
							  </div>
							  <!-- /.card-header -->
							  <!-- form start -->
							  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
								<div class="card-body">
									<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
									<input type = "hidden" name = "islem" value = "guncelle">
									<input type = "hidden" name = "tab_no" value = "5">
									<div class="form-group">
										<label class="control-label">Verildiği İl İlçe (Y1)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_verildigi_il_ilce_y1" value = "<?php echo $arac_bilgileri[ 'ruhsat_verildigi_il_ilce_y1' ]; ?>" placeholder="Y1 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">İlk Tescil Tarihi (B9)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_ilk_tescil_tarihi_b9" value = "<?php echo $arac_bilgileri[ 'ruhsat_ilk_tescil_tarihi_b9' ]; ?>" placeholder="B9 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Tescil Tarihi (1)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_tescil_tarihi_1" value = "<?php echo $arac_bilgileri[ 'ruhsat_tescil_tarihi_1' ]; ?>" placeholder="1 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Araç Sınıfı (J)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_arac_sinifi_j" value = "<?php echo $arac_bilgileri[ 'ruhsat_arac_sinifi_j' ]; ?>" placeholder="J alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Cinsi (D5)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_cinsi_d5" value = "<?php echo $arac_bilgileri[ 'ruhsat_cinsi_d5' ]; ?>" placeholder="D5 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Motor No (P5)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_motor_no_p5" value = "<?php echo $arac_bilgileri[ 'ruhsat_motor_no_p5' ]; ?>" placeholder="P5 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Şase No (E)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_sase_no_e" value = "<?php echo $arac_bilgileri[ 'ruhsat_sase_no_e' ]; ?>" placeholder="E alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Koltuk Sayısı (S1)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_koltuk_sayisi_s1" value = "<?php echo $arac_bilgileri[ 'ruhsat_koltuk_sayisi_s1' ]; ?>" placeholder="S1 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Kullanım Amacı (Y3)</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_kullanim_amaci_y3" value = "<?php echo $arac_bilgileri[ 'ruhsat_kullanim_amaci_y3' ]; ?>" placeholder="Y3 alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Belge Seri</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_belge_seri" value = "<?php echo $arac_bilgileri[ 'ruhsat_belge_seri' ]; ?>" placeholder="Belge Seri alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
										<label class="control-label">Belge No</label>
										<input type="text"  class="form-control " oninput="yaz(this);" name ="ruhsat_no" value = "<?php echo $arac_bilgileri[ 'ruhsat_no' ]; ?>" placeholder="Belge No alanına bakıp yazınız." required>
									</div>
									<div class="form-group">
									  <label class="control-label">Muayene Geçerlilik Tarihi:</label>
										<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input type="text" name="ruhsat_muayene_gecerlilik_tarihi" value="<?php if( $arac_bilgileri['ruhsat_muayene_gecerlilik_tarihi'] !=null ) echo date('d.m.Y H:i',strtotime($arac_bilgileri['ruhsat_muayene_gecerlilik_tarihi'])); else echo date('d.m.Y H:i'); ?>" class="form-control  datetimepicker-input" data-target="#datetimepicker2" data-toggle="datetimepicker"/>
										</div>
									</div>
								</div>
								<!-- /.card-body -->
								<div class="card-footer">
									<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
								</div>
							  </form>
							</div>
						</div>					
					<!-- /.card -->
						<div class="col-md-7">
							<div class="card card-secondary">
								<div class="card-header">
									<h3 class="card-title">Ruhsat Görüntüsü</h3>
								</div>
								<br>
								<table style="border:solid 1px gray;border-collapse: collapse;padding:0;" width="100%" id="ruhsat">
									<tr>
										<td style="border:solid 1px gray;" colspan="4" width="50%" class="ruhsat_bg"><div class="ruhsat_baslik">(Y1) VERİLDİĞİ İL İLÇE</div><div class="ruhsat_deger" id="ruhsat_verildigi_il_ilce_y1"><?php echo $arac_bilgileri[ 'ruhsat_verildigi_il_ilce_y1' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="4" width="50%" class="ruhsat_bg"><div class="ruhsat_baslik">(Y4) TC KİMLİK NO / VERGİ NO</div><div class="ruhsat_deger" id="sahip_tc_no"><?php echo $arac_bilgileri[ 'sahip_tc_no' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(A) PLAKA</div><div class="ruhsat_deger" id="plaka"><?php echo $arac_bilgileri[ 'plaka' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(B9) İLK TESCİL TARİHİ</div><div class="ruhsat_deger" id="ruhsat_ilk_tescil_tarihi_b9"><?php echo $arac_bilgileri[ 'ruhsat_ilk_tescil_tarihi_b9' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(C11) SOYADI / TİCARİ ÜNVANI</div><div class="ruhsat_deger" id="sahip_soyadi"><?php echo $arac_bilgileri[ 'sahip_soyadi' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(Y2) TESCİL SIRA NO</div><div class="ruhsat_deger" id="ruhsat_tescil_sira_no_y2"><?php echo $arac_bilgileri[ 'ruhsat_tescil_sira_no_y2' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(1) TESCİL TARİHİ</div><div class="ruhsat_deger" id="ruhsat_tescil_tarihi_1"><?php echo $arac_bilgileri[ 'ruhsat_tescil_tarihi_1' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(C12) ADI</div><div class="ruhsat_deger" id="sahip_adi"><?php echo $arac_bilgileri[ 'sahip_adi' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D1) MARKASI</div><div class="ruhsat_deger" id="arac_marka_adi"><?php echo $arac_bilgileri[ 'arac_marka_adi' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D2) TİPİ</div><div class="ruhsat_deger" id="tipi"><?php echo $arac_bilgileri[ 'tipi' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="4" rowspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(C13) ADRESİ</div><div class="ruhsat_deger" id="sahip_adres"><?php echo $arac_bilgileri[ 'sahip_adres' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D13) TİCARİ ADI</div><div class="ruhsat_deger" id="ticari_adi"><?php echo $arac_bilgileri[ 'ticari_adi' ]; ?></div></td>
										<td style="border:solid 1px gray;" class="ruhsat_bg"><div class="ruhsat_baslik">(D4) MODEL</div><div class="ruhsat_deger" id="model_yili"><?php echo $arac_bilgileri[ 'model_yili' ]; ?></div></td>
										<td style="border:solid 1px gray;" class="ruhsat_bg"><div class="ruhsat_baslik">(J) ARAÇ SINIFI</div><div class="ruhsat_deger" id="ruhsat_arac_sinifi_j"><?php echo $arac_bilgileri[ 'ruhsat_arac_sinifi_j' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" width="25%" class="ruhsat_bg"><div class="ruhsat_baslik">(D5) CİNSİ</div><div class="ruhsat_deger" id="ruhsat_cinsi_d5"><?php echo $arac_bilgileri[ 'ruhsat_cinsi_d5' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" width="25%" class="ruhsat_bg"><div class="ruhsat_baslik">(R) RENGİ</div><div class="ruhsat_deger" id="arac_renk_adi"><?php echo $arac_bilgileri[ 'arac_renk_adi' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" width="25%" rowspan="4"><div class="ruhsat_baslik">(Z1) ARAÇ ÜZERİNDEKİ HAK VE MENFAATİ BULUNANLAR</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2" width="25%"><div class="ruhsat_baslik">(Z31) NOTER SATIŞ TARİHİ</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(P5) MOTOR NO</div><div class="ruhsat_deger" id="ruhsat_motor_no_p5"><?php echo $arac_bilgileri[ 'ruhsat_motor_no_p5' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(Z32) NOTER SATIŞ NO</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(E) ŞASE NO</div><div class="ruhsat_deger" id="ruhsat_sase_no_e"><?php echo $arac_bilgileri[ 'ruhsat_sase_no_e' ]; ?>&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2" rowspan="2"><div class="ruhsat_baslik">(Z33) NOTERİN ADI</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(G1) NET AĞIRLIK</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(F1) AZAMİ YÜK AĞIRLIĞI (kg)</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(G)KATAR AĞIRLIĞI</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(G2) RÖMORK AZAMİ YÜK</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2" rowspan="3"><div class="ruhsat_baslik">(Z2) DİĞER BİLGİLER</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2" rowspan="4"><div class="ruhsat_baslik">(Z4) NOTER MÜHÜR İMZA</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(S1) KOLTUK SAYISI</div><div class="ruhsat_deger" id="ruhsat_koltuk_sayisi_s1"><?php echo $arac_bilgileri[ 'ruhsat_koltuk_sayisi_s1' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(S2) AYAKTA YOLCU SAYISI</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P1) SİLİNDİR HACMİ (cm3)</div><div class="ruhsat_deger" id="silindir_hacmi"><?php echo $arac_bilgileri[ 'silindir_hacmi' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P2) MOTOR GÜCÜ (kw)</div><div class="ruhsat_deger" id="motor_gucu"><?php echo $arac_bilgileri[ 'motor_gucu' ]; ?></div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P3) YAKIT CİNSİ</div><div class="ruhsat_deger" id="arac_yakit_tipi_id"><?php echo $arac_bilgileri[ 'arac_yakit_tipi_adi' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(Q) GÜÇ AĞIRLIK ORANI </div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" colspan="2" rowspan="2"><div class="ruhsat_baslik">(Y5) ONAYLAYAN SİCİL</div><div class="ruhsat_deger">&nbsp;</div></td>
									</tr>
									<tr>
										<td style="border:solid 1px gray;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(Y3) KULLANIM AMACI</div><div class="ruhsat_deger" id="ruhsat_kullanim_amaci_y3"><?php echo $arac_bilgileri[ 'ruhsat_kullanim_amaci_y3' ]; ?></div></td>
										<td style="border:solid 1px gray;" colspan="2"><div class="ruhsat_baslik">(K) TİP ONAY NO</div><div class="ruhsat_deger">&nbsp;</div></td>
										<td style="border:solid 1px gray;" class="ruhsat_bg"><div class="ruhsat_baslik">BELGE SERİ</div><div class="ruhsat_deger" id="ruhsat_belge_seri"><?php echo $arac_bilgileri[ 'ruhsat_belge_seri' ]; ?></div></td>
										<td style="border:solid 1px gray;" class="ruhsat_bg"><div class="ruhsat_baslik">NO</div><div class="ruhsat_deger" id="ruhsat_no"><?php echo $arac_bilgileri[ 'ruhsat_no' ]; ?></div></td>
									</tr>
								</table>
								<br>
								<table>
									<tr>
										<td style="border:solid 1px gray;" class="ruhsat_bg"><div class="ruhsat_baslik">MUAYENE GEÇERLİLİK TARİHİ</div><div class="ruhsat_deger" id="ruhsat_muayene_gecerlilik_tarihi"><?php echo date('d.m.Y',strtotime($arac_bilgileri['ruhsat_muayene_gecerlilik_tarihi'])); ?></div></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 6 ) echo "show active"; ?>" id="arac_bilgi6" role="tabpanel" aria-labelledby="arac_bilgi6_tab">
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Fiyatlama</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->

					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
						<div class="row">
						<div class="col-md-6">
							<button type="button" class="btn btn-success" data-toggle="modal" data-target="#ehb_modal">
							  <i class="fas fa-plus"></i>Yeni Fiyat Ekle
							</button>
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "bos">
							<input type = "hidden" name = "tab_no" value = "6">
							<div class="form-group">
								<label class="control-label">Piyasa Değeri</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number"  class="form-control " name ="piyasa_degeri" id ="piyasa_degeri" value = "<?php echo $arac_bilgileri[ 'piyasa_degeri' ]; ?>" placeholder="Örn : 122000" required>
								</div>
								<a href="https://www.arabam.com/arabam-kac-para" class="" target="_blank">Piyasa Değerini Öğrenmek İçin Tıklayın</a>
							</div>
							<div class="form-group">
								<label class="control-label">Kasko Değeri</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="kasko_degeri" value = "<?php echo $arac_bilgileri[ 'kasko_degeri' ]; ?>" placeholder="Örn : 122000" required>
								</div>
								<a href="https://www.hangikredi.com/sigorta/arac-kasko-deger-listesi" class="" target="_blank">Kasko Değerini Öğrenmek İçin Tıklayın</a>
							</div>
							<div class="form-group">
								<label class="control-label">Talep Edilen Fiyat</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="talep_fiyat" id="talep_fiyat" value = "<?php echo $arac_bilgileri[ 'talep_fiyat' ]; ?>" placeholder="Örn : 122000" required>
								</div>
								<?php $sayac = 1; foreach( $arac_fiyatlar[ 2 ] AS $arac_fiyat ) { 
									$arac_fiyatlari_dizi[] = $arac_fiyat[ 'talep_fiyat' ];
									if( count( $arac_fiyatlar[ 2 ] ) > 1 ){ 										
										if( count( $arac_fiyatlar[ 2 ] ) != $sayac ){
											echo "<span class='text-danger'><del>".$arac_fiyat[ 'talep_fiyat' ]."</del></span>";
											echo " <i class='fas fa-arrow-right'></i> ";
										}else{
											echo "<span class='text-success'>".$arac_fiyat[ 'talep_fiyat' ]."</span>";
										}
									}
									$sayac++;
								} ?>
							</div>
							<div class="form-group">
								<label class="control-label">Ekstra İstenen Hizmet Bedeli</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="ekstra_istenen_hizmet_bedeli" id="ekstra_istenen_hizmet_bedeli" value = "<?php echo $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli' ]+0; ?>" placeholder="Örn : 1500" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Hizmet Bedeli</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="hizmet_bedeli" value = "<?php echo $arac_bilgileri[ 'hizmet_bedeli' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div>
							<!--div class="form-group col-12">
								<div class="input-group">
									<?php if( $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli_onay' ] == 2  ){ ?>
									<span class="btn btn-warning">Ekstra <?php echo $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli' ];?> &#8378; için Onay Bekleniyor</span>
									&nbsp;<button modul= 'araclar' yetki_islem="ekstra_hizmet_bedeli_onayla" type="button" class="btn btn-sm btn-success" data-href="_modul/araclar/araclarSEG.php?islem=ekstra_hizmet_bedeli_onayla&tab_no=6&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" data-toggle="modal" data-target="#araclar_ekstra_hizmet_bedeli_onayla_onay" >Onayla</button>
									<?php } ?>
									<?php if( $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli_onay' ] == 1  ){ ?>
									<span class="btn btn-success">Ekstra <?php echo $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli' ];?> &#8378; için İşlem Onaylandı</span>
									&nbsp;<button modul= 'araclar' yetki_islem="ekstra_hizmet_bedeli_onay_kaldir" type="button" class="btn btn-sm  btn-danger" data-href="_modul/araclar/araclarSEG.php?islem=ekstra_hizmet_bedeli_onay_kaldir&tab_no=6&id=<?php echo $arac_bilgileri[ 'id' ]; ?>" data-toggle="modal" data-target="#araclar_ekstra_hizmet_bedeli_onayla_onay" >Onayı Kaldır</button>
									<?php } ?>
									<?php if( $arac_bilgileri[ 'ekstra_istenen_hizmet_bedeli_onay' ] == 0  ){ ?>
									<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#ehb_modal">
									  Ekstra Hizmet Bedeli Talep Et
									</button>
									<?php } ?>										
								</div>
							</div-->
							<div class="form-group">
								<label class="control-label">Pazarlık Payı</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="pazarlik_payi" value = "<?php echo $arac_bilgileri[ 'pazarlik_payi' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">İlan Fiyatı</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">&#8378;</span>
									</div>
									<input disabled type="number" class="form-control " name ="ilan_fiyati" value = "<?php echo $arac_bilgileri[ 'ilan_fiyati' ]; ?>" placeholder="Örn : 122000" >
								</div>
							</div>
						</div>
						<div class="col-md-6">
						<h3 align="center"> Fiyat Endeksi</h3>
						  <div id="canvas-holder" >
							<canvas id="chart"></canvas>
						  </div>
						  <div class="row">
							  <div class="col-1" ></div>
							  <div class="bg-red text-white col-2" style="background-color:red;text-align:center;font-size:11pt;">Çok Yüksek</div>
							  <div class="text-white col-2" style="background-color:blue;text-align:center;font-size:11pt;">Yüksek</div>
							  <div class="text-black col-2" style="background-color:yellow;text-align:center;font-size:11pt;">Normal</div>
							  <div class="text-black col-2" style="background-color:orange;text-align:center;font-size:11pt;">Satar</div>
							  <div class="text-white col-2" style="background-color:green;text-align:center;font-size:11pt;">Hemen Satar</div>
							  <div class="col-1"></div>
						  </div>
						  <div class="row">
							  <div class="col-1" ></div>
							  <div class="col-2" id="cok_yuksek" style="font-size:9pt;text-align:center;"></div>
							  <div class="col-2" id="yuksek" style="font-size:9pt;text-align:center;"></div>
							  <div class="col-2" id="normal" style="font-size:9pt;text-align:center;"></div>
							  <div class="col-2" id="satar" style="font-size:9pt;text-align:center;"></div>
							  <div class="col-2" id="hemen_satar" style="font-size:9pt;text-align:center;"></div>
							  <div class="col-1"></div>
						  </div>
						</div>
						</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 7 ) echo "show active"; ?>" id="arac_bilgi7" role="tabpanel" aria-labelledby="arac_bilgi7_tab">
					<div class="card card-secondary">
					  <div class="card-header">
						<h3 class="card-title">Kayıt Kontrol</h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "7">
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Rehin Durumu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="rehin_durumu1" name="rehin_durumu" value="1" <?php if( $arac_bilgileri[ 'rehin_durumu' ] == "1" ) echo 'checked'?> >
									<label for="rehin_durumu1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="rehin_durumu2" name="rehin_durumu" value="2" <?php if( $arac_bilgileri[ 'rehin_durumu' ] == "2" ) echo 'checked'?> >
									<label for="rehin_durumu2">
										YOK
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>Trafik Cezası</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="trafik_cezasi1" name="trafik_cezasi" value="1" <?php if( $arac_bilgileri[ 'trafik_cezasi' ] == "1" ) echo 'checked'?> >
									<label for="trafik_cezasi1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="trafik_cezasi2" name="trafik_cezasi" value="2" <?php if( $arac_bilgileri[ 'trafik_cezasi' ] == "2" ) echo 'checked'?> >
									<label for="trafik_cezasi2">
										YOK
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body">
								  <label>MTV Borcu</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="mtv_borcu1" name="mtv_borcu" value="1" <?php if( $arac_bilgileri[ 'mtv_borcu' ] == "1" ) echo 'checked'?> >
									<label for="mtv_borcu1">
										VAR
									</label>
								  </div>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="mtv_borcu2" name="mtv_borcu" value="2" <?php if( $arac_bilgileri[ 'mtv_borcu' ] == "2" ) echo 'checked'?> >
									<label for="mtv_borcu2">
										YOK
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve İlerle</button>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 8 ) echo "show active"; ?>" id="arac_bilgi8" role="tabpanel" aria-labelledby="arac_bilgi8_tab">
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "8">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<?php if( $arac_bilgileri[ 'sahip_kimlik_foto' ] != null ){ ?>
								<div class="card card-default">
								  <div class="card-header">
									<h4 class="card-title">Araç Sahibi Kimlik Fotokopi</h4>
								  </div>
								  <div class="card-body">
									<div class="row">
									  <div class="col-sm-2">
										<a href="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['sahip_kimlik_foto'];?>" data-toggle="lightbox" data-title="<?php echo $arac_bilgileri['arac_no'].' ('.$arac_bilgileri['plaka'].')';?>" data-gallery="gallery">
										  <img src="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['sahip_kimlik_foto'];?>" class="img-fluid mb-2" alt="white sample"/>
										</a>
									  </div>
									</div>
								  </div>
								</div>							
							<?php } if( ($arac_bilgileri[ 'sahip_kimlik_foto' ] == null) OR ($arac_bilgileri[ 'sahip_kimlik_foto' ] == '') OR ($_SESSION[ 'super' ] == 1)  ){ ?>
							<div class="form-group">
								<label for="exampleInputFile">Araç Sahibi Kimlik Fotokopi</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="sahip_kimlik_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php } ?>
							<?php if( $arac_bilgileri[ 'sahip_kimlik_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için merkeze bildiriniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<!-- /.card -->
					<?php if( $arac_bilgileri[ 'vekil' ] == 1 ){ ?>
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "8">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<?php if( $arac_bilgileri[ 'vekil_kimlik_foto' ] != null ){ ?>
								<div class="card card-default">
								  <div class="card-header">
									<h4 class="card-title">Vekil Kimlik Fotokopi</h4>
								  </div>
								  <div class="card-body">
									<div class="row">
									  <div class="col-sm-2">
										<a href="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['vekil_kimlik_foto'];?>" data-toggle="lightbox" data-title="<?php echo $arac_bilgileri['arac_no'].' ('.$arac_bilgileri['plaka'].')';?>" data-gallery="gallery">
										  <img src="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['vekil_kimlik_foto'];?>" class="img-fluid mb-2" alt="white sample"/>
										</a>
									  </div>
									</div>
								  </div>
								</div>							
							<?php } if( ($arac_bilgileri[ 'vekil_kimlik_foto' ] == null) OR ($arac_bilgileri[ 'vekil_kimlik_foto' ] == '') OR ($_SESSION[ 'super' ] == 1)  ){ ?>
							<div class="form-group">
								<label for="exampleInputFile">Vekil Kimlik Fotokopi</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="vekil_kimlik_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php } ?>
							<?php if( $arac_bilgileri[ 'vekil_kimlik_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için merkeze bildiriniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<?php } ?>
					<!-- /.card -->
					<div class="card">
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST"  enctype="multipart/form-data">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "8">
							<input type = "hidden" name = "arac_no" value = "<?php echo $arac_bilgileri[ 'arac_no' ]; ?>">
							<?php if( $arac_bilgileri[ 'ruhsat_foto' ] != null ){ ?>
								<div class="card card-default">
								  <div class="card-header">
									<h4 class="card-title">Ruhsat Fotoğrafı</h4>
								  </div>
								  <div class="card-body">
									<div class="row">
									  <div class="col-sm-2">
										<a href="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['ruhsat_foto'];?>" data-toggle="lightbox" data-title="<?php echo $arac_bilgileri['arac_no'].' ('.$arac_bilgileri['plaka'].')';?>" data-gallery="gallery">
										  <img src="arac_resimler/<?php echo $arac_bilgileri['arac_no'];?>/<?php echo $arac_bilgileri['ruhsat_foto'];?>" class="img-fluid mb-2" alt="white sample"/>
										</a>
									  </div>
									</div>
								  </div>
								</div>							
							<?php } if( ($arac_bilgileri[ 'ruhsat_foto' ] == null) OR ($arac_bilgileri[ 'ruhsat_foto' ] == '') OR ($_SESSION[ 'super' ] == 1)  ){ ?>
							<div class="form-group">
								<label for="exampleInputFile">Ruhsat Fotoğrafı</label>
								<div class="input-group">
								  <div class="custom-file">
									<input type="file" class="custom-file-input" id="exampleInputFile" name="ruhsat_foto"  accept="image/x-png,image/gif,image/jpeg">
									<label class="custom-file-label" for="exampleInputFile">Dosya seçin...</label>
								  </div>
								  <div class="input-group-append">
								  <button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right input-group-text"><span class="fa fa-save"></span> Kaydet</button>
								  </div>
								</div>
							</div>
							<?php } ?>
							<?php if( $arac_bilgileri[ 'ruhsat_foto' ] != null ){ ?>
							<div class="text-success text-sm"><span class="fa fa-check"></span> Bu alana fotoğraf eklenmiştir. Değiştirmek için merkeze bildiriniz.</div>
							<?php } ?>
						</div>
					  </form>
					</div>
					<!-- /.card -->
				  </div>
				  <div class="tab-pane fade <?php if( $_REQUEST[ 'tab_no' ] == 9 ) echo "show active"; ?>" id="arac_bilgi9" role="tabpanel" aria-labelledby="arac_bilgi9_tab">
					<div class="card">
					  <!--div class="card-header">
						<h3 class="card-title">Araç bilgisi 1</h3>
					  </div-->
					  <!-- /.card-header -->
					  <!-- form start -->
					  <form id = "kayit_formu" action = "_modul/araclar/araclarSEG.php" method = "POST" class="">
						<div class="card-body">
							<input type = "hidden" name = "id" value = "<?php echo $arac_bilgileri[ 'id' ]; ?>">
							<input type = "hidden" name = "islem" value = "guncelle">
							<input type = "hidden" name = "tab_no" value = "9">
							<div class="form-group clearfix card">
								<div class="card-body ">
								<a modul= 'araclar' yetki_islem="hizmet_sozlesmesi" class = "btn btn-sm btn-success" style="width:200px;" href = "_modul/araclar/hizmet_sozlesmesi.php?arac_id=<?php echo $arac_bilgileri[ 'id' ]; ?>" target="_blank">
									Satıcı Hizmet Sözleşmesi
								</a><br>
								  <label>Satıcı Hizmet Sözleşmesi</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_hizmet_sozlesmesi1" name="print_hizmet_sozlesmesi" value="1" <?php if( $arac_bilgileri[ 'print_hizmet_sozlesmesi' ] == "1" ) echo 'checked'?> >
									<label for="print_hizmet_sozlesmesi1">
										Yazdırıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="print_hizmet_sozlesmesi2" name="print_hizmet_sozlesmesi" value="2" <?php if( $arac_bilgileri[ 'print_hizmet_sozlesmesi' ] == "2" ) echo 'checked'?> >
									<label for="print_hizmet_sozlesmesi2">
										Yazdırılmadı
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								<a modul= 'araclar' yetki_islem="kapak_yazdir" class = "btn btn-sm btn-warning" style="width:200px;" href = "_modul/araclar/arac_info.php?arac_id=<?php echo $arac_bilgileri[ 'id' ]; ?>" target="_blank">
									Kapak Yazdır
								</a><br>
								  <label>Kapak</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_arac_info1" name="print_arac_info" value="1" <?php if( $arac_bilgileri[ 'print_arac_info' ] == "1" ) echo 'checked'?> >
									<label for="print_arac_info1">
										Yazdırıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="print_arac_info2" name="print_arac_info" value="2" <?php if( $arac_bilgileri[ 'print_arac_info' ] == "2" ) echo 'checked'?> >
									<label for="print_arac_info2">
										Yazdırılmadı
									</label>
								  </div>
								</div>
							</div>
							<div class="form-group clearfix card">
								<div class="card-body ">
								<a modul= 'araclar' yetki_islem="qr_kod_yazdir" class = "btn btn-sm btn-primary" style="width:200px;" href = "_modul/araclar/qr.php?arac_no=<?php echo $arac_bilgileri[ 'arac_no' ]; ?>" target="_blank">
									QR Kod Yazdır
								</a><br>
								  <label>QR Kod</label><br>
								  <div class="icheck-success d-inline">
									<input required type="radio" id="print_qr_kod1" name="print_qr_kod" value="1" <?php if( $arac_bilgileri[ 'print_qr_kod' ] == "1" ) echo 'checked'?> >
									<label for="print_qr_kod1">
										Yazdırıldı
									</label>
								  </div>
								  <div class="icheck-danger d-inline">
									<input required type="radio" id="print_qr_kod2" name="print_qr_kod" value="2" <?php if( $arac_bilgileri[ 'print_qr_kod' ] == "2" ) echo 'checked'?> >
									<label for="print_qr_kod2">
										Yazdırılmadı
									</label>
								  </div>
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button modul= 'araclar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet ve Bitir</button>
						</div>
					  </form>
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
<script>
function motor_gucu_hp_hesapla(x){
	document.getElementById('motor_gucu_hp').value = Math.round(x.value * 1.34102);
}
</script>
<script>
  $(function () {
    // Summernote
    $('#summernote').summernote()

    // CodeMirror
    CodeMirror.fromTextArea(document.getElementById("codeMirrorDemo"), {
      mode: "htmlmixed",
      theme: "monokai"
    });
  })
</script>

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
	
	$(function () {
		$('#datetimepicker2').datetimepicker({
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
<script>
$(function () {
  bsCustomFileInput.init();
});
</script>
<script>
function yaz(ruhsat_alan_id){
	document.getElementById(ruhsat_alan_id.name).innerHTML =ruhsat_alan_id.value;
}
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
var randomScalingFactor = function() {
  return Math.round(Math.random() * 100);
};

var randomData = function () {
  return [
    randomScalingFactor(),
    randomScalingFactor(),
    randomScalingFactor(),
    randomScalingFactor()
  ];
};

var randomValue = function (data) {
  return Math.max.apply(null, data) * Math.random();
};

//var data = randomData();
//var value = randomValue(data);
var x1,x2,x3,x4,fark,talep_fiyat,ibre_yuzde;
var piyasa_fiyati = document.getElementById('piyasa_degeri').value;
var talep_fiyat = document.getElementById('talep_fiyat').value;
x1 = piyasa_fiyati * 130 / 100;
x2 = piyasa_fiyati * 110 / 100;
x3 = piyasa_fiyati * 103 / 100;
x4 = piyasa_fiyati * 97 / 100;
x5 = piyasa_fiyati * 93 / 100;
x6 = piyasa_fiyati * 70 / 100;
fark1 = x1-x2;
fark2 = x2-x3;
fark3 = x3-x4;
fark4 = x4-x5;
fark5 = x5-x6;
fark = x1-x6;
y1 = 100*fark1/fark;
y2 = 100*fark2/fark;
y3 = 100*fark3/fark;
y4 = 100*fark4/fark;
y5 = 100*fark5/fark;

document.getElementById('cok_yuksek').innerHTML 	= 'Min : ' + (new Intl.NumberFormat('de-DE').format(x2)) + '<br>Max : ' +(new Intl.NumberFormat('de-DE').format(x1));
document.getElementById('yuksek').innerHTML 		= 'Min : ' + (new Intl.NumberFormat('de-DE').format(x3)) + '<br>Max : ' +(new Intl.NumberFormat('de-DE').format(x2));
document.getElementById('normal').innerHTML 		= 'Min : ' + (new Intl.NumberFormat('de-DE').format(x4)) + '<br>Max : ' +(new Intl.NumberFormat('de-DE').format(x3));
document.getElementById('satar').innerHTML 			= 'Min : ' + (new Intl.NumberFormat('de-DE').format(x5)) + '<br>Max : ' +(new Intl.NumberFormat('de-DE').format(x4));
document.getElementById('hemen_satar').innerHTML 	= 'Min : ' + (new Intl.NumberFormat('de-DE').format(x6)) + '<br>Max : ' +(new Intl.NumberFormat('de-DE').format(x5));
if(talep_fiyat>=x1)
	talep_fiyat = x1;

if(talep_fiyat<=x6)
	talep_fiyat = x6;

piyasa_y = y1+y2+(y3/2);
talep_fark = Math.abs(talep_fiyat - piyasa_fiyati);
ibre_yuzde = 100*talep_fark/fark;

var data = [y1,y1+y2,y1+y2+y3,y1+y2+y3+y4,y1+y2+y3+y4+y5];

if(talep_fiyat>piyasa_fiyati)
	var value = piyasa_y - ibre_yuzde;
else
	var value = piyasa_y + ibre_yuzde;

var config = {
  type: 'gauge',
  data: {
    //labels: ['Success', 'Warning', 'Warning', 'Error'],
    datasets: [{
      data: data,
      value: value,
      backgroundColor: ['#d9534f', 'blue', 'yellow', 'orange','green'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    title: {
      display: false,
      text: 'Gauge chart'
    },
    layout: {
      padding: {
        bottom: 30
      }
    },
    needle: {
      // Needle circle radius as the percentage of the chart area width
      radiusPercentage: 2,
      // Needle width as the percentage of the chart area width
      widthPercentage: 3.2,
      // Needle length as the percentage of the interval between inner radius (0%) and outer radius (100%) of the arc
      lengthPercentage: 80,
      // The color of the needle
      color: 'rgba(0, 0, 0, 1)'
    },
    valueLabel: {
		display: false,
		formatter: Math.round
    }
  }
};
document.getElementById('talep_fiyat').addEventListener('input', function() {
	
var x1,x2,x3,x4,fark,talep_fiyat,ibre_yuzde;
var piyasa_fiyati = document.getElementById('piyasa_degeri').value;
var talep_fiyat = document.getElementById('talep_fiyat').value;
x1 = piyasa_fiyati * 130 / 100;
x2 = piyasa_fiyati * 110 / 100;
x3 = piyasa_fiyati * 103 / 100;
x4 = piyasa_fiyati * 97 / 100;
x5 = piyasa_fiyati * 93 / 100;
x6 = piyasa_fiyati * 70 / 100;
fark1 = x1-x2;
fark2 = x2-x3;
fark3 = x3-x4;
fark4 = x4-x5;
fark5 = x5-x6;
fark = x1-x6;
y1 = 100*fark1/fark;
y2 = 100*fark2/fark;
y3 = 100*fark3/fark;
y4 = 100*fark4/fark;
y5 = 100*fark5/fark;

if(talep_fiyat>=x1)
	talep_fiyat = x1;

if(talep_fiyat<=x6)
	talep_fiyat = x6;

piyasa_y = y1+y2+(y3/2);
talep_fark = Math.abs(talep_fiyat - piyasa_fiyati);
ibre_yuzde = 100*talep_fark/fark;

var data = [y1,y1+y2,y1+y2+y3,y1+y2+y3+y4,y1+y2+y3+y4+y5];

if(talep_fiyat>piyasa_fiyati)
	var value = piyasa_y - ibre_yuzde;
else
	var value = piyasa_y + ibre_yuzde;

	
	
  config.data.datasets.forEach(function(dataset) {
    dataset.data = data;
    dataset.value = value;
  });
  window.myGauge.update();
});

window.onload = function() {
  var ctx = document.getElementById('chart').getContext('2d');
  window.myGauge = new Chart(ctx, config);
};

document.getElementById('randomizeData').addEventListener('click', function() {
  config.data.datasets.forEach(function(dataset) {
    dataset.data = randomData();
    dataset.value = randomValue(dataset.data);
  });

  window.myGauge.update();
});
</script>