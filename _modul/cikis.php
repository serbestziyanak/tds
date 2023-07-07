<?php
session_start();
session_destroy();

unset( $_COOKIE[ 'benihatirla' ] );
unset($_COOKIE[ 'kullanici_id' ]);
unset($_COOKIE[ 'firma_id' ]);
unset($_COOKIE[ 'firma_adi' ]);

setcookie("benihatirla", null, -1, '/');
setcookie("kullanici_id", null, -1, '/');
setcookie("firma_id", null, -1, '/');
setcookie("firma_adi", null, -1, '/');

header( 'Location: ../index.php' );

?>