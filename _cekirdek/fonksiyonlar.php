<?php
ob_start();
 if ( session_status() == PHP_SESSION_NONE ) {
	session_start();
}
include 'veritabani.php';
/*
	Session tanımlaması yapıyoruz. Çünkü henüz giriş yapmadığı zaman session tanmsız oluyor. 
	Biz de tanımlayıp default değerini false yapıyoruz. 
*/

$_SESSION[ 'giris' ] = array_key_exists( 'giris', $_SESSION ) ? $_SESSION[ 'giris' ] : false;

class Fonksiyonlar {
	private $vt;

	const SQL_rol = <<< SQL
SELECT
	 r.id
	,r.adi
	,GROUP_CONCAT( gr.gorulecek_rol_id SEPARATOR ',' ) AS gorulecek_rol_id
	,r.varsayilan
FROM
	tb_roller AS r
LEFT JOIN
	tb_gorulecek_roller AS gr ON r.id = gr.rol_id
GROUP BY
	r.id
ORDER BY
	r.id DESC
SQL;

	const SQL_super = <<< SQL
SELECT
	super
FROM
	tb_sistem_kullanici
WHERE
	id = ?
SQL;

	const SQL_yetki = <<< SQL
SELECT
	 m.id AS menuID
	,m.adi AS sinif_adi
	,m.modul
	,ku.email AS kullanici_adi
	,ku.adi
	,ku.soyadi
	,ku.super
	,ku.rol_id
	,ku.resim
	,r.adi AS rol_adi
	,( SELECT GROUP_CONCAT( birim_id SEPARATOR ',' )	FROM tb_sistem_kullanici_yetkili_birimler WHERE kullanici_id = ku.id )	AS yetki_birim_id
	,( SELECT GROUP_CONCAT( adi SEPARATOR ',' )			FROM tb_yetki_islem_turleri WHERE id = ANY( SELECT yetki_islem_turu_id FROM tb_yetki where kullanici_id = ku.id  AND modul_id = m.id ) )	AS yetki_islemler
	,( SELECT GROUP_CONCAT( adi SEPARATOR ',' )			FROM tb_yetki_islem_turleri WHERE id = ANY( SELECT islem_turu_id FROM tb_rol_yetkiler WHERE rol_id = ku.rol_id AND modul_id = m.id ) )		AS rol_islemler
FROM
	tb_modul AS m
LEFT JOIN
	tb_sistem_kullanici AS ku ON ku.id = ?
JOIN
	tb_roller AS r ON ku.rol_id = r.id
SQL;

/* Yetki Modülü içinde kullanılan sube ve firmalar için yetki kontrolü.*/
	const SQL_yetkili_subeler_yetki_modulu = <<<SQL
SELECT
	*
FROM
	tb_subeler
WHERE
	id = ANY( SELECT sube_id FROM tb_rol_yetkili_subeler WHERE rol_id = ? )
SQL;

	const SQL_yetkili_firmalar_yetki_modulu = <<<SQL
SELECT
	*
FROM
	tb_firmalar
WHERE
	id = ANY( SELECT firma_id FROM tb_rol_yetkili_firmalar WHERE rol_id = ? )
SQL;
/**/

/* Normal Modüller içinde kullanılan sube ve firmalar için yetki kontrolü.*/
	const SQL_yetkili_subeler = <<<SQL
SELECT
	*
FROM
	tb_subeler
WHERE
	id = ANY( SELECT sube_id FROM tb_rol_yetkili_subeler WHERE rol_id = ? ) or ?
SQL;

	const SQL_yetkili_firmalar = <<<SQL
SELECT
	*
FROM
	tb_firmalar
WHERE
	id = ANY( SELECT firma_id FROM tb_rol_yetkili_firmalar WHERE rol_id = ? ) or ?
SQL;
/* */

	const SQL_firmalar = <<<SQL
SELECT
	*
FROM
	tb_firmalar
SQL;


	const SQL_module_atanan_yetki_islem_turleri = <<<SQL
SELECT
	yetki_islem_id AS id
FROM
	tb_modul_yetki_islemler
WHERE
	modul_id = ?
SQL;


	/* Kurucu metod  */
	public function __construct() {
		$this->vt = new VeriTabani();
	}

	/* Kullanıcı süper değilse rolünün görebileceği roller dizisi döner. */
	public function rolVer() {
		$sonuc				= $this->vt->select( self::SQL_rol );
		$kayitlar			= array();
		$gorulecekRoller	= array();
		
		/* hata varsa boş dizi dön */
		if( $sonuc[ 0 ] ) return array();
		$sonuc = $sonuc[ 2 ];

		for( $i = 0; $i < count( $sonuc ); $i++ )
			if( $sonuc[ $i ][ 'id' ] == $_SESSION[ 'rol_id' ] ) {
				$gorulecekRoller = array_map( 'intval', explode( ',', $sonuc[ $i ][ 'gorulecek_rol_id' ] ) );
			}

		for( $i = 0; $i < count( $sonuc ); $i++ ) {
			if( !in_array( $sonuc[ $i ][ 'id' ], $gorulecekRoller ) && !$this->superKullanici() ) continue;

			$kayit[ 'id' ]					= $sonuc[ $i ][ 'id' ] * 1;
			$kayit[ 'adi' ]					= $sonuc[ $i ][ 'adi' ];
			$kayit[ 'gorulecek_rol_id' ]	= $sonuc[ $i ][ 'gorulecek_rol_id' ] ? array_map( 'intval', explode( ',', $sonuc[ $i ][ 'gorulecek_rol_id' ] ) ) : array();
			$kayit[ 'varsayilan' ]			= $sonuc[ $i ][ 'varsayilan' ] * 1;
			$kayitlar[]						= $kayit;
		}
		return $kayitlar;
	}

	/*
	*
	*	GÜVENLİK FONKSİYONLARI
	*
	*/

	/* Süper kullanıcı olup olmadığını kontrol ediyor. */
	public function superKullanici( $kul_id = 0 ) {
		if( !$kul_id ) $kul_id = $_SESSION[ 'kullanici_id' ];
		$sonuc = $this->vt->selectSingle( self::SQL_super, array( $kul_id ) );
		return  $sonuc[ 2 ][ 'super' ] * 1;
	}

	/*
	*	Kullanıcının sahip olduğu roldeki "tb_rol_yetkiler" ve "tb_yetki" tablosundaki yetkileri birleştiriliyor.
	*	Kümelerdeki birleşim kuralı geçerlidir. Yani aynı eleman tekrarlanmaz. Örneğin hem "tb_rol_yetkiler" tablosunda hem de "tb_yetki" tablosunda "ekle" işlemi olursa birtane "ekle" işlemi alınıyor.
	*	Eğer yetkiIslem = A ve rolIslem B adında bir kümeyi ifade ederse, rolVeModulYetkileriBirlestir = ( A / B )U( B ) olur.
	*/
	public function rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDeger = true ) {
		$yeniDizi	= array();
		/* $yetkiIslem'de olup $rolIslem'de olmayan yetki işlemlerini $rolIslem'e ekle */
		$dizi		= count( $yetkiIslem ) ? array_merge( array_diff( $yetkiIslem, $rolIslem ), $rolIslem ) : $rolIslem;
		/** Eğer isteniyorsa Dizinin anahtar ve değerleri aynı olsun. */
		for( $i = 0; $i < count( $dizi ); $i++ ) $yeniDizi[ $dizi[ $i ] ] = $dizi[ $i ];
		/* Eğer dizinin anahtar ve değrlerinin aynı olması İSTENMİYORSA rolVeModulYetkileriBirlestir fonksiyonu  $anahtarDeger = false parametresi ile çağırılmalıdır. */
		return $anahtarDeger ? $yeniDizi : $dizi;
	}

	/*
	*	"kullanici_id" si bilinen bir kullanıcının her bir modüle ait tüm yetkilerini dizi şeklinde verir. 
	*
	*	Sisteme yeni bir modul eklendiğinde bu modul için rol yetkileri de, yani "tb_rol_yetkiler" tablosuna eklenmelidir. 
	*	Tüm rollerin tüm moduller için yetkileri "tb_rol_yetkiler" tablosunda bulunmak zorundadır. Bütün rollerin varsayılan yetkileri listele'dir.
	*	Ayrıca sistemde kayıtlı bir varsayılan rol olmak zorundadır. Çünkü eklenecek yeni kullanıcıların varsayılan olarak bir rolun atanması lazım. 
	*	Yani sistemde rolu olmayan bir kullanıcı olamaz.
	*/

	public function tumYetkileriVer( $id ) {
		if( !$id ) return array();
		$sonuc				= $this->vt->select( self::SQL_yetki,  array( $id ) );
		$yetkiler			= array();
		$kullaniciAdi 		= '';
		$ad 				= '';
		$soyad 				= '';
		$rolAdi 			= '';
		$super				= 0;
		$rol_id				= 0;
		$yetkiliBirimler	= array();
		
		foreach( $sonuc[ 2 ] AS $satir ) {
			$yetkiIslem		= $satir[ "yetki_islemler" ];
			$rolIslem		= $satir[ "rol_islemler" ];

			/* "tb_yetki" tablosunda kullanıcının herhangi bir modül için sahip olduğu yetki işlemleri ( ekle,sil,guncelle...) */
			$yetkiIslem		= $yetkiIslem ? explode( ",", $yetkiIslem ) : array();

			/* "tb_rol_yetkiler" tablosunda bir kullanıcının rolünün sahip olduğu yetki işlemleri ( ekle,sil,guncelle...) */
			$rolIslem		= $rolIslem ? explode( ",", $rolIslem ) : array();

			$kullaniciAdi		= $satir[ "kullanici_adi"	];
			$ad 				= $satir[ "adi" 			];
			$soyad 				= $satir[ "soyadi"			];
			$rol_id 			= $satir[ "rol_id"			];
			$rolAdi 			= $satir[ "rol_adi"			];
			$super 				= $satir[ "super"			] * 1;
			$resim 				= $satir[ "resim"			];
			$yetkiliBirimler	= $satir[ 'yetki_birim_id' ];
			$yetkiliBirimler	= explode( ',', $yetkiliBirimler );
			$yetkiler[ $satir[ "modul" ] ] = $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem );
		}

		$hesapBilgileri = array(
			 'ad'				=> $ad
			,'soyad'			=> $soyad
			,'kullaniciAdi'		=> $kullaniciAdi
			,'rolAdi'			=> $rolAdi
			,'rol_id'			=> $rol_id * 1
			,'supermi'			=> $super
			,'giris'			=> $_SESSION[ 'giris' ] ? true : false
			,'yetkiliBirimler'	=> array_map( 'intval', $yetkiliBirimler )
		);

		$_SESSION[ 'ad_soyad' ]			= $ad . ' ' . $this->tumuBuyukHarf( $soyad );
		$_SESSION[ 'rol_adi' ]			= $super ? 'Süper' : $rolAdi;
		$_SESSION[ 'rol_id' ]			= $rol_id;
		$_SESSION[ 'kullanici_resim' ]	= $resim;
		$yetkiler[ 'hesapBilgileri' ]	= $hesapBilgileri;
		return $yetkiler;
	}
	
	/* İstenilen kullanıcının tüm modülleri varsa yetki işlemleri ile birlikte verir. */
	public function modulYetkileriVer( $id, $anahtarDegerAyni = false ) {
		if( !$id ) return array();
		$sonuc					= $this->vt->select( self::SQL_yetki,  array( $id ) );

		$modulDizisiIndexli		= array();
		$modulDizisiAnahtarli	= array();

		foreach( $sonuc[ 2 ] AS $satir ) {
			$yetkiIslem		= $satir[ "yetki_islemler" ];
			$rolIslem		= $satir[ "rol_islemler" ];

			/* "tb_yetki" tablosunda kullanıcının herhangi bir modül için sahip olduğu yetki işlemlerini verir ( ekle,sil,guncelle...) */
			$yetkiIslem		= $yetkiIslem ? explode( ",", $yetkiIslem ) : array();

			/* "tb_rol_yetkiler" tablosunda bir kullanıcının rolünün sahip olduğu yetki işlemlerini verir ( ekle,sil,guncelle...) */
			$rolIslem = $rolIslem ? explode( ",", $rolIslem ) : array();
			$modulDizisiIndexli[ $satir[ 'menuID' ] ]	= $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDegerAyni );
			$modulDizisiAnahtarli[ $satir[ "modul" ] ]	= $this->rolVeModulYetkileriBirlestir( $yetkiIslem, $rolIslem, $anahtarDegerAyni );
		}
		return $anahtarDegerAyni ? $modulDizisiAnahtarli : $modulDizisiIndexli;
	}

	public function yetkiKontrol( $kullanici_id = 0, $modul = '', $islem = '' ) {
		/* id, modül veya sorgulanacak yetki işlemi boş gelirse veya giriş yapılmamışsa dön. */
		if( !$kullanici_id || !$modul || !$islem ) return 0;
		/* Eğer kullanıcı süper yetkili ise, yetki kontrolünü yapma ve dön. Bu durumda kullanıcı tam yetkili olur. */
		if( $this->superKullanici( $kullanici_id ) ) return 1;
		/** Sorgulanan kullanıcının tüm yetkileri al. */
		$yetkiler = $this->modulYetkileriVer( $kullanici_id, true );
		if( !count( $yetkiler ) ) return 0;
		/** istenilen modulde istenilen yetki varmı bak */
		//$modul = 'mdl_' . $modul;
		foreach( $yetkiler as $modulAnahtar => $islemler ) foreach( $islemler as $isl ) if( $modulAnahtar == $modul && $isl == $islem ) return 1;
		return 0;
	}
	
	/* Herhangi bir rol_id nin yetkili olduğu subelerı verir.*/
	public function superKontrolluRolYetkilisubeVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_subeler,  array( $rol_id, $_SESSION[ 'super' ] ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function superKontrolluRolYetkiliFirmaVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_firmalar,  array( $rol_id, $_SESSION[ 'super' ] ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}
	
	
//////////////////////
	/* Herhangi bir rol_id nin yetkili olduğu subelerı verir.*/
	public function yetkilisubeVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_subeler_yetki_modulu,  array( $rol_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}


	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function yetkiliFirmaVer( $rol_id, $sadece_idler = false ) {
		$sonuclar = $this->vt->select( self::SQL_yetkili_firmalar_yetki_modulu,  array( $rol_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Herhangi bir rol_id nin yetkili olduğu firmaları verir.*/
	public function yetkiliIslemTurleriVer( $modul_id ) {
		$sonuclar = $this->vt->select( self::SQL_module_atanan_yetki_islem_turleri,  array( $modul_id ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] =  $sonuc[ 'id' ];
		return $dizi;
	}

	/*
	*	Firma'nın türüne göre firma listesini verir.
	*	@firma_turu : uretici_firma, tasiyici_firma, alici_firma, satici_firma, analiz_firmasi olabilir
	*	@sadece_idler : true, false olabilir.
	*/
	public function firmaVer( $firma_turu = 1, $sadece_idler = false ) {
		$SQL = self::SQL_firmalar;
		$SQL = $SQL . ' WHERE ' . $firma_turu . ' = ?';
		$sonuclar = $this->vt->select( $SQL,  array( 1 ) );
		$dizi = array();
		foreach( $sonuclar[ 2 ] as $sonuc ) $dizi[] = $sadece_idler ? $sonuc[ 'id' ] : $sonuc;
		return $dizi;
	}

	/* Cihaz algılama fonksiyonu */
	public function mobilCihaz() {
		$useragent = $_SERVER[ 'HTTP_USER_AGENT' ];
		if( preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4) ) )
			return true;
		return false;
	}
	
	/**
	* @$file_input_adi file input nesnesine verdiğiniz isim (name)
	* @dosya_adi upload edilecek olan dosya vermek istediğiniz isim
	*/
	public function resimYukle( $file_input_adi, $dosya_adi ) {
		if( isset( $_FILES[ $file_input_adi ] ) ) {
			$errors		= array();
			$file_name	= $_FILES[ $file_input_adi ]['name'];
			$file_size	= $_FILES[ $file_input_adi ]['size'];
			$file_tmp	= $_FILES[ $file_input_adi ]['tmp_name'];
			$file_type	= $_FILES[ $file_input_adi ]['type'];
			$file_ext	= strtolower( end( explode( '.', $_FILES[ $file_input_adi ][ 'name' ] ) ) );
			$expensions	= array( "jpeg", "jpg", "png", 'JPEG', 'JPG', 'PNG' );
			if( in_array( $file_ext, $expensions ) === false ) {
				$errors[] = "Lütfen sadece jpg veya png uzantılı fotoğtaf yükleyiniz. ";
			}
			if( $file_size > 2012345 ) {
				$errors[] = 'Dosya boyutu en fazla 2MB olabilir';
			}
			if( empty( $errors ) == true ) {
				if( move_uploaded_file( $file_tmp, "../../resimler/" . $dosya_adi . "." . $file_ext ) ) {
					return array( true, $dosya_adi . "." . $file_ext );
				} else {
					return array( false, false );
				}
			} else {
				return array( false, false );
			}
		} else {
			return array( false, false );
		}
	}

	/*
	*
	*	METİN FONKSİYONLARI
	*
	*/	

	/*( 1.234,67 ) şeklinde sayı formatı verir.*/
	public function sayiFormatiVer( $sayi ) {
		if( gettype( $sayi ) == 'double' )
			return number_format( $sayi, 2, ',', '.');
		else
			return number_format( $sayi, 0, ',', '.');
	}
	
	/* tüm harfleri _BÜYÜK_ harfe çevir */
	public function tumuBuyukHarf( $metin ) {
		$b = array( 'Ç', 'Ğ', 'İ', 'I', 'Ö', 'Ş', 'Ü' );
		$k = array( 'ç', 'ğ', 'i', 'ı', 'ö', 'ş', 'ü' );
		return mb_strtoupper( str_replace( $k, $b, $metin ), 'utf-8' );
	}
	
	/* tüm harfleri _küçük_ harfe çevir */
	public function tumuKucukHarf( $metin ) {
		$b = array( 'Ç', 'Ğ', 'İ', 'I', 'Ö', 'Ş', 'Ü' );
		$k = array( 'ç', 'ğ', 'i', 'ı', 'ö', 'ş', 'ü' );
		return mb_strtolower( str_replace( $b, $k, $metin ), 'utf-8' );
	}
	
	/* Her kelimenin ilk harfini büyük diğerlerini küçük yazar. */
	public function ilkHarfleriBuyut( $metin ) {
		if( !$metin ) return '';		
		$metin = str_replace( "I", "ı", $metin );
		$ilkHarf = mb_substr($metin, 0, 1, $encoding);
		if( $ilkHarf == "i" )
			$metin = str_replace( "i", "İ", $metin );
		if( $ilkHarf == "ı" )
			$metin = str_replace( "ı", "I", $metin );
		return mb_convert_case( $metin, MB_CASE_TITLE, "UTF-8" );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihi 22-01-2015 şeklinde saat olmadan verir */
	public function tarihFormatiDuzelt( $tarih ) {
		if( strlen( $tarih ) < 1 ) return '00-00-0000';
		$t 	= explode( ' ', $tarih );
		$t	= explode( '-', $t[ 0 ] );
		return $t[ 2 ] . '.' . $t[ 1 ] . '.' . $t[ 0 ];
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 00:00 şeklinde verir */
	public function saatFormatiDuzelt( $tarih ) {
		if( strlen( $tarih ) < 1 ) return '00:00';
		$t 	= explode( ' ', $tarih );
		$s	= explode( ':', $t[ 1 ] );
		return $s[ 0 ] . ':' . $s[ 1 ];
	}

	/* 1986-04-23 09:32:52 formatındaki tarihi 22-01-2015 şeklinde saat olmadan verir */
	public function tarihVer( $tarih ) {
		if( $tarih == "" or $tarih == null )
			return "";
		else
			return date('d.m.Y', strtotime( $tarih ) );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 09:32 şeklinde verir */
	public function saatDakikaVer( $tarih ) {
		return date('H:i', strtotime( $tarih ) );
	}

	/* 1986-04-23 09:32:52 formatındaki tarihin saat kısmını 09:32:52 şeklinde verir */
	public function saatDakikaSaniyeVer( $tarih ) {
		return date('H:i:s', strtotime( $tarih ) );
	}

	/* 1986-04-23 23:32:52 formatındaki tarihi 23-04-1986 23:32:52 şeklinde verir */
	public function tarihSaatVer( $tarih ) {
		return date('d-m-Y H:i:s', strtotime( $tarih ) );
	}

	public function uzantiVer( $str ) {
		if( !strlen( $str ) ) return $str;
		$tersMetin		= strrev( $str );
		$dizi			= explode( '.', $tersMetin );
		return strrev( $dizi[ 0 ] );
	}
	/* Bir metindeki boşlukları '_' karakteri ile ve türkçe karakterleri de inglizce karakterleri ile değiştirir. */
	public function turkceKarakterSil( $s ) {
		$s	= str_replace( ' ', "_", $s );
		$tr	= array( 'ç', 'Ç', 'ı', 'İ', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü', 'ğ', 'Ğ' );
		$en	= array( 'c', 'C', 'i', 'I', 'o', 'O', 's', 'S', 'u', 'U', 'g', 'G' );
		return str_replace( $tr, $en, $s );
	}
	/* Rakam olarak verilne ayın adını ver*/
	public function ayAdiVer( $kacinci_ay, $ad_uzunlugu = 0 ) {
		$aylar = array(
			 1 =>	array( 'Ocak'		,'Oc.'		)
			,2 =>	array( 'Şubat'		,'Şub.'		)
			,3 =>	array( 'Mart'		,'Mar.'		)
			,4 =>	array( 'Nisan'		,'Nis.'		)
			,5 =>	array( 'Mayıs'		,'May.'		)
			,6 =>	array( 'Haziran'	,'Haz.'		)
			,7 =>	array( 'Temmuz'		,'Tem.'		)
			,8 =>	array( 'Ağustos'	,'Ağus.'	)
			,9 =>	array( 'Eylül'		,'Eyl.'		)
			,10 =>	array( 'Ekim'		,'Ek.'		)
			,11 =>	array( 'Kasım'		,'Kas.'		)
			,12 =>	array( 'Aralık'		,'Ara.'		)
		);
		return $aylar[ $kacinci_ay ][ $ad_uzunlugu ];
	}

	//2022-04-30 formatındaki tarihe ait günü veriyor
	public function gunVer($gelenTarih,$locale='tr'){
	    $gelentarih=explode ("-",$gelenTarih);
	    //							     AY             Gün              YIL
	    $gun = date("l",mktime(0,0,0,$gelentarih[1],$gelentarih[2],$gelentarih[0])); 
	    if ($locale == 'tr') {
	        switch ($gun) {
	            case 'Monday':
	                    return 'Pazartesi';
	                break;

	            case 'Tuesday':
	                    return 'Salı';
	                break;

	            case 'Wednesday':
	                    return 'Çarşamba';
	                break;

	            case 'Thursday':
	                    return 'Perşembe';
	                break;

	            case 'Friday':
	                    return 'Cuma';
	                break;

	            case 'Saturday':
	                    return 'Cumartesi';
	                break;
	            
	            default:
	                    return 'Pazar';
	                break;
	        }
	    }else{
	        return $gun;
	    }
	}

	//Tek Haneli Sayi Veriyor
	public function ikiHaneliVer($sayi){
		return strlen($sayi) == 2 ? $sayi : '0'.$sayi;
	}

	//Giriş Çıkış tablosunda kayırlı olan sat ve güncellenen saatin karsılaştırmasını yapıyoruz Eger guncelenen bir saat varsa onu ele alacağız yoksa kaytıtlı saati alacagız 
	public function saatKarsilastir($kayitliSaat,$guncellenenSaat){
		if ($guncellenenSaat == "" or $guncellenenSaat == "00:00:00") {
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($kayitliSaat));
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($kayitliSaat));
			
		}else{
			$saat[] = $kayitliSaat == '' ? ' - ' : date("H:i",strtotime($guncellenenSaat));
			$saat[] = $kayitliSaat == '' ? ' - ' : '<b class="text-danger">'.date("H:i",strtotime($kayitliSaat)).'</b>';
		}
		return $saat;
	}

	public function islemTipi($islemtipi,$personel_id,$tarih){
		$sonuc  = "";
		$baslik = "Tutanak Oluştur";
		if (!array_key_exists( "gelmedi", $islemtipi)) {
			if (count($islemtipi) == 0) {
				$baslik  = 	'Mesaide';
			}else if ( array_key_exists( "gecgelme", $islemtipi ) or array_key_exists( "erkencikma", $islemtipi ) ){
				$sonuc = array_key_exists( "gecgelme", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gecgelme&saat='.$islemtipi["gecgelme"].'" class="btn btn-outline-info btn-xs col-sm-12" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn" >Geç Gelme Yazdır </a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gecgelme&saat='.$islemtipi["gecgelme"].'" target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Geç Gelme Word İndir</a>' : '';

				//Personel erken çıkmış
				$sonuc .= array_key_exists( "erkencikma", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=erkencikma&saat='.$islemtipi["erkencikma"].'" class="btn btn-outline-primary btn-xs col-sm-12 mt-1" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn">Erken Çıkma Yazdır</a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=erkencikma&saat='.$islemtipi["erkencikma"].'"  target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Erken Çıkma Word İndir</a>' : '';
			}else{
				$sonuc = '<b class="text-center text-warning">'.implode( ", ", $islemtipi).'</b>';
			}
		}else{
			//Personel hiç giriş yapmamış ise 
			$sonuc =  array_key_exists( "gelmedi", $islemtipi ) ? '<a target="_blank" href="?modul=tutanakolustur&personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gunluk" class="btn btn-danger btn-xs col-sm-12" data-id="'.$personel_id.'" id="GelememeTutanakOlusturBtn">Tutanak Tut</a> <a href="_modul/wordolustur/wordolustur.php?personel_id='.$personel_id.'&tarih='.$tarih.'&tip=gunluk"  target="_blank" class="btn btn-xs col-sm-12 btn-dark mt-1">Word İndir</a>' : '<b class="text-center text-warning">'.implode( ", ", $islemtipi ).'</b>';
		}
		if ( $baslik == 'Mesaide' ){
			$sonuc  = '<b class="text-success">Mesaide</b>';
		}else{
			//dropdown menu oluşturma
			$sonuc = '
			<div class="btn-group">
	          <button type="button" class="btn btn-xs btn-default" data-toggle="dropdown" aria-expanded="false">'.$baslik.'</button>
	          
	          </button>
	          <div class="dropdown-menu" role="menu" style="min-width:180px; padding:10px;">
	            '.$sonuc.'
	          </div>
	        </div>';
		}
			

		return $sonuc;
	}

	public function islem_tipi_isim( $islem ) {
		
		switch ($islem) {
			case 'gunluk':
				$sonuc = 'Gelmeme';
				break;

			case 'gecgelme':
				$sonuc = 'Geç Gelme';
				break;
			
			case 'erkencikma':
				$sonuc = 'Geç Gelme';
				break;
		}

		return $sonuc;
	}

	public function saatfarkiver( $baslangic, $bitis ) {
		//baslangicSaati => o zamana kadar geçen saniyesini buluyoruz.
		$baslangicSaati = strtotime($baslangic);
		
		//bitisSaati => o zamana kadar geçen saniyesini buluyoruz.
		$bitisSaati = strtotime($bitis);
		
		//Aradaki saniye farkını bulduk.
		$fark = $bitisSaati - $baslangicSaati;

		$dakika = floor($fark / 60);
		return $dakika;
	}
}
