<?php
require_once( 'fonksiyonlar.php' );
require_once( 'min-js.php' );

/*
*	Giriş yapan bir kullanıcı varsa onun yetkisini çek yoksa ziyaretçi yetkisini çek.
*/
$fn			= new Fonksiyonlar();
$minicik	= new JSqueeze();

echo $minicik->squeeze( "
( function() {
	var yetki	= {};
	Obj	= function() {
		yetki = " . json_encode( $fn->tumYetkileriVer( array_key_exists( 'kullanici_id', $_SESSION ) ? $_SESSION[ 'kullanici_id' ] : -1 ) ) . ";
	};

	Obj.prototype.yetkiVer = function( modul, islem ) {
		if( !yetki ) return 0; /* Eğer yetki objesi boş ise yani kullanıcıya henuz bir yetki atanmamış ise, modul etiketine sahip tüm nesneleri gizle */
		if( !modul || !islem ) return 1;
		if( __F.hesapBilgileri().supermi ) return 1; /* Eğer süper user ise dön */
		if( yetki[ modul ][ islem ] === islem ) return 1;
		return 0;
	};
	/* Yetkilendirilecek nesnenin modul ve yetki_islem özellikleri olmalıdır. */
	Obj.prototype.nesneYetkilendir = function( nesne ) {
		var nesneler = $( '*[modul]' );
		for( var i = 0; i < nesneler.length; i++ ) {

			var nesne = $( nesneler[ i ] );
			nesne				= $( nesne );
			var modul			= nesne.attr( 'modul' ) || '';
			var yetki_islem		= nesne.attr( 'yetki_islem' ) || '';
			if ( !this.yetkiVer( modul, yetki_islem ) ) {
				nesne.remove();
			}
		}
	};

	Obj.prototype.hesapBilgileri	= function() { return  yetki.hesapBilgileri || {}	};
	Obj.prototype.superKullanici	= function() { return  yetki.hesapBilgileri.supermi || {};	};
	Obj.prototype.tumYetkiler		= function() { return  yetki || {};	};
} )();
__F	= new Obj() || {};
" );