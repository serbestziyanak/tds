function mesajVer( mesaj, tur ) {
	mesaj	= mesaj || '';
	switch( tur ) {
		case 'yesil':
			toastr.success(mesaj);
		break;
		case 'mavi':
			toastr.info(mesaj);
		break;
		case 'turuncu':
			toastr.warning(mesaj);
		break;
		case 'kirmizi':
			toastr.error(mesaj);
		break;
	}

}