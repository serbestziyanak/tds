<?php
session_start();
include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
/* Tüm yetki işlem türlerini oku */
// $SQL_yetki_islem_turu_listesi_tumu = <<< SQL
// SELECT
	// *
// FROM
	// tb_yetki_islem_turleri
// SQL;

$SQL_module_atanan_tum_yetki_islem_turleri = <<< SQL
SELECT
	 yit.id
	,yit.gorunen_adi
	,yit.adi
FROM
	tb_modul_yetki_islemler AS myi
JOIN
	tb_yetki_islem_turleri AS yit ON myi.yetki_islem_id = yit.id
WHERE
	myi.modul_id = ?
SQL;

/* Bir rol ve modüle ait yetki işlem türleri */
$SQL_rol_modul_yetki_islem_turleri = <<< SQL
SELECT
	*
FROM
	tb_rol_yetkiler
WHERE
	modul_id = ? AND rol_id = ?
SQL;

/* Bir rol ve modüle ait yetki işlemlerini kaydet */
$SQL_rol_modul_yetki_islem_turleri_kaydet = <<< SQL
INSERT INTO
	tb_rol_yetkiler
SET
	 rol_id			= ?
	,modul_id		= ?
	,islem_turu_id	= ?
SQL;

/* Rol yetkilerde bir rolün modülüne ait yetkileri temizle ve yeni gelen yetki işlmemlerini rol e ait modüle ekle.  */
$SQL_rol_yetkileri_temizle = <<< SQL
DELETE FROM tb_rol_yetkiler WHERE rol_id = ? AND modul_id = ?
SQL;

/* Sevkiyat modülünde seçilen sipariş koduna ait güzergahları getir */
$SQL_siparis_guzergahlar = <<< SQL
SELECT 
	sg.*
	,concat(cd.adi,'->',vd.adi) AS guzergah
FROM
	tb_siparis_guzergah AS sg
LEFT JOIN
	tb_siparisler AS sip ON sg.siparis_id=sip.id
LEFT JOIN
	tb_depolar AS cd ON sg.cikis_depo_id=cd.id
LEFT JOIN
	tb_depolar AS vd ON sg.varis_depo_id=vd.id
WHERE 
	sip.id=?
ORDER BY 
	sip.id,sg.id
SQL;

/* Rapor Sevkiyat modülünde seçilen sipariş koduna ait güzergahları getir */
$SQL_rapor_sevkiyata_ait_siparis_guzergahlar = <<< SQL
SELECT 
	 sg.*
	,concat(cd.adi,'->',vd.adi) AS adi
FROM
	tb_siparis_guzergah AS sg
LEFT JOIN
	tb_siparisler AS sip ON sg.siparis_id=sip.id
LEFT JOIN
	tb_depolar AS cd ON sg.cikis_depo_id=cd.id
LEFT JOIN
	tb_depolar AS vd ON sg.varis_depo_id=vd.id
WHERE
	sip.aktif = 1
SQL;


/* Rapor üretim modülünde seçilen firma idlere göre firmaların lotlarını getir. */
$SQL_rapor_uretim_firmaya_gore_latlari_ver = <<< SQL
SELECT
	 l.id
	,CONCAT( f.on_ek, on_ek_sira, ' ( ', f.adi, ' ) ' ) AS adi
FROM
	tb_lot_tanimlari AS l
JOIN
	tb_firmalar AS f ON l.firma_id = f.id
WHERE
	l.aktif = 1 
SQL;

/* Bildirim Deneme. */
$SQL_bildirim_getir = <<< SQL
SELECT
	 bil.*
	,concat( f.on_ek, l.on_ek_sira ) as lot_adi
	,concat( f.on_ek, l.on_ek_sira, '-', af.on_ek, '-', sip.sira ) as siparis_kodu
	,concat( k.adi,' ',k.soyadi ) as kullanici_adi
	,k.resim as kullanici_resim
FROM tb_bildirimler as bil
LEFT JOIN
	tb_lot_tanimlari AS l ON bil.lot_id = l.id
LEFT JOIN
	tb_siparisler AS sip ON bil.siparis_id = sip.id
LEFT JOIN
	tb_sozlesmeler AS soz ON sip.sozlesme_id = soz.id
LEFT JOIN
	tb_firmalar AS f ON l.firma_id = f.id
LEFT JOIN
	tb_firmalar AS af ON soz.alici_firma_id = af.id
LEFT JOIN
	tb_sistem_kullanici AS k ON bil.kullanici_id = k.id
WHERE
	bil.aktif = 1 and iletilen_kullanici_id = ?
ORDER BY bil.ekleme_tarihi DESC
SQL;


$SQL_ilceler_getir = <<< SQL
SELECT
	*
FROM
	tb_ilceler
WHERE 
	il_id = ?
SQL;

$SQL_ders_yillari_getir = <<< SQL
SELECT
	*
FROM
	tb_ders_yillari
WHERE 
	universite_id = ? AND
 	aktif = 1
SQL;

$SQL_donemler_getir = <<< SQL
SELECT
	*
FROM
	tb_donemler
WHERE 
	universite_id = ? AND
	program_id 	  = ? AND
 	aktif = 1
SQL;

/*Programa ait dersler*/
$SQL_dersler_getir = <<< SQL
SELECT
	id,
	adi,
	ders_kodu
FROM
	tb_dersler
WHERE 
	program_id 	  = ? AND
 	aktif = 1
SQL;



$vt = new VeriTabani();

switch( $_POST[ 'islem' ] ) {
	case 'dersYillariListe':
		$ders_yillari = $vt->select( $SQL_ders_yillari_getir, array( $_SESSION['universite_id'] ) )[ 2 ];
		$option = '';
		foreach( $ders_yillari AS $yil ) {
			$option .="
				<option value='$yil[id]'>$yil[adi]</option>
			";
		}
		$select = '<label  class="control-label">Ders Yılı</label>
					<select class="form-control select2" name = "ders_yili_id" id="ders-yili-sec" data-url="./_modul/ajax/ajax_data.php" data-islem="donemListesi" required>
						<option>Seçiniz...</option>
						'.$option.'
					</select>
					<script>
						$("#ders-yili-sec").on("change", function(e) { 
					    var program_id 		= $("#program-sec").val();
					    var ders_yili_id 	= $(this).val();
					    var data_islem 		= $(this).data("islem");
					    var data_url 		= $(this).data("url");
					    $("#donemListesi").empty();
					    $.post(data_url, { islem : data_islem,ders_yili_id : ders_yili_id,program_id : program_id}, function (response) {
					        $("#donemListesi").append(response);
					    });
					});
					</script>';
		echo $select;
	break;

	case 'donemListesi':
		$donemler = $vt->select( $SQL_donemler_getir, array( $_SESSION[ "universite_id" ], $_REQUEST[ "program_id" ] ) )[ 2 ];
		$option = '';
		foreach( $donemler AS $donem ) {
			$option .="
				<option value='$donem[id]'>$donem[adi]</option>
			";
		}
		$select = '<label  class="control-label">Dönem</label>
					<select class="form-control select2" name = "donem_id" id="donemler" data-url="./_modul/ajax/ajax_data.php" data-islem="dersler" required>
						<option>Seçiniz...</option>
						'.$option.'
					</select>
					<script>
						$("#donemler").on("change", function(e) {
							var program_id 		= $("#program-sec").val();
							var data_islem 		= $(this).data("islem");
						    var data_url 		= $(this).data("url");
						    $("#dersler").empty();
						    $.post(data_url, { islem : data_islem,program_id : program_id}, function (response) {
						        $("#dersler").append(response);
						    });
						});
					</script>';
		
		echo $select;
	break;
	

	case 'dersler':
		$dersler 	= $vt->select( $SQL_dersler_getir, array( $_REQUEST[ "program_id" ] ) )[ 2 ];
		$dersSonuc 		= "";
		foreach ($dersler as $ders) {
			$dersSonuc .= '
				<div class="form-group " style="display: flex; align-items: center;">
					<div class="custom-control custom-checkbox col-sm-8 float-left">
						<input class="custom-control-input " name="ders_id[]" type="checkbox" id="'.$ders[ "id" ].'" value="'.$ders[ "id" ].'">
						<label for="'.$ders[ "id" ].'" class="custom-control-label">'.$ders[ "ders_kodu" ].' - '.$ders[ "adi" ].'</label>
					</div>
					<input  type="number" class="form-control col-sm-2 float-left" name ="teorik_ders_saati-'.$ders[ "id" ].'"  autocomplete="off">
					<input  type="number" class="form-control col-sm-2 float-left" name ="uygulama_ders_saati-'.$ders[ "id" ].'"  autocomplete="off">
				</div><hr>';
		}

		$sonuc =  '
			<hr>
			<div class="col-sm-12">
				<div class="form-group " style="display: flex; align-items: center;">
					<div class="custom-control custom-checkbox col-sm-8 float-left">
						<b>Ders</b>
					</div>
					<div class="col-sm-2 float-left"><b>Teaorik D.S.</b></div>
					<div class="col-sm-2 float-left"><b>Uygulama D.S.</b></div>
					
				</div>
			</div>

			<div class="col-sm-12">
				'.$dersSonuc.'
			</div>
			<script>
				$(".dersSecimi").on("click", function() {
					
				});
			</script>';
		echo $sonuc;
	break;
	
	case 'ilce_ver':

		$ilceler	= $vt->select( $SQL_ilceler_getir, array( $_REQUEST[ 'il_id' ] ) );
		$sonuc = "<option value=''>Seçiniz</option>";
		foreach( $ilceler[ 2 ] AS $ilce ) {
			$sonuc.="
				<option value='$ilce[id]'>$ilce[adi]</option>
			";
		}
		echo $sonuc;
	break;
	case 'cevap_turune_gore_secenek_ver':
		$sonuc = "";
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 1 ){ $deger1 = "Evet"; $deger2 = "Hayır"; }
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 2 ){ $deger1 = "Doğru"; $deger2 = "Yanlış"; }
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 3 ){ $deger1 = "Var"; $deger2 = "Yok"; }
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 1 OR $_REQUEST[ 'soru_cevap_turu_id' ] == 2 OR $_REQUEST[ 'soru_cevap_turu_id' ] == 3 ){ 
			$sonuc = "
				<div class='input-group mb-3'>
				<div class='input-group-prepend'>
					<span class='input-group-text'><input type='radio' name='radiosecenek'></span>
				</div>
				<input type='text' class='form-control' name='' value='$deger1' disabled>
				<input type='hidden' class='form-control' name='secenekler[]' value='$deger1'>
				<div class='input-group-append'>
					<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
				</div>			  
				</div>						
				<div class='input-group mb-3'>
				<div class='input-group-prepend'>
					<span class='input-group-text'><input type='radio' name='radiosecenek'></span>
				</div> 
				<input type='text' class='form-control' name='' value='$deger2' disabled>
				<input type='hidden' class='form-control' name='secenekler[]' value='$deger2' >
				<div class='input-group-append'>
					<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
				</div>
				</div>
				<input type='hidden' id='secenek_sayisi' value='2'>
			";
		}
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 4 ){
			$sonuc = "
			<div class='form-group'>
				<button type='button' class='btn btn-xs btn-info' onclick='secenek_ekle()'><i class='fas fa-plus'></i> Seçenek Ekle</button>
			</div>
			<div class='input-group mb-3'>
			  <div class='input-group-prepend'>
				<span class='input-group-text'><input type='radio' name='radiosecenek'></span>
			  </div>
			  <input type='text' class='form-control' name='secenekler[]' value='' required>
			  <div class='input-group-append'>
				<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
			  </div>			  
			</div>						
			<div class='input-group mb-3'>
			  <div class='input-group-prepend'>
				<span class='input-group-text'><input type='radio' name='radiosecenek'></span>
			  </div> 
			  <input type='text' class='form-control' name='secenekler[]' value='' required>
			  <div class='input-group-append'>
				<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
			  </div>
			</div>
			<input type='hidden' id='secenek_sayisi' value='2'>			
			";
		}
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 5 ){
			$sonuc = "
			<div class='form-group'>
				<button type='button' class='btn btn-xs btn-info' onclick='secenek_ekle()'><i class='fas fa-plus'></i> Seçenek Ekle</button>
			</div>
			<div class='input-group mb-3'>
			  <div class='input-group-prepend'>
				<span class='input-group-text'><input type='checkbox' ></span>
			  </div>
			  <input type='text' class='form-control' name='secenekler[]' value='' required>
			  <div class='input-group-append'>
				<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
			  </div>			  
			</div>						
			<div class='input-group mb-3'>
			  <div class='input-group-prepend'>
				<span class='input-group-text'><input type='checkbox' ></span>
			  </div> 
			  <input type='text' class='form-control' name='secenekler[]' value='' required>
			  <div class='input-group-append'>
				<button type='button' class='btn btn-danger' disabled><i class='fas fa-trash-alt'></i></button>
			  </div>
			</div>
			<input type='hidden' id='secenek_sayisi' value='2'>			
			";
		}
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 6 ){
			$sonuc = "
			<div class='input-group mb-3'>
			  <div class='input-group-prepend'>
				<span class='input-group-text'>Cevabınız</span>
			  </div>
			  <input type='text' class='form-control' placeholder='Cevap bu şekilde text alanına girilecektir.'>			  
			</div>						
			";
		}
		if( $_REQUEST[ 'soru_cevap_turu_id' ] == 7 ){
			$sonuc = "
			<div class='form-group'>
			<div class='input-group'>
			  <div class='custom-file'>
				<input type='file' class='custom-file-input' id='exampleInputFile'>
				<label class='custom-file-label' for='exampleInputFile'>Dosya Seçiniz...</label>
			  </div>
			</div>
			<small  class='form-text text-muted'>Kullanıcılar dosya yükleme alanını bu şekilde göreceklerdir.</small>
		  </div>
		  ";
		}
		
		
		
		echo $sonuc;
	break;
	case 'rapor_sevkiyat_guzergahlari_ver':
		$siparis_idler = array_key_exists( 'siparis_idler', $_REQUEST ) ? join( "','", array_map( 'intval', $_REQUEST[ 'siparis_idler' ] ) ) : array();
		$SQL_rapor_sevkiyata_ait_siparis_guzergahlar  = $SQL_rapor_sevkiyata_ait_siparis_guzergahlar . "  AND sip.id IN( '$siparis_idler' ) ORDER BY sip.id,sg.id";
		$sonuc = "<option value = '' >Seçiniz</option>";
		$siparis_guzergahlar	= $vt->select( $SQL_rapor_sevkiyata_ait_siparis_guzergahlar, array() );
		echo $SQL_rapor_sevkiyata_ait_siparis_guzergahlar;
		foreach( $siparis_guzergahlar[ 2 ] AS $satir ) {
			$secili = '';
			$guzergah		= $satir[ 'adi' ];
			$guzergah_id	= $satir[ 'id' ];
			$sonuc .= "<option value = '$guzergah_id' >$guzergah</option>";
		}
		echo $sonuc;
	break;
	case 'rapor_uretim_firmalara_ait_lotlar_ver':
		$firma_idler = array_key_exists( 'firma_idler', $_REQUEST ) ? join( "','", array_map( 'intval', $_REQUEST[ 'firma_idler' ] ) ) : array();
		if( count( $_REQUEST[ 'firma_idler' ] ) > 0 ) $SQL_rapor_uretim_firmaya_gore_latlari_ver .= " AND l.firma_id IN( '$firma_idler' ) ORDER BY l.firma_id, l.on_ek_sira";
		else $SQL_rapor_uretim_firmaya_gore_latlari_ver .= " ORDER BY l.firma_id, l.on_ek_sira";
		echo $SQL_rapor_uretim_firmaya_gore_latlari_ver;
		$sonuc = "<option value = '0' >Seçiniz</option>";
		$firmaya_ait_lotlar	= $vt->select( $SQL_rapor_uretim_firmaya_gore_latlari_ver, array() );
		foreach( $firmaya_ait_lotlar[ 2 ] AS $lot ) {
			$lot_adi		= $lot[ 'adi' ];
			$lot_id			= $lot[ 'id' ];
			$sonuc .= "<option value = '$lot_id' >$lot_adi</option>";
		}
		echo $sonuc;
	break;
	case 'bildirim_deneme':
		/*
		$sonuc1 = $_REQUEST[ 'b_sayisi' ]+1;
		if($sonuc1 > 99)
			$sonuc1 = $_REQUEST[ 'b_sayisi' ];
		*/
		$bildirimler	= $vt->select( $SQL_bildirim_getir, array( $_SESSION[ 'kullanici_id' ] ) );
		//$sonuc1 = $bildirimler[ 3 ];
		$bildirim_sayisi = 0;
		$bildirim_renk  = "";
		foreach( $bildirimler[ 2 ] AS $satir ) {
			if( $satir[ 'okundu' ] == 0 ){
				$bildirim_sayisi +=1;
				$bildirim_renk = "list-group-item-warning";
			}
			$sonuc2.='
				<li  class="'.$bildirim_renk.'">
					<a href="?modul='.$satir[ 'yonlendirilecek_modul' ].'&bil='.$satir[ 'id' ].'">
						<table>
							<tr>
								<td>
									<img src="resimler/'.$satir[ 'kullanici_resim' ].'" height="40" class="img-circle" > &nbsp;&nbsp;
								</td> 
								<td>
									<span id="bildirimler"><b>'.$satir[ 'kullanici_adi' ].'</b>, <font color="gray"><b>'.$satir[ 'siparis_kodu' ].'</b></font><br>kodlu yeni bir sipariş girdi.</span>
								</td>
							</tr>
						</table>
					</a>
				</li>		
			';
		}
		$sonuc = $bildirim_sayisi."~".$sonuc2;
		echo $sonuc;
	break;
	case 'siparis_guzergahlari_ver':
		$sonuc = "<option value = '' >Seçiniz</option>";
		$siparis_guzergahlar	= $vt->select( $SQL_siparis_guzergahlar, array( $_REQUEST[ 'siparis_id' ] ) );
		foreach( $siparis_guzergahlar[ 2 ] AS $satir ) {
			if( ( !in_array( $satir[ 'cikis_depo_id' ], $fn->superKontrolluRolYetkiliDepoVer( $_SESSION[ 'rol_id' ], true ) ) ) or $satir[ 'sevkiyat_tamamlandi' ] == 1 )
				continue;
			$secili = '';
			//foreach( $rol_modul_yetki_islem_turleri[ 2 ] AS $satir2 ) if( $satir[ 'id' ] == $satir2[ 'islem_turu_id' ] ) $secili = 'checked';
			$guzergah		= $satir[ 'guzergah' ];
			$guzergah_id	= $satir[ 'id' ];

			$sonuc .= "<option value = '$guzergah_id' >$guzergah</option>";
		}
		//$sonuc .= "</select>";
		echo $sonuc;
	break;

	case 'rol_modul_yetki_islem_oku':
		$sonuc = '';
		$yetki_islem_turleri_tumu		= $vt->select( $SQL_module_atanan_tum_yetki_islem_turleri, array( $_REQUEST[ 'modul_id' ] ) );
		$rol_modul_yetki_islem_turleri	= $vt->select( $SQL_rol_modul_yetki_islem_turleri, array( $_REQUEST[ 'modul_id' ],  $_REQUEST[ 'rol_id' ] ) );

		foreach( $yetki_islem_turleri_tumu[ 2 ] AS $satir ) {
			$secili = '';
			foreach( $rol_modul_yetki_islem_turleri[ 2 ] AS $satir2 ) if( $satir[ 'id' ] == $satir2[ 'islem_turu_id' ] ) $secili = 'checked';
			$adi		= $satir[ 'gorunen_adi' ];
			$id			= $satir[ 'id' ];
			$name		= $satir[ 'adi' ];

			$sonuc .= "
			<li class='list-group-item'>			
				<input id='$id' name='$id' type='checkbox'  $secili data-bootstrap-switch data-off-color='danger' data-on-color='success'/> $adi
				<script>$('input[data-bootstrap-switch]').each(function(){
				  $(this).bootstrapSwitch('state', $(this).prop('checked'));
				  });
				</script>
			</li>
			";
		}
		echo $sonuc;
	break;
	case 'rol_modul_yetki_islem_kaydet':
		$sonuc 					= array( 'hata'=> false, 'mesaj' => 'İşlem başarı ile bitti' );
		$islem_idler			= array();
		$modul_id				= array_key_exists( 'modul_id', $_REQUEST ) 		? $_REQUEST[ 'modul_id' ]		: 0;  
		$rol_id					= array_key_exists( 'rol_id', $_REQUEST ) 			? $_REQUEST[ 'rol_id' ]			: 0;  
		$yetki_islemler			= array_key_exists( 'yetki_islemler', $_REQUEST ) 	? $_REQUEST[ 'yetki_islemler' ]	: false;  
		/* Yetki Ataması yapıldıktan sonra combolar seçili kalsın */
		$_SESSION[ 'rol_id' ]			= $rol_id;
		$_SESSION[ 'modul_id' ]			= $modul_id;
		$_SESSION[ 'aktif_tab_id' ]		= 'rol_yetkileri';

		if( !$modul_id * $rol_id ) {
			$sonuc = array( 'hata'=> true, 'mesaj' => 'Parametreler eksik gönderildiğinden işlem iptal edildi' );
		} else {
			/* 1=on&2=on&3=on stringi'ni parçalayalım ve 1,2,3 gibi id değerlerini $islem_idler dizisinde saklayalım*/
			$vt->islemBaslat();
			$islemler = explode( '&', $yetki_islemler );
			for( $i = 1; $i < count( $islemler ); $i++ ) {
				$id = explode( '=', $islemler[ $i ] );
				$islem_idler[] = $id[ 0 ];
			}

			/* Önce varolan yetki işlemlerini sil */
			$sorgu_sonuc = $vt->delete( $SQL_rol_yetkileri_temizle, array( $rol_id, $modul_id ) );

			if( $sorgu_sonuc[ 0 ] ) {
				$sonuc = array( 'hata'=> true, 'mesaj' => $sorgu_sonuc[ 1 ] );
				break;
			} else {
				if( $yetki_islemler )
					for( $i = 0; $i < count( $islem_idler ); $i++ ) {
						$sorgu_sonuc = $vt->insert( $SQL_rol_modul_yetki_islem_turleri_kaydet, array( $rol_id, $modul_id, $islem_idler[ $i ] ) );
						if( $sorgu_sonuc[ 0 ] ) {
							$sonuc = array( 'hata'=> true, 'mesaj' => $sorgu_sonuc[ 1 ] );
							break;
						}
					}
			}
			$vt->islemBitir();
		}
		echo json_encode( $sonuc );
	break;
}
?>