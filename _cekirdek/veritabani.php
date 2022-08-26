<?php

class VeriTabani {
	private $vt;
	private $hataLocal;
	private $hataTopluIslem;

	/* Veritabanına bağlan */
	public function __construct() {
		$this->vt				= NULL;
		$this->hata				= false;
		$this->hataTopluIslem	= false;

		try { 
            if( $_SERVER['SERVER_NAME'] == "localhost" )
				
                $this->vt = new PDO( "mysql:host=localhost; dbname=eyps", "root", "" );

            else
                $this->vt = new PDO( "mysql:host=localhost; dbname=syntaxbi_tds", "syntaxbi_tds_usr", "6vH@+S9C" );
        } catch ( PDOException $e ) {
			echo "Veritabanı bağlantısı sağlanamadı";
			exit;
		}
		$this->vt->query( "SET CHARACTER SET utf8" );
		date_default_timezone_set( 'Europe/Istanbul' );
	}
	
	public function __destruct() {
		$this->vt = NULL;
	}

	/* Tüm kayıtları oku */
	public function select( $sql, $param = array() ) {
		$vt			= $this->vt;
		$sorguHazir	= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$sonuc		= $sorguHazir->fetchAll( PDO::FETCH_ASSOC );
		$hataDizi	= $sorguHazir->errorInfo();
		$hataMesaj	= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"', $sonuc, count( $sonuc ) );
	}

	/* Tek kayıt oku */
	public function selectSingle( $sql, $param = array() ) {
		$vt			= $this->vt;
		$sorguHazir	= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$sonuc		= $sorguHazir->fetch( PDO::FETCH_ASSOC );
		$hataDizi	= $sorguHazir->errorInfo();
		$hataMesaj	= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"', $sonuc );
	}

	/* Kayıt ekleme */
	public function insert( $sql, $param = array() ) {
		$vt			= $this->vt;
		$sorguHazir	= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$hataDizi	= $sorguHazir->errorInfo();
		$hataMesaj	= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"', $vt->lastInsertId() );
	}

	/* İstenilen parametreye göre kayıt günceller */
	public function update( $sql, $param = array() ) {
		$vt			= $this->vt;
		$sorguHazir	= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$hataDizi	= $sorguHazir->errorInfo();
		$hataMesaj	= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"' );
	}

	/* İstenilen id'ye ait kaydı siler */
	public function delete( $sql, $param = array() ) {
		$vt					= $this->vt;
		$sorguHazir			= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$silinenKayitSayisi = $sorguHazir->rowCount();
		$hataDizi			= $sorguHazir->errorInfo();
		$hataMesaj			= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"', $silinenKayitSayisi );
	}

	/* count(*) kullnarak kayıt sayısını verir. */
	public function rowCount( $sql, $param = array() ) {
		$vt			= $this->vt;
		$sorguHazir	= $vt->prepare( $sql );
		$sorguHazir->execute( $param );
		$sonuc		= $sorguHazir->fetchColumn();
		$hataDizi	= $sorguHazir->errorInfo();
		$hataMesaj	= str_replace( "'", "\'", $hataDizi[ 2 ] );
		if( $hataDizi[ 0 ] != "00000" ) {
			$this->hataLocal		= true;
			$this->hataTopluIslem	= true;
		}
		return array( $this->hataLocal, '"'. $hataMesaj . '"', $sonuc );
	}

	/* Toplu işlme başlat */
	public function islemBaslat() {
		$vt = $this->vt;
		$vt->beginTransaction();
	}

	/* Hata varsa yapılan değişiklikleri geri sar */
	public function islemBitir() {
		$vt = $this->vt;
		if( $this->hataTopluIslem ) $vt->rollBack();
		else $vt->commit();
	}
}
