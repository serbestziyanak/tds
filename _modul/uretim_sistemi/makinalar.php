<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'sayacCihaz_id' ] = $_SESSION[ 'sonuclar' ][ 'sayacCihaz_id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
	sm.*,
	sip.adi AS is_parca_adi,
	ssc.sayac_no,
	smt.adi AS makina_turu_adi,
	CONCAT( p.adi, " ", p.soyadi ) AS personel_ad_soyad
FROM 
	sayac_makina AS sm
LEFT JOIN sayac_is_parcalari AS sip ON sip.id = sm.is_parca_id
LEFT JOIN sayac_makina_turleri AS smt ON smt.id = sm.makina_turu_id
LEFT JOIN sayac_sayac_cihazlari AS ssc ON ssc.id = sm.sayac_cihaz_id
LEFT JOIN tb_personel as p on sm.personel_id = p.id
WHERE
	sm.firma_id = ? AND
	sm.aktif 	= 1
SQL;

$SQL_sayac_cihazlari = <<< SQL
SELECT
	*
FROM 
	sayac_sayac_cihazlari
WHERE
	firma_id = ? AND
	id NOT IN (
				SELECT 
					sayac_cihaz_id
				FROM sayac_makina 
				WHERE 
					firma_id = sayac_makina.firma_id AND 
					aktif = 1 
			) AND
	aktif 	 = 1
SQL;

$SQL_is_parcalari = <<< SQL
SELECT
	*
FROM 
	sayac_is_parcalari
WHERE
	firma_id = ? AND
	aktif 	 = 1
SQL;

$SQL_makina_turleri = <<< SQL
SELECT
	*
FROM 
	sayac_makina_turleri
WHERE
	firma_id = ? AND
	aktif 	 = 1
SQL;

$SQL_tekMakina_oku = <<< SQL
SELECT
	*
FROM 
	sayac_makina
WHERE
	id 			= ? AND 
	firma_id 	= ?
SQL;

$SQL_personeller = <<< SQL
SELECT
	id,
	CONCAT(adi," " ,soyadi) AS adisoyadi
FROM 
	tb_personel
WHERE
	firma_id = ? AND
	id NOT IN (
				SELECT 
					personel_id
				FROM sayac_makina 
				WHERE 
					firma_id = sayac_makina.firma_id AND 
					aktif = 1 
			) AND
	aktif 	 = 1
SQL;


$sayacCihaz_id	= array_key_exists( 'sayacCihaz_id', $_REQUEST ) ? $_REQUEST[ 'sayacCihaz_id' ] : 0;
$islem			= array_key_exists( 'islem', $_REQUEST ) 		 ? $_REQUEST[ 'islem' ] 		: 'ekle';

$makinaTurleri	= $vt->select( $SQL_makina_turleri, array( $_SESSION[ "firma_id" ] ) )[2];
$isParcalari	= $vt->select( $SQL_is_parcalari, array( $_SESSION[ "firma_id" ] ) )[2];
$cihazlar		= $vt->select( $SQL_sayac_cihazlari, array( $_SESSION[ "firma_id" ] ) )[2];
$makinalar		= $vt->select( $SQL_oku, array( $_SESSION[ "firma_id" ] ) );
$tekMakina		= $vt->select( $SQL_tekMakina_oku, array( $sayacCihaz_id, $_SESSION[ "firma_id" ] ) );
$personeller	= $vt->select( $SQL_personeller, array( $_SESSION[ "firma_id" ] ) )[2];

$cihazBilgisi = array(
	 'id'						=> $sayacCihaz_id > 0 ? $sayacCihaz_id : 0
	,'personel_id'				=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'personel_id' ] : ''
	,'makina_id'				=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'makina_id' ] : ''
	,'makina_turu_id'			=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'makina_turu_id' ] : ''
	,'makina_seri_no'			=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'makina_seri_no' ] : ''
	,'makina_marka'				=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'makina_marka' ] : ''
	,'is_parca_id'				=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'is_parca_id' ] : ''
	,'sayac_cihaz_id'			=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'sayac_cihaz_id' ] : ''
	,'is_basina_sayac_sayisi'	=> $sayacCihaz_id > 0 ? $tekMakina[ 2 ][ 0 ][ 'is_basina_sayac_sayisi' ] : ''
);

$satir_renk				= $sayacCihaz_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $sayacCihaz_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $sayacCihaz_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

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
	<div class="col-md-8">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Makinalar</h3>
			</div>
			<div class="card-body">
				<table id="example2" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th>Personel</th>
							<th>Makina Türü</th>
							<th>Makina Marka</th>
							<th>İş Parçası</th>
							<th>Cihaz Numarası</th>
							<th>İ.B.S.S.</th>
							<th data-priority="1" style="width: 20px">Düzenle</th>
							<th data-priority="1" style="width: 20px">Sil</th>
						</tr>
					</thead>
					<tbody>
						<?php 
							$sayi = ($sayfa-1)*$limit+1;  
							foreach( $makinalar[ 2 ] AS $makina ) { ?>
								<tr <?php if( $makina[ 'id' ] == $sayacCihaz_id ) echo "class = '$satir_renk'"; ?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $makina[ 'personel_ad_soyad' ]; ?></td>
									<td><?php echo $makina[ 'makina_turu_adi' ]; ?></td>
									<td><?php echo $makina[ 'makina_marka' ]; ?></td>
									<td><?php echo $makina[ 'is_parca_adi' ]; ?></td>
									<td><?php echo $makina[ 'sayac_no' ]; ?></td>
									<td><?php echo $makina[ 'is_basina_sayac_sayisi' ]; ?></td>
									<td align = "center">
										<a modul = 'makinalar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=makinalar&islem=guncelle&sayacCihaz_id=<?php echo $makina[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul = 'makinalar' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/uretim_sistemi/makinalarSEG.php?islem=sil&sayacCihaz_id=<?php echo $makina[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay" >Sil</button>
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
	<div class="col-md-4">
		<div class="card card-secondary">
		<div class="card-header">
			<h3 class="card-title">Makina Ekle / Güncelle</h3>
		</div>
		<form id = "kayit_formu" action = "_modul/uretim_sistemi/makinalarSEG.php" method = "POST">
			<div class="card-body">
			<input type = "hidden" name = "sayacCihaz_id" value = "<?php echo $cihazBilgisi[ 'id' ]; ?>">
			<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
			
			<div class="form-group">
				<label  class="control-label">Personel</label>
				<select  class="form-control select2"  name = "personel_id" required>
					<option value=''>Personel Seçiniz</option>
					<?php foreach( $personeller as $personel ) { ?>
						<option value = "<?php echo $personel[ 'id' ]; ?>" <?php echo $cihazBilgisi[ 'personel_id' ] == $personel[ 'id' ] ? 'selected' : ''; ?>><?php echo $personel['adisoyadi']; ?></option>
					<?php } ?>
				</select>
			</div>
			
			<div class="form-group">
				<label  class="control-label">Makina Türü</label>
				<select  class="form-control select2"  name = "makina_turu_id" required>
					<option value="">Seçiniz</option>
					<?php foreach( $makinaTurleri as $tur ) { ?>
						<option value = "<?php echo $tur[ 'id' ]; ?>" <?php echo $cihazBilgisi[ 'makina_turu_id' ] == $tur[ 'id' ] ? 'selected' : ''; ?>><?php echo $tur['adi']; ?></option>
					<?php } ?>
				</select>
			</div>
			
			<div class="form-group">
				<label  class="control-label">Makina Seri No</label>
				<input autocomplete="off" type="text" class="form-control" name ="makina_seri_no" value = "<?php echo $cihazBilgisi[ 'makina_seri_no' ]; ?>" required placeholder="">
			</div>
			
			<div class="form-group">
				<label  class="control-label">Makina Marka</label>
				<input autocomplete="off" type="text" class="form-control" name ="makina_marka" value = "<?php echo $cihazBilgisi[ 'makina_marka' ]; ?>" required placeholder="">
			</div>
			
			<div class="form-group">
				<label  class="control-label">İş Parçası</label>
				<select  class="form-control select2"  name = "is_parca_id" required>
					<option value="">İş Parçası Seçiniz</option>
					<?php foreach( $isParcalari as $parca ) { ?>
						<option value = "<?php echo $parca[ 'id' ]; ?>" <?php echo $cihazBilgisi[ 'is_parca_id' ] == $parca[ 'id' ] ? 'selected' : ''; ?>><?php echo $parca['adi']; ?></option>
					<?php } ?>
				</select>
			</div>
			
			<div class="form-group">
				<label  class="control-label">Cihaz</label>
				<select  class="form-control select2"  name = "sayac_cihaz_id" required>
					<option value=''>Cihaz Numarası Seçiniz</option>
					<?php foreach( $cihazlar as $cihaz ) { ?>
						<option value = "<?php echo $cihaz[ 'id' ]; ?>" <?php echo $cihazBilgisi[ 'sayac_cihaz_id' ] == $cihaz[ 'id' ] ? 'selected' : ''; ?>><?php echo $cihaz['sayac_no']; ?></option>
					<?php } ?>
				</select>
			</div>
			
			<div class="form-group">
				<label  class="control-label">İş Başına Sayaç Sayısı</label>
				<input autocomplete="off" min="1" type="number" class="form-control" name ="is_basina_sayac_sayisi" value = "<?php echo $cihazBilgisi[ 'is_basina_sayac_sayisi' ]; ?>" required placeholder="">
			</div>
			
			<div class="card-footer">
			<button modul= 'makinalar' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi?></button>
				<button onclick="window.location.href = '?modul=makinalar&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
			</div>
		</form>
		</div>
	</div>
</div>



