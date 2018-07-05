<?
/**
*	Descrição: 	Classe que busca resultado de loterias do site da Caixa Federal 
*	Author: 	Thiago R. Gham
*	Data: 		19-05-2016
*	Versão:		0.1
*/
class Loterias{
	private $loteria     	= 'megasena'; 
	private $url         	= 'http://www.loterias.caixa.gov.br/wps/portal/loterias/landing/';
	private $content     	= '';
	private $jsonloteria 	= '';
	private $resultado   	= array();
	private $concurso    	= ''; 
	private $proximo     	= '';
	private $dt_concurso  	= '';
	private $timecoracao 	= '';
	public  $http_code   	= '';
	public  $errno       	= '';
	public  $errmsg      	= '';
	/**
	*	Descrição: Nome loteria 
	*	@param $loteria
	*/
	public function __construct($loteria) {
		if(stripos($loteria, 'megasena') !== false OR (stripos($loteria, 'mega') !== false AND stripos($loteria, 'sena') !== false)){
			$loteria = 'megasena';
		}elseif(stripos($loteria, 'lotofacil') !== false OR (stripos($loteria, 'loto') !== false AND stripos($loteria, 'facil') !== false)){
			$loteria = 'lotofacil';
		}elseif(stripos($loteria, 'lotomania') !== false OR (stripos($loteria, 'loto') !== false AND stripos($loteria, 'mania') !== false)){
			$loteria = 'lotomania';
		}elseif(stripos($loteria, 'timemania') !== false OR (stripos($loteria, 'time') !== false AND stripos($loteria, 'mania') !== false)){
			$loteria = 'timemania';
		}elseif(stripos($loteria, 'duplasena') !== false OR (stripos($loteria, 'dupla') !== false AND stripos($loteria, 'sena') !== false)){
			$loteria = 'duplasena';
		}elseif(stripos($loteria, 'federal') !== false OR (stripos($loteria, 'fede') !== false AND stripos($loteria, 'eral') !== false)){
			$loteria = 'federal';
		}elseif(stripos($loteria, 'quina') !== false){
			$loteria = 'quina';
		}else{
			$loteria = $this->loteria;
		}

		$this->loteria = $loteria;
		$this->getLoteria();
		$funcao = 'get'.ucfirst($this->loteria);
		$this->$funcao();
	}
	/**
	*	Descrição: Gera Json Loteria
	*	@param 
	*/
	public function getJsonLoteria(){
		$this->jsonloteria = new StdClass;
		$this->jsonloteria->concurso 		  = 'Resultado Concurso ' . $this->getConcurso() . ' ' . $this->getDataConcurso();
		$this->jsonloteria->numeros_sorteados = $this->getResultado();
		$this->jsonloteria->proximo_concurso  = 'Estimativa de prêmio do próximo concurso R$ ' . $this->getProximo();
		return $this->jsonloteria;
	}
	/**
	*	Descrição: Salva Arquivo com resultado loteria
	*	@param 
	*/
	public function salvaLoteria($nm_arquivo){
		$dados = $this->getJsonLoteria();
		if(!empty($dados->concurso) AND !empty($dados->numeros_sorteados)){
			return file_put_contents($nm_arquivo, json_encode($dados));	
		}
		return false;
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	public function getDataConcurso(){
		return $this->dt_concurso;
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	public function getResultado(){
		return $this->resultado;
	}
	/**
	*	Descrição: Captura dados para MEgaSena 
	*	@param 
	*/
	public function getConcurso(){
		return ($this->concurso);
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	public function getProximo(){
		return $this->proximo;
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	public function getTimecoracao(){
		return $this->timecoracao;
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getMegasena(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->concurso;
			$this->resultado   = explode('-', $json->resultadoOrdenado);
			$this->proximo     = number_format(str_replace(array(',','.'), '', $json->vr_estimativa),2,",",".");
			$this->dt_concurso = $json->dataStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}
	}
	/**
	*	Descrição: Captura dados para duplasena
	*	@param 
	*/
	private function getDuplasena(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->concurso;
			$this->resultado   = explode('-', $json->resultadoOrdenadoSorteio1);
			$this->resultado   = array_merge($this->resultado, explode('-', $json->resultadoOrdenadoSorteio2));
			$this->proximo     = number_format(str_replace(array(',','.'), '', $json->valor_estimativa),2,",",".");
			$this->dt_concurso = $json->dataStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getTimemania(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->nu_CONCURSO;
			$this->resultado   = explode('-', $json->resultadoOrdenado);
			$this->proximo     = number_format(str_replace(array(',','.'), '', $json->vr_ACUMULADO_PROXIMO_CONCURSO),2,",",".");
			$this->dt_concurso = $json->dt_APURACAOStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
			$this->timecoracao = $json->timeCoracao;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}		
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getLotofacil(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->nu_concurso;
			$this->resultado   = explode('-', $json->resultadoOrdenado);
			$this->proximo     = $json->vrEstimativa;
			$this->dt_concurso = $json->dt_apuracaoStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getLotomania(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->concurso;
			$this->resultado   = explode('-', $json->resultadoOrdenado);
			$this->proximo     = number_format(str_replace(array(',','.'), '', $json->vrEstimativa),2,",",".");
			$this->dt_concurso = $json->dtApuracaoStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getFederal(){
		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->concurso;
			$this->dt_concurso = $json->data;
			$resultado_aux = array();
			$count = count($json->premios);
			for ($i=0; $i < $count; $i++) { 
				$dados = new StdClass;
				$dados->concurso   = $json->concurso;
				$dados->bilhete    = $json->premios[$i]->bilhete;
				$dados->valor_pago = $json->premios[$i]->valor;
				$resultado_aux[]   = $dados;
			}
			$this->resultado = $resultado_aux;
		}
	}
	/**
	*	Descrição: Captura dados para MEgaSena
	*	@param 
	*/
	private function getQuina(){

		$json = json_decode( $this->content );

		if(is_object($json)){
			$this->concurso    = $json->concurso;
			$this->resultado   = explode('-', $json->resultadoOrdenado);
			$this->proximo     = number_format(str_replace(array(',','.'), '', $json->vrEstimado),2,",",".");
			$this->dt_concurso = $json->dataStr;//str_replace(array(',','.'), '', $json->vr_estimativa);
		}
	}
	/**
	*	Descrição: Busca dados para a loteria
	*	@param 
	*/
	private function getLoteria(){

		$parametro = '';

		switch ($this->loteria) {
			case 'megasena':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbwMPI0sDBxNXAOMwrzCjA0sjIEKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wNnUwNHfxcnSwBgIDUyhCvA5EawAjxsKckMjDDI9FQE-F4ca/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_HGK818G0KO6H80AU71KG7J0072/res/id=buscaResultado';
				break;
			case 'lotofacil':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbz8vTxNDRy9_Y2NQ13CDA0sTIEKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wBmoxN_FydLAGAgNTKEK8DkRrACPGwpyQyMMMj0VAcySpRM!/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0J0VSC0AC4GLFAD2003/res/id=buscaResultado';
				break;
			case 'quina':
				$parametro = '/!ut/p/a1/jc69DoIwAATgZ_EJepS2wFgoaUswsojYxXQyTfgbjM9vNS4Oordd8l1yxJGBuNnfw9XfwjL78dmduIikhYFGA0tzSFZ3tG_6FCmP4BxBpaVhWQuA5RRWlUZlxR6w4r89vkTi1_5E3CfRXcUhD6osEAHA32Dr4gtsfFin44Bgdw9WWSwj/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0J0VSC0AC4GLFAD20G6/res/id=buscaResultado';
				break;	
			case 'lotomania':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbz8vTxNDRy9_Y2NQ13CDA38jYEKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wBmoxN_FydLAGAgNTKEK8DkRrACPGwpyQyMMMj0VAajYsZo!/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0JGJVA0AKLR5T3K00V0/res/id=buscaResultado';
				break;
			case 'timemania':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbz8vTxNDRy9_Y2NQ13CDA1MzIEKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wBmoxN_FydLAGAgNTKEK8DkRrACPGwpyQyMMMj0VASrq9qk!/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0JGJVA0AKLR5T3K00M4/res/id=buscaResultado';
				break;
			case 'duplasena':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbwMPI0sDBxNXAOMwrzCjA2cDIAKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wNnUwNHfxcnSwBgIDUyhCvA5EawAjxsKckMjDDI9FQGgnyPS/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0J0I280A4EP2VJV30N4/res/id=buscaResultado';
				break;
			case 'federal':
				$parametro = '/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbz8vTxNDRy9_Y2NQ13CDA0MzIAKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wBmoxN_FydLAGAgNTKEK8DkRrACPGwpyQyMMMj0VAYe29yM!/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_61L0H0G0J0VSC0AC4GLFAD20G0/res/id=buscaResultado';
				
				break;
		}

		$curl = curl_init();
		$cookie_file = __DIR__.DIRECTORY_SEPARATOR.$this->loteria.'.txt';
		curl_setopt_array($curl, array(
			CURLOPT_URL 			=> $this->url.$this->loteria.$parametro,
			CURLOPT_REFERER 		=> 'http://www.loterias.caixa.gov.br',
			CURLOPT_USERAGENT 		=> 'Foo Spider',
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_CONNECTTIMEOUT 	=> 6,
			CURLOPT_TIMEOUT 		=> 6,
			CURLOPT_MAXREDIRS 		=> 1,
			CURLOPT_FOLLOWLOCATION 	=> true,
			CURLOPT_COOKIESESSION 	=> true,
			CURLOPT_COOKIEFILE 		=> $cookie_file,
			CURLOPT_COOKIEJAR 		=> $cookie_file
			)
		);

		try {
			$content 	     = curl_exec($curl);
			$this->content   = $content;
			$data 	 	     = curl_getinfo($curl);
			$this->http_code = $data['http_code'];
			$this->errno   	 = curl_errno($curl);
			$this->errmsg    = curl_error($curl);
		} catch (HttpException $ex) {
			die($ex);
		}

		curl_close($curl); 
	}
}