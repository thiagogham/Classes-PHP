<?
/**
*	Descrição: 	Classe que busca desligamentos Programados por cidade
*	Author: 	Thiago R. Gham 
*	Data: 		16-01-2017
*	Versão:		0.1
*/
class Ceee{
	
	private $cd_cidade     	= 69; 
	private $url         	= 'http://www.ceee.com.br/pportal/ceee/Component/Controller.aspx?CC=1213';
	private $content     	= '';
	private $retorno 	   	= array();
	private $data    		= ''; 
	private $hora     		= '';
	private $nm_cidade 	 	= '';
	private $bairros 	 	= '';
	private $ruas    	 	= '';
	private $motivo 	 	= '';
	private $pessoas	 	= '';
	private $cd_servico	 	= '';
	private $obs    	 	= '';
	public  $http_code   	= '';
	public  $errno       	= '';
	public  $errmsg      	= '';
	/**
	*	Descrição: Construtor
	*	@param $cd_cidade
	*/
	public function __construct($cd_cidade = 69) {
		$this->cd_cidade = $cd_cidade;
		$this->getPage();
	}
	/**
	*	Descrição: Construtor
	*	@param $cd_cidade
	*/
	public function getDados(){

		$doc = new DOMDocument();
		$doc->loadHTML($this->content);
		$uls = $doc->getElementsByTagName('ul');
		foreach ($uls as $ul) {
		    if ($ul->getAttribute('class') == 'list desligamento-programado') {
		        $lis = $ul->getElementsByTagName('li');
		        foreach ($lis as $li) {
		            $this->data   = rtrim($li->getElementsByTagName('h3')->item(0)->textContent);
		            $this->data   = substr($this->data, 0, 10);
		            $this->hora   = rtrim($li->getElementsByTagName('strong')->item(0)->textContent);
		            $table  = $li->getElementsByTagName('table');
		            foreach ($table as $row) {
		                $cells = $row->getElementsByTagName('td');
		                $x = 0;
		                foreach ($cells as $cell) {
		                    switch ($x) {
		                        case 1:
		                            $this->cd_servico = rtrim($cell->nodeValue);
		                            break;
		                        case 3:
		                            $this->nm_cidade = rtrim($cell->nodeValue);
		                            break;
		                        case 5:
		                            $this->bairros = rtrim($cell->nodeValue);
		                            break;
		                        case 7:
		                            $this->ruas = rtrim($cell->nodeValue);
		                            break;
		                        case 9:
		                            $this->motivo = rtrim($cell->nodeValue);
		                            break;
		                        case 13:
		                            $this->pessoas = rtrim($cell->nodeValue);
		                            break;
		                        case 15:
		                            $this->obs = rtrim($cell->nodeValue);
		                            break;
		                    }
		                    $x++;
		                }
		            }
		            $obj = new stdClass;
					$obj->cd_servico = $this->cd_servico;
					$obj->data 		 = $this->data;
					$obj->hora 		 = $this->hora;
					$obj->cd_cidade  = $this->cd_cidade;
					$obj->nm_cidade  = $this->nm_cidade;
					$obj->bairros    = $this->bairros;
					$obj->ruas       = $this->ruas;
					$obj->motivo     = $this->motivo;
					$obj->pessoas    = $this->pessoas;
					$obj->obs        = $this->obs;

					$this->retorno[] = $obj;
				}
		    }
		}
		return $this->retorno;
	}
	/**
	*	Descrição: Construtor
	*	@param $cd_cidade
	*/
	private function getPage(){
		$postData = array("inpCodCity" => $this->cd_cidade);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_URL             => $this->url,
		    CURLOPT_REFERER         => 'http://www.ceee.com.br',
		    CURLOPT_USERAGENT       => 'Foo Spider',
		    CURLOPT_HEADER          => true,
		    CURLOPT_RETURNTRANSFER  => true,
		    CURLOPT_CONNECTTIMEOUT  => 6,
		    CURLOPT_TIMEOUT         => 6,
		    CURLOPT_MAXREDIRS       => 1,
		    CURLOPT_FOLLOWLOCATION  => true,
		    CURLOPT_COOKIESESSION   => true,
		    CURLOPT_POSTFIELDS      => http_build_query($postData),
		    CURLOPT_POST            => true
		    )
		);

		try {
		    $this->content    = curl_exec($curl);
		    $data             = curl_getinfo($curl);
		    $this->http_code = $data['http_code'];
		    $this->errno     = curl_errno($curl);
		    $this->errmsg    = curl_error($curl);
		} catch (HttpException $ex) {
		    die($ex);
		}
		curl_close($curl); 
	}
}