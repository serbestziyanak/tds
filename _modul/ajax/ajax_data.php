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

$SQL_tutanak_sil = <<< SQL
DELETE FROM tb_tutanak
WHERE 
	firma_id 	= ? AND 
	id 			= ? 
SQL;

$vt = new VeriTabani();

switch( $_POST[ 'islem' ] ) {
	
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

	case 'personelListesiGetir':

		$sorgu	= array_key_exists( 'sorgu', $_REQUEST ) ?  $_REQUEST[ 'sorgu' ] : '';

		if( $sorgu == "mesaideOlan"){

			$personeller = $fn->mesaiCikmayan( date("Y-m-d") );

		}else if( $sorgu == "mesaiCikis"){

			$personeller = array();
			
		}else if( $sorgu == "izinli"){

			$personeller = $fn->izinliPersonel( date("Y-m-d") );

		}else if( $sorgu == "gelmeyen"){

			$personeller = $fn->gelmeyenler( date("Y-m-d") );
			
		}else if( $sorgu == "iseGiris"){
			
			$personeller = $fn->iseGiris( date("Y-m") );

		}else if( $sorgu == "istenCikis"){

			$personeller = $fn->istenCikis( date("Y-m") );
			
		}else if( $sorgu == "beyazYakali"){

			$personeller = $fn->beyazYakali();
			
		}else{
			die();
		}
		
		if( count( $personeller ) > 0 ){
			/*Personel listesini satırlara ekliyoruz*/
			$satir		= "";
			$say 		= 1;
			foreach ($personeller as $personel) {
				$satir .= "<tr>
							<td>$say</td>
							<td>$personel[adsoyad]</td>
							</tr>";
				$say++;
			}

			$satir = "<table class='table table-bordered table-hover table-sm dataTable no-footer dtr-inline' id='personelListesi'>
						<thead>
							<tr>
								<th style='width:30px;'>Sıra</th>
								<th>Personel Adı</th>
							</tr>
						<thead>
						<tbody>
							$satir
						</tbody>
					</table>";

		}else{
			$satir = "<div class='alert alert-danger text-center'> Veri Bulunamadı. </div>";
		}
			

		/*satırları Modal içierisindeki tabloya aktarıyoruz*/
		$sonuc = "<div class='modal fade'  id='liste'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<h4 class='modal-title'>Personel Listesi</h4>
							</div>
							<div class='modal-body'>
								<div class='table-responsive'>
									$satir
								</div>
							</div>
							<div class='modal-footer justify-content-between'>
								<button type='button' class='btn btn-default' data-dismiss='modal'>Kapat</button>
							</div>
						</div>
					</div>
				</div>";
		
		echo $sonuc;
	break;

	case "tutanakSil":
		
		if($_POST){
			$id = array_key_exists( 'id', $_REQUEST ) ?  $_REQUEST[ 'id' ] : '';
			
			$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "anasayfa", "tutanakSil" );
			if ( $yetiKontrol == 0 ) {
				echo "0";
				die;
			}else{
				$tutanakSilinenSayi = $vt->delete( $SQL_tutanak_sil, array( $_SESSION["firma_id"], $id ) )[2];
				echo $tutanakSilinenSayi > 0 ? "1" : "0";
			}
		}else{
			echo "0";
		}

	break;

	

}
?>