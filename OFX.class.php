<?php
/**
 *  Descrição: Classe para consumir arquivos do tipo OFX
 *	Autor: Thiago Gham
 *	Data: 10/04/2018
 */
class OFX {
	private $StringOFX;
	public $cd_banco;
	public $cd_agencia;
	public $cd_ctacor;
	public $dt_saldo;
	public $vl_saldo;
	public $movimentos; 

	/**
	 * [__construct description]
	 */
	public function __construct($OFXFile = ''){
		if(file_exists($OFXFile)){
			$this->StringOFX 	= file_get_contents($OFXFile);
			if(!empty($this->StringOFX)){
				$this->validaOFX();
				$this->loadFromString();
				$this->StringOFX = '';
				return true;	
			}
		}
		return false;
	}
	/**
	 * 
	 */
	public function setStringOFX( $text = ''){
		$this->StringOFX = $text;
		if(!empty($this->StringOFX)){
			$this->validaOFX();
			$this->loadFromString();
			$this->StringOFX = '';
			return true;	
		}
		return false;
	}
	/**
	 * 
	 */
	private function fixTag($tag, $acceptSpaces= false){
		$tag = strtoupper($tag);
		$this->StringOFX = preg_replace('/<'.$tag.'>([\w0-9\.\-_\+\,'.(($acceptSpaces)? ' ': '').'])+/i', '$0</'.$tag.'>', $this->StringOFX);
	}
	/**
	 * 
	 */
	private function resolve(){
		$this->fixTag('NAME');
		$this->fixTag('CODE');
		$this->fixTag('STATUS');
		$this->fixTag('SEVERITY');
		$this->fixTag('CURDEF');
		$this->fixTag('DTSERVER');
		$this->fixTag('LANGUAGE');
		$this->fixTag('TRNUID');
		$this->fixTag('BANKID');
		$this->fixTag('ACCTID');
		$this->fixTag('ACCTTYPE');
		$this->fixTag('DTSTART');
		$this->fixTag('DTEND');
		$this->fixTag('TRNTYPE');
		$this->fixTag('DTPOSTED');
		$this->fixTag('TRNAMT');
		$this->fixTag('FITID');
		$this->fixTag('CHECKNUM');
		$this->fixTag('DTASOF');
		$this->fixTag('LEDGERBAL');
		$this->fixTag('BALAMT');
		$this->fixTag('LEDGERBAL');
		$this->fixTag('MEMO', true);
	}
	/**
	 * 
	 */
	private function loadFromString(){

		$DADOS_OFX = simplexml_load_string("<?xml version='1.0'?> ".$this->StringOFX);
		
		if(empty($DADOS_OFX)){
			$this->resolve();
			$DADOS_OFX = simplexml_load_string("<?xml version='1.0'?> ".$this->StringOFX);
		}

		$this->cd_banco  = (string)$DADOS_OFX->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->BANKID;
		$this->cd_ctacor = (string)$DADOS_OFX->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->ACCTID;
		$this->cd_agencia = 0;
		$this->vl_saldo  = (string)$DADOS_OFX->BANKMSGSRSV1->STMTTRNRS->STMTRS->LEDGERBAL->BALAMT;
		$this->dt_saldo  = $this->getData($DADOS_OFX->BANKMSGSRSV1->STMTTRNRS->STMTRS->LEDGERBAL->DTASOF);

		$this->movimentos = Array();
		
		foreach($DADOS_OFX->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $mov){
			$mov_aux = (array)$mov;
			$mov_aux['DATA'] = $this->getData($mov_aux['DTPOSTED']);
			$this->movimentos[]= $mov_aux;
		}
		/**
		 * Banco Sicredi
		 */
		if($this->cd_banco == 748){
			$this->cd_agencia = '0'.substr($this->cd_ctacor, 0, 3);
			$this->cd_ctacor  = substr($this->cd_ctacor, -6, 5).'-'.substr($this->cd_ctacor, -1);
		}
		/**
		 * Banco Banrisul
		 */
		if($this->cd_banco == '041'){
			$this->cd_agencia = substr($this->cd_ctacor, 0, 4);
			$this->cd_ctacor  = substr($this->cd_ctacor, -10, 2).'.'.substr($this->cd_ctacor, -8, 6).'.'.substr($this->cd_ctacor, -2, 1).'-'.substr($this->cd_ctacor, -1);
		}
		/**
		 * Banco Itau
		 */
		if($this->cd_banco == '0341'){
			$this->cd_agencia = substr($this->cd_ctacor, 0, 4);
			$this->cd_ctacor  = substr($this->cd_ctacor, -6, 5).'-'.substr($this->cd_ctacor, -1);
		}

	}
	/**
	 * 
	 */	
	public function getDadosConta()	{
		$vl_credito = 0;
		$vl_debito  = 0;
		for($i=0, $j = sizeof($this->movimentos); $i<$j; $i++){
			if($this->movimentos[$i]['TRNTYPE'] == 'CREDIT'){
				$vl_credito += $this->movimentos[$i]['TRNAMT'];
			}
			if($this->movimentos[$i]['TRNTYPE'] == 'DEBIT'){
				$vl_debito += $this->movimentos[$i]['TRNAMT'];
			}
		}
		$retorno = array('cd_banco' 	=> $this->cd_banco, 
						 'cd_agencia' 	=> $this->cd_agencia,
						 'cd_ctacor' 	=> $this->cd_ctacor,
						 'vl_saldo'		=> $this->vl_saldo,
						 'dt_saldo'		=> $this->dt_saldo,
						 'vl_debito'	=> $vl_debito,
						 'vl_credito'	=> $vl_credito);
		return $retorno;
	}
	/**
	 * 
	 */	
	public function getCreditos($min = 0)	{
		$ret = Array();
		if($min > 0)
			$min *= (-1);

		for($i=0, $j = sizeof($this->movimentos); $i<$j; $i++){
			if($this->movimentos[$i]['TRNTYPE'] == 'CREDIT' && $this->movimentos[$i]['TRNAMT'] >= $min){
				$ret[]= $this->movimentos[$i];
			}
		}
		return $ret;
	}
	/**
	 * 
	 */
	public function getDebitos($min = 0){
		$ret = Array();
		
		if($min > 0)
			$min *= (-1);
		
		for($i=0, $j= sizeof($this->movimentos); $i<$j; $i++){
			if($this->movimentos[$i]['TRNTYPE'] == 'DEBIT' && $this->movimentos[$i]['TRNAMT'] <= $min){
				$this->movimentos[$i]['TRNAMT'] = str_replace('-', '', $this->movimentos[$i]['TRNAMT']);
				$ret[]= $this->movimentos[$i];
			}
		}
		return $ret;
	}
	/**
	 * 
	 */
	public function getMovimentos(){
		return $this->movimentos;
	}
	/**
	 * 
	 */
	public function getMovimento($mov = ''){
		
		if(isset($this->movimentos[$mov])){
			return $this->movimentos[$mov];
		}
		return false;
	}
	/**
	 * 
	 */
	private function getData($data = ''){
		
		if(!empty($data)){
			$data = date('Y-m-d', strtotime(substr($data,0,8)));
		}
		
		return $data;
	}
	/**
	 * 
	 */
	private function validaOFX(){
		$this->StringOFX = stristr($this->StringOFX, '<OFX>');
		$this->StringOFX = substr($this->StringOFX, strlen('<OFX>'));
		$stop   = stripos($this->StringOFX, '</OFX>');
		$this->StringOFX = '<OFX>'.substr($this->StringOFX, 0, $stop).'</OFX>';
	}
}